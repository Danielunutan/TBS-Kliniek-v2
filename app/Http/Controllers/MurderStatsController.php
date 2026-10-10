<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class MurderStatsController extends Controller
{
    public function index()
    {
        $character = Auth::user()->character;

        return view('game.murder_stats.index', compact('character'));
    }
}
