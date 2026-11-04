<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $character = $request->user()->character;
        abort_unless($character, 404);

        $notifications = $character->notifications()->orderByDesc('id')->paginate(20);
        $unreadCount = $character->notifications()->where('is_read', false)->count();

        return view('game.notifications.index', compact('character', 'notifications', 'unreadCount'));
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $character = $request->user()->character;
        abort_unless($character, 404);

        $character->notifications()->where('is_read', false)->update(['is_read' => true]);

        return redirect()->route('notifications.index');
    }
}
