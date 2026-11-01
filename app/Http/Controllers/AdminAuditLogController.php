<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Character;
use App\Services\AuditLogService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminAuditLogController extends Controller
{
    /**
     * Display the audit log dashboard with filters and automatic risk analysis.
     */
    public function index(Request $request): View
    {
        AuditLogService::sanitizeContaminatedIps();

        $risk = $request->input('risk', 'all');
        $category = $request->input('category', 'all');
        $search = $request->input('search');
        $dateRange = $request->input('date', 'all');
        $ip = $request->input('ip');
        $characterId = $request->input('character_id');
        $sort = $request->input('sort', 'newest');
        $perPage = in_array((int) $request->input('per_page', 30), [30, 50, 100], true)
            ? (int) $request->input('per_page', 30)
            : 30;

        $query = $this->buildFilteredQuery($request);

        $logs = $query->paginate($perPage)->withQueryString();

        $sub24h = Carbon::now()->subDay();
        $kpiStats = AuditLog::query()
            ->selectRaw("
                COUNT(*) as total_count,
                COUNT(CASE WHEN risk_level = 'hoog' THEN 1 END) as high_risk_count,
                COUNT(CASE WHEN risk_level = 'verdacht' THEN 1 END) as suspicious_count,
                COUNT(CASE WHEN risk_level = 'normaal' THEN 1 END) as normal_count,
                COUNT(DISTINCT character_id) as unique_players_count,
                COUNT(CASE WHEN created_at >= ? THEN 1 END) as total_24h,
                COUNT(CASE WHEN created_at >= ? AND risk_level = 'hoog' THEN 1 END) as high_risk_24h,
                COUNT(CASE WHEN created_at >= ? AND risk_level = 'verdacht' THEN 1 END) as suspicious_24h
            ", [$sub24h, $sub24h, $sub24h])
            ->first();

        $totalCount = (int) ($kpiStats->total_count ?? 0);
        $highRiskCount = (int) ($kpiStats->high_risk_count ?? 0);
        $suspiciousCount = (int) ($kpiStats->suspicious_count ?? 0);
        $normalCount = (int) ($kpiStats->normal_count ?? 0);
        $uniquePlayersCount = (int) ($kpiStats->unique_players_count ?? 0);

        $stats24h = [
            'total' => (int) ($kpiStats->total_24h ?? 0),
            'high_risk' => (int) ($kpiStats->high_risk_24h ?? 0),
            'suspicious' => (int) ($kpiStats->suspicious_24h ?? 0),
        ];

        $categories24h = AuditLog::query()
            ->where('created_at', '>=', $sub24h)
            ->select('category', DB::raw('COUNT(*) as total'))
            ->groupBy('category')
            ->orderByDesc('total')
            ->pluck('total', 'category')
            ->toArray();

        $topRiskPlayers = AuditLog::query()
            ->whereNotNull('character_id')
            ->whereIn('risk_level', ['hoog', 'verdacht'])
            ->select('character_id')
            ->selectRaw("
                COUNT(*) as incident_count,
                COUNT(CASE WHEN risk_level = 'hoog' THEN 1 END) as high_risk_count,
                MAX(risk_score) as max_risk_score,
                SUM(risk_score) as total_risk_score,
                COALESCE(SUM(amount), 0) as total_amount,
                MAX(created_at) as last_incident_at
            ")
            ->with(['character:id,name,level,cash,bank,user_id'])
            ->groupBy('character_id')
            ->orderByDesc('high_risk_count')
            ->orderByDesc('total_risk_score')
            ->limit(5)
            ->get();

        $sharedIps = AuditLog::query()
            ->whereNotNull('ip_address')
            ->where('ip_address', '!=', '')
            ->whereNotNull('user_id')
            ->whereNotNull('character_id')
            ->select('ip_address')
            ->selectRaw("
                COUNT(DISTINCT user_id) as user_count,
                COUNT(DISTINCT character_id) as character_count,
                COUNT(*) as total_logs,
                COUNT(CASE WHEN risk_level = 'hoog' THEN 1 END) as high_risk_count,
                MAX(created_at) as last_seen_at
            ")
            ->groupBy('ip_address')
            ->havingRaw('COUNT(DISTINCT user_id) > 1 OR COUNT(DISTINCT character_id) > 1')
            ->orderByDesc('character_count')
            ->orderByDesc('total_logs')
            ->limit(5)
            ->get();

        $sharedIpAddresses = $sharedIps->pluck('ip_address')->all();
        $sharedIpCharacters = ! empty($sharedIpAddresses)
            ? AuditLog::query()
                ->whereIn('ip_address', $sharedIpAddresses)
                ->whereNotNull('user_id')
                ->whereNotNull('character_id')
                ->with('character:id,name,level')
                ->select('ip_address', 'character_id')
                ->distinct()
                ->get()
                ->groupBy('ip_address')
            : collect();

        $selectedCharacter = (! empty($characterId) && is_numeric($characterId))
            ? Character::find((int) $characterId)
            : null;

        return view('admin.audit_log', compact(
            'logs',
            'risk',
            'category',
            'search',
            'dateRange',
            'ip',
            'characterId',
            'selectedCharacter',
            'sort',
            'perPage',
            'totalCount',
            'highRiskCount',
            'suspiciousCount',
            'normalCount',
            'uniquePlayersCount',
            'stats24h',
            'categories24h',
            'topRiskPlayers',
            'sharedIps',
            'sharedIpCharacters'
        ));
    }

    /**
     * Stream filtered audit logs as a clean UTF-8 CSV download.
     */
    public function export(Request $request): StreamedResponse
    {
        AuditLogService::sanitizeContaminatedIps();

        $query = $this->buildFilteredQuery($request);

        $filename = 'audit-logs-export-'.Carbon::now()->format('Y-m-d-His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->streamDownload(function () use ($query): void {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                return;
            }

            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'ID',
                'Tijdstip',
                'Speler',
                'Speler ID',
                'Gebruiker',
                'Gebruiker ID',
                'IP Adres',
                'Actie',
                'Categorie',
                'Omschrijving',
                'Bedrag',
                'Risiconiveau',
                'Risicoscore',
                'Risicoreden',
                'Risicoflags',
                'Details (JSON)',
            ]);

            $sanitize = static function (?string $value): string {
                if ($value === null || $value === '') {
                    return '';
                }
                if (in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
                    return "'".$value;
                }

                return $value;
            };

            foreach ($query->cursor() as $log) {
                fputcsv($handle, [
                    $log->id,
                    $log->created_at?->format('Y-m-d H:i:s'),
                    $sanitize($log->character?->name ?? 'N/A'),
                    $log->character_id ?? '',
                    $sanitize($log->user?->name ?? 'N/A'),
                    $log->user_id ?? '',
                    $log->ip_address ?? '',
                    $sanitize($log->action),
                    $sanitize($log->category),
                    $sanitize($log->description),
                    $log->amount ?? 0,
                    $log->risk_level,
                    $log->risk_score,
                    $sanitize($log->risk_reason ?? ''),
                    $sanitize(is_array($log->risk_flags) ? implode(', ', $log->risk_flags) : ($log->risk_flags ?? '')),
                    ! empty($log->details) ? json_encode($log->details, JSON_UNESCAPED_UNICODE) : '',
                ]);
            }

            fclose($handle);
        }, $filename, $headers);
    }

    /**
     * Re-analyze and synchronize historical logs.
     */
    public function sync(Request $request): RedirectResponse
    {
        $limit = max(100, min(5000, (int) $request->input('limit', 2000)));
        $stats = AuditLogService::backfillHistory($limit);

        return redirect()->route('admin.audit_log.index')
            ->with('success', "Historische audit log gesynchroniseerd! {$stats['total_synced']} nieuwe gebeurtenissen geanalyseerd ({$stats['high_risk']} hoog risico, {$stats['suspicious']} verdacht).");
    }

    /**
     * Build the filtered and sorted query for audit logs.
     */
    protected function buildFilteredQuery(Request $request): Builder
    {
        $query = AuditLog::with(['character', 'user']);

        $risk = $request->input('risk', 'all');
        if ($risk && $risk !== 'all') {
            $query->risk($risk);
        }

        $category = $request->input('category', 'all');
        if ($category && $category !== 'all') {
            $query->category($category);
        }

        $search = $request->input('search');
        if ($search) {
            $query->search($search);
        }

        $ip = $request->input('ip');
        if (! empty($ip)) {
            $query->ip($ip);
        }

        $characterId = $request->input('character_id');
        if (! empty($characterId) && is_numeric($characterId)) {
            $query->where('character_id', (int) $characterId);
        }

        $dateRange = $request->input('date', 'all');
        if ($dateRange === 'today') {
            $query->whereDate('created_at', Carbon::today());
        } elseif ($dateRange === '24h') {
            $query->where('created_at', '>=', Carbon::now()->subDay());
        } elseif ($dateRange === 'week') {
            $query->where('created_at', '>=', Carbon::now()->subDays(7));
        } elseif ($dateRange === 'month') {
            $query->where('created_at', '>=', Carbon::now()->subDays(30));
        }

        $sort = $request->input('sort', 'newest');
        match ($sort) {
            'oldest' => $query->orderBy('id', 'asc'),
            'highest_risk' => $query->orderByDesc('risk_score')->orderByDesc('id'),
            'highest_amount' => $query->orderByDesc('amount')->orderByDesc('id'),
            default => $query->orderByDesc('id'),
        };

        return $query;
    }
}
