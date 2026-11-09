<?php

namespace App\Http\Controllers;

use App\Models\Bounty;
use App\Models\Character;
use App\Models\GameNotification;
use App\Services\EconomyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BountyController extends Controller
{
    public function __construct(
        protected EconomyService $economy,
    ) {}

    public function index(): View
    {
        $character = Auth::user()?->character;
        if (! $character) {
            abort(404, 'Karakter niet gevonden.');
        }

        // Active bounties, grouped by target or listed chronologically
        $bounties = Bounty::with(['target', 'placedBy'])
            ->where('is_claimed', false)
            ->whereHas('target', fn ($q) => $q->playable())
            ->orderByDesc('amount')
            ->get();

        // Recently claimed bounties
        $claimedBounties = Bounty::with(['target', 'placedBy', 'claimedBy'])
            ->where('is_claimed', true)
            ->orderByDesc('claimed_at')
            ->limit(10)
            ->get();

        // Potential targets to place bounty on (other active players)
        $potentialTargets = Character::where('id', '!=', $character->id)
            ->playable()
            ->orderBy('name')
            ->limit(200)
            ->get();

        return view('game.bounties.index', compact('character', 'bounties', 'claimedBounties', 'potentialTargets'));
    }

    public function store(Request $request): RedirectResponse
    {
        $character = Auth::user()?->character;
        if (! $character) {
            return redirect()->route('dashboard')->with('error', 'Geen personage gevonden.');
        }

        if ($character->isImprisoned()) {
            return redirect()->route('prison')->with('error', 'Vanuit de isoleercel kun je geen kopgeld uitloven!');
        }

        if ($character->isComatose()) {
            return back()->with('error', 'Je verkeert in Code Blauw (Hartstilstand)! Dien eerst een '.Character::REVIVE_SERUM_NAME.' toe.');
        }

        $request->validate([
            'amount' => 'required|integer|min:1000|max:10000000',
            'reason' => 'nullable|string|max:150',
            'target_name' => 'nullable|string',
            'target_id' => 'nullable|integer',
        ]);

        $target = null;
        if ($request->filled('target_id')) {
            $target = Character::find($request->input('target_id'));
        } elseif ($request->filled('target_name')) {
            $target = Character::where('name', trim($request->input('target_name')))->first();
        }

        if (! $target) {
            return back()->with('error', 'Selecteer een geldige patiënt om een kopgeld op te zetten.');
        }

        if ($target->id === $character->id) {
            return back()->with('error', 'Je kunt geen kopgeld op je eigen hoofd zetten!');
        }

        if ($target->isGameExempt() || $character->isGameExempt()) {
            return back()->with('error', 'Je kunt geen kopgeld zetten op een kliniekeigenaar!');
        }

        $amount = (int) $request->input('amount');

        $deducted = DB::transaction(function () use ($character, $target, $amount, $request): bool {
            // Deduct cash from placer atomically
            if (! $this->economy->deductCash($character, $amount)) {
                return false;
            }

            // Create bounty
            Bounty::create([
                'target_character_id' => $target->id,
                'placed_by_character_id' => $character->id,
                'amount' => $amount,
                'reason' => $request->input('reason') ?: 'Vogelvrij verklaard door rivaal.',
                'is_claimed' => false,
            ]);

            // Send warning notification to target
            GameNotification::create([
                'character_id' => $target->id,
                'type' => 'bounty_placed',
                'title' => 'Er staat KOPGELD op je hoofd!',
                'message' => "Patiënt {$character->name} heeft een kopgeld van €".number_format($amount, 0, ',', '.').' op jouw hoofd gezet! Kijk uit voor premiejagers.',
                'is_read' => false,
            ]);

            return true;
        });

        if (! $deducted) {
            return back()->with('error', 'Je hebt niet genoeg contant geld (€ '.number_format($amount, 0, ',', '.').' nodig) om dit kopgeld uit te loven.');
        }

        return redirect()->route('bounties.index')
            ->with('success', 'Kopgeld van €'.number_format($amount, 0, ',', '.')." succesvol uitgeloofd op {$target->name}!");
    }
}
