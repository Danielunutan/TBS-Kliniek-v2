<?php

namespace App\Http\Controllers;

use App\Models\Character;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ComplexController extends Controller
{
    /**
     * Show the 2D isometric virtual world client.
     */
    public function index(Request $request): View
    {
        // Auto-login test user if running locally and unauthenticated so testing is 1-click effortless
        if (! Auth::check() && app()->environment('local')) {
            $testUser = User::where('email', 'demo@tbskliniek.nl')->first() ?? User::first();
            if ($testUser) {
                Auth::login($testUser);
            }
        }

        $user = Auth::user();
        $character = $user?->character;

        if (! $character && $user) {
            $character = Character::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'name' => $user->name ?? 'Gedetineerde',
                    'cash' => 5000,
                    'bank' => 25000,
                    'level' => 1,
                    'gender' => 'male',
                    'avatar' => 1,
                ]
            );
        }

        return view('game.complex.index', compact('user', 'character'));
    }
}

