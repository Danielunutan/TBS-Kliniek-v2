<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\CpaleadConversion;
use App\Models\GameNotification;
use App\Services\EconomyService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CpaleadPostbackController extends Controller
{
    public function __construct(
        protected EconomyService $economy,
    ) {}

    /**
     * Handle incoming CPAlead publisher postback callback (HTTP GET).
     */
    public function handle(Request $request): Response
    {
        $configuredPassword = config('services.cpalead.password') ?? env('CPALEAD_POSTBACK_PASSWORD');
        $incomingPassword = $request->input('password');

        // 1. Password check (if configured)
        if (! empty($configuredPassword)) {
            if (empty($incomingPassword) || ! hash_equals((string) $configuredPassword, (string) $incomingPassword)) {
                Log::warning('CPAlead postback: Invalid password provided.', [
                    'ip' => $request->ip(),
                    'lead_id' => $request->input('lead_id'),
                ]);

                return response('Error: Invalid postback password', 403);
            }
        }

        // 2. Validate essential parameters
        $leadId = trim((string) $request->input('lead_id'));
        $subid = trim((string) $request->input('subid'));
        $subid2 = strtolower(trim((string) $request->input('subid2', 'cash')));
        $payout = (float) $request->input('payout', 0);
        $campaignId = $request->input('campaign_id');
        $campaignName = $request->input('campaign_name', 'CPAlead Offer');
        $ipAddress = $request->input('ip') ?? $request->input('ip_address') ?? $request->ip();

        if (empty($leadId)) {
            return response('Error: Missing lead_id', 400);
        }

        if (empty($subid)) {
            return response('Error: Missing subid', 400);
        }

        // 3. Atomic duplicate check (idempotent)
        if (CpaleadConversion::where('lead_id', $leadId)->exists()) {
            return response('OK - Duplicate already processed', 200);
        }

        // 4. Resolve Character
        $character = Character::find($subid);
        if (! $character) {
            Log::info('CPAlead postback: Character not found for subid.', ['subid' => $subid, 'lead_id' => $leadId]);

            // Save conversion record without character to acknowledge receipt to CPAlead
            try {
                CpaleadConversion::create([
                    'lead_id' => $leadId,
                    'subid' => $subid,
                    'subid2' => $subid2,
                    'payout' => $payout,
                    'campaign_id' => $campaignId,
                    'campaign_name' => $campaignName,
                    'ip_address' => $ipAddress,
                    'reward_granted' => 'Character not found',
                ]);
            } catch (\Exception $e) {
            }

            return response('OK - Character not found', 200);
        }

        // 5. Grant reward inside a database transaction
        $rewardGranted = '';

        DB::transaction(function () use ($character, $leadId, $subid, $subid2, $payout, $campaignId, $campaignName, $ipAddress, &$rewardGranted) {
            // Check double-entry lock
            if (CpaleadConversion::where('lead_id', $leadId)->lockForUpdate()->exists()) {
                return;
            }

            // Increment sponsor ad counts
            $today = now()->format('Y-m-d');
            if ($character->last_ad_date !== $today) {
                $character->ads_watched_today = 1;
                $character->last_ad_date = $today;
            } else {
                $character->ads_watched_today = ($character->ads_watched_today ?? 0) + 1;
            }

            $character->ads_watched_total = ($character->ads_watched_total ?? 0) + 1;
            $character->last_ad_watched_at = now();

            // Check 250-ad milestone for 24h Free VIP
            $vipNotice = '';
            if ($character->ads_watched_total >= 250 && (! $character->is_vip || ($character->vip_expires_at && $character->vip_expires_at->isPast()))) {
                $character->is_vip = true;
                $character->vip_expires_at = now()->addHours(24);
                $vipNotice = ' 🌟 24 UUR GRATIS VIP ONTGRENDELD!';
            }

            // Grant chosen reward
            switch ($subid2) {
                case 'prison':
                    if ($character->is_in_prison && $character->prison_until && $character->prison_until->isFuture()) {
                        $newUntil = $character->prison_until->subMinutes(15);
                        if ($newUntil->isPast()) {
                            $character->is_in_prison = false;
                            $character->prison_until = null;
                            $character->prison_reason = null;
                            $rewardGranted = '-15 min straftijd (Direct vrijgelaten uit isoleercel!)';
                        } else {
                            $character->prison_until = $newUntil;
                            $rewardGranted = '-15 min straftijd';
                        }
                    } else {
                        // Fallback to cash if not in prison
                        $this->economy->addCash($character, 2500);
                        $rewardGranted = '€2.500 zakgeld (Niet in cel)';
                    }
                    break;

                case 'bullets':
                case 'bullet':
                case 'kogels':
                case 'kogel':
                    $character->bullets = ($character->bullets ?? 0) + 50;
                    $rewardGranted = '+50 Scherpe Kogels 💣';
                    break;

                case 'vest':
                case 'kogelvest':
                case 'kogel_vest':
                case 'armor':
                case 'armour':
                    $character->vest_until = now()->addHours(12);
                    $rewardGranted = '12u Kogelwerend Vest 🛡️';
                    break;

                case 'coma_revive':
                case 'coma':
                case 'ic':
                case 'intensive_care':
                case 'revive':
                    $character->coma_until = null;
                    $character->health = max(25, (int) ($character->max_health * 0.5));
                    $rewardGranted = 'Ontwaakt uit Intensive Care Coma 💉';
                    break;

                case 'mask':
                    $character->masks = ($character->masks ?? 0) + 1;
                    $rewardGranted = '+1 Zakkenrollers Masker 🎭';
                    break;

                case 'token':
                    $character->vault_tokens = ($character->vault_tokens ?? 0) + 1;
                    $rewardGranted = '+1 Kluismunt 🪙';
                    break;

                case 'star':
                    $character->stars = ($character->stars ?? 0) + 1;
                    $rewardGranted = '+1 Ster ⭐';
                    break;

                case 'cash':
                default:
                    $this->economy->addCash($character, 2500);
                    $rewardGranted = '€2.500 zakgeld';
                    break;
            }

            $character->save();

            // Record conversion
            CpaleadConversion::create([
                'character_id' => $character->id,
                'lead_id' => $leadId,
                'subid' => $subid,
                'subid2' => $subid2,
                'payout' => $payout,
                'campaign_id' => $campaignId,
                'campaign_name' => $campaignName,
                'ip_address' => $ipAddress,
                'reward_granted' => $rewardGranted.$vipNotice,
            ]);

            // Notify player in-game
            try {
                GameNotification::create([
                    'character_id' => $character->id,
                    'type' => 'ad_reward',
                    'title' => '🎉 Sponsorkamer Beloning Ontvangen!',
                    'message' => "Je hebt een CPAlead sponsoropdracht voltooid ({$campaignName}). Jouw beloning: {$rewardGranted}!{$vipNotice}",
                    'link' => route('sponsor.index'),
                    'is_read' => false,
                ]);
            } catch (\Exception $e) {
            }
        });

        Log::info("CPAlead conversion credited: Lead {$leadId} for Character {$subid} ({$rewardGranted}).");

        return response('OK', 200);
    }
}
