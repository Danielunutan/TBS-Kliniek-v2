<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class TravelController extends Controller
{
    public function index()
    {
        $character = Auth::user()->character;

        return view('game.travel.index', compact('character'));
    }
}
