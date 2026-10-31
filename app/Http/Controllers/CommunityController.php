<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\SocialRewardClaim;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CommunityController extends Controller
{
    /**
     * @return array<string, array{name: string, icon: string, color: string, badge: string, handle: string, url: string, action_label: string, reward: array{cash: int, respect: int, energy: int}, description: string}>
     */
    public static function platforms(): array
    {
        return [
            'discord' => [
                'name' => 'Officiële Discord',
                'icon' => 'fa-brands fa-discord',
                'color' => '#5865F2',
                'badge' => 'Discord Server',
                'handle' => 'discord.gg/tbs',
                'url' => 'https://discord.gg/tbs',
                'action_label' => 'Word Lid van Discord',
                'description' => 'Chat gezellig mee met mede-patiënten, doe mee aan exclusieve giveaways en ontvang direct alerts voor game-updates.',
                'reward' => ['cash' => 25000, 'respect' => 25, 'energy' => 15],
            ],
            'youtube' => [
                'name' => 'YouTube Kanaal',
                'icon' => 'fa-brands fa-youtube',
                'color' => '#FF0000',
                'badge' => '@tbsklinieknl',
                'handle' => 'youtube.com/@tbsklinieknl',
                'url' => 'https://www.youtube.com/@tbsklinieknl',
                'action_label' => 'Abonneer op YouTube',
                'description' => 'Bekijk cinematische trailers, gameplay video\'s en exclusieve vlogs over de kliniek.',
                'reward' => ['cash' => 25000, 'respect' => 25, 'energy' => 15],
            ],
            'tiktok' => [
                'name' => 'TikTok Kanaal',
                'icon' => 'fa-brands fa-tiktok',
                'color' => '#0f172a',
                'badge' => '@tbsklinieknl',
                'handle' => 'tiktok.com/@tbsklinieknl',
                'url' => 'https://www.tiktok.com/@tbsklinieknl',
                'action_label' => 'Volg op TikTok',
                'description' => 'Korte grappige clips, memes en behind-the-scenes van de TBS Kliniek zaalwacht.',
                'reward' => ['cash' => 25000, 'respect' => 25, 'energy' => 15],
            ],
            'instagram' => [
                'name' => 'Instagram Profiel',
                'icon' => 'fa-brands fa-instagram',
                'color' => '#E1306C',
                'badge' => '@tbsklinieknl',
                'handle' => 'instagram.com/tbsklinieknl',
                'url' => 'https://www.instagram.com/tbsklinieknl/',
                'action_label' => 'Volg op Instagram',
                'description' => 'Volg de dagelijkse gebeurtenissen in de TBS Kliniek en doe mee aan community polls.',
                'reward' => ['cash' => 25000, 'respect' => 25, 'energy' => 15],
            ],
            'twitter' => [
                'name' => 'Twitter / X',
                'icon' => 'fa-brands fa-x-twitter',
                'color' => '#000000',
                'badge' => '@tbskliniek',
                'handle' => 'x.com/tbskliniek',
                'url' => 'https://x.com/tbskliniek',
                'action_label' => 'Volg op Twitter / X',
                'description' => 'Snelle aankondigingen, serverstatussen en interactieve polls direct van het team.',
                'reward' => ['cash' => 25000, 'respect' => 25, 'energy' => 15],
            ],
            'hyves' => [
                'name' => 'Hyves (TBS Kliniek)',
                'icon' => 'hyves-h',
                'color' => '#F36F21',
                'badge' => 'Respect Hyve',
                'handle' => 'hyves.nl/#my-profile/hyve?id=Zv3DWejo',
                'url' => 'https://hyves.nl/#my-profile/hyve?id=Zv3DWejo',
                'action_label' => 'Respecteer op Hyves',
                'description' => 'Dansende bananen, krabbels en pure nostalgie! Word lid van onze officiële Hyve.',
                'reward' => ['cash' => 25000, 'respect' => 25, 'energy' => 15],
            ],
            'push' => [
                'name' => 'Pushnotificaties (PC & Mobiel)',
                'icon' => 'fa-solid fa-bell',
                'color' => '#8b5cf6',
                'badge' => 'Desktop & WebApp',
                'handle' => 'Directe Kliniek Alerts',
                'url' => '/instellingen/account',
                'action_label' => 'Inschakelen & Bonus Claimen',
                'description' => 'Blijf alert op pc of mobiel bij diefstallen, aanvallen en berichten als je offline bent.',
                'reward' => ['cash' => 25000, 'respect' => 25, 'energy' => 20],
            ],
        ];
    }

    public function index(): View
    {
        $platforms = self::platforms();
        $user = Auth::user();
        $claims = [];

        if ($user) {
            $claims = SocialRewardClaim::where('user_id', $user->id)
                ->pluck('claimed_at', 'platform')
                ->toArray();
        }

        $totalPlatforms = count($platforms);
        $claimedCount = count($claims);
        $totalPossibleCash = array_sum(array_column(array_column($platforms, 'reward'), 'cash'));
        $totalPossibleRespect = array_sum(array_column(array_column($platforms, 'reward'), 'respect'));

        return view('game.community.index', compact(
            'platforms',
            'claims',
            'totalPlatforms',
            'claimedCount',
            'totalPossibleCash',
            'totalPossibleRespect'
        ));
    }

    public function claim(Request $request, string $platform): RedirectResponse|JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login')->with('error', 'Log eerst in om je community beloningen te claimen.');
        }

        $character = $user->character;
        if (! $character) {
            return redirect()->route('dashboard')->with('error', 'Je hebt nog geen actief personage.');
        }

        if ($character->isImprisoned()) {
            $jailMsg = 'Vanuit de isoleercel kun je geen community beloningen claimen!';

            return ($request->expectsJson() || $request->ajax())
                ? response()->json(['success' => false, 'message' => $jailMsg], 422)
                : redirect()->route('community.index')->with('error', $jailMsg);
        }

        if ($character->isComatose()) {
            $comaMsg = 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.';

            return ($request->expectsJson() || $request->ajax())
                ? response()->json(['success' => false, 'message' => $comaMsg], 422)
                : redirect()->route('community.index')->with('error', $comaMsg);
        }

        $platforms = self::platforms();
        if (! isset($platforms[$platform])) {
            return redirect()->route('community.index')->with('error', 'Ongeldig platform geselecteerd.');
        }

        $platformData = $platforms[$platform];

        // Check if already claimed
        $alreadyClaimed = SocialRewardClaim::where('user_id', $user->id)
            ->where('platform', $platform)
            ->exists();

        if ($alreadyClaimed) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Je hebt de beloning voor '.$platformData['name'].' al geclaimd.',
                    'url' => $platformData['url'],
                ]);
            }

            return redirect()->route('community.index')->with('error', 'Je hebt de beloning voor '.$platformData['name'].' al geclaimd.');
        }

        if ($platform === 'push' && ! $user->pushSubscriptions()->exists()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Schakel eerst pushnotificaties in op deze pagina of in je accountinstellingen om de € 25.000 bonus te ontvangen.',
                    'url' => route('settings.account'),
                ]);
            }

            return redirect()->route('settings.account')->with('info', 'Schakel pushnotificaties in om direct € 25.000 cash en 25 respect te ontvangen!');
        }

        $reward = $platformData['reward'];

        // Record the claim first. The unique (user_id, platform) index rejects a parallel duplicate,
        // and the transaction rolls the reward back with it.
        try {
            DB::transaction(function () use ($user, $character, $platform, $reward, $request): void {
                SocialRewardClaim::create([
                    'user_id' => $user->id,
                    'character_id' => $character->id,
                    'platform' => $platform,
                    'reward_cash' => $reward['cash'],
                    'reward_respect' => $reward['respect'],
                    'reward_energy' => $reward['energy'],
                    'ip_address' => $request->ip(),
                    'claimed_at' => now(),
                ]);

                /** @var Character $locked */
                $locked = Character::where('id', $character->id)->lockForUpdate()->first();
                $locked->cash += $reward['cash'];
                $locked->energy = min($locked->max_energy, $locked->energy + $reward['energy']);
                $locked->addRespect($reward['respect']);
                $locked->save();
            });
        } catch (UniqueConstraintViolationException) {
            $duplicateMessage = 'Je hebt de beloning voor '.$platformData['name'].' al geclaimd.';

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $duplicateMessage,
                    'url' => $platformData['url'],
                ]);
            }

            return redirect()->route('community.index')->with('error', $duplicateMessage);
        }

        $character->refresh();

        $successMessage = '🎉 Bedankt voor het volgen van ons '.$platformData['name'].'! Je hebt € '.number_format($reward['cash'], 0, ',', '.').' cash, '.$reward['respect'].' respect en '.$reward['energy'].' energie ontvangen!';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $successMessage,
                'url' => $platformData['url'],
                'platform' => $platform,
                'character' => [
                    'cash' => $character->cash,
                    'respect' => $character->respect,
                    'energy' => $character->energy,
                ],
            ]);
        }

        return redirect()->route('community.index')->with('success', $successMessage);
    }
}
