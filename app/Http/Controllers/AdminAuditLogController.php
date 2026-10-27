<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Services\AuditLogService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminAuditLogController extends Controller
{
    /**
     * Display the audit log dashboard with filters and automatic risk analysis.
     */
    public function index(Request $request): View
    {
        $risk = $request->input('risk', 'all');
        $category = $request->input('category', 'all');
        $search = $request->input('search');
        $dateRange = $request->input('date', 'all');

        $query = AuditLog::with(['character', 'user']);

        if ($risk && $risk !== 'all') {
            $query->risk($risk);
        }

        if ($category && $category !== 'all') {
            $query->category($category);
        }

        if ($search) {
            $query->search($search);
        }

        if ($dateRange === 'today') {
            $query->whereDate('created_at', Carbon::today());
        } elseif ($dateRange === 'week') {
            $query->where('created_at', '>=', Carbon::now()->subDays(7));
        } elseif ($dateRange === 'month') {
            $query->where('created_at', '>=', Carbon::now()->subDays(30));
        }

        $logs = $query->orderByDesc('id')
            ->paginate(30)
            ->withQueryString();

        $totalCount = AuditLog::count();
        $highRiskCount = AuditLog::where('risk_level', 'hoog')->count();
        $suspiciousCount = AuditLog::where('risk_level', 'verdacht')->count();
        $normalCount = AuditLog::where('risk_level', 'normaal')->count();
        $uniquePlayersCount = AuditLog::whereNotNull('character_id')->distinct('character_id')->count('character_id');

        return view('admin.audit_log', compact(
            'logs',
            'risk',
            'category',
            'search',
            'dateRange',
            'totalCount',
            'highRiskCount',
            'suspiciousCount',
            'normalCount',
            'uniquePlayersCount'
        ));
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
}
