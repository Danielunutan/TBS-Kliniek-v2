<?php

namespace App\Http\Controllers;

use App\Models\UserWarning;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WarningController extends Controller
{
    /**
     * Acknowledge an official warning issued to the authenticated player.
     */
    public function acknowledge(Request $request, UserWarning $warning): RedirectResponse
    {
        if ($warning->user_id !== Auth::id()) {
            abort(403, 'Je hebt geen toegang tot deze waarschuwing.');
        }

        $warning->acknowledge();

        return back()->with('success', 'Officiële waarschuwing bevestigd. Houd je aan de huisregels om verdere sancties te voorkomen.');
    }
}
