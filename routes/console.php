<?php

use App\Models\Character;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Str;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('chat:directie-tiktok-announcement', function () {
    $user = User::firstOrCreate(
        ['email' => 'directie@tbs-kliniek.nl'],
        [
            'name' => 'Kliniek Directie',
            'password' => bcrypt(Str::random(32)),
            'role' => 'admin',
            'is_staff' => true,
            'is_onboarded' => true,
            'email_verified_at' => now(),
        ]
    );

    if ($user->role !== 'admin' || ! $user->is_staff) {
        $user->update(['role' => 'admin', 'is_staff' => true]);
    }

    $character = Character::firstOrCreate(
        ['user_id' => $user->id],
        [
            'name' => 'Kliniek Directie',
            'username' => 'directie',
            'display_name' => 'Kliniek Directie',
            'title' => 'Hoofddirectie & Beheer',
            'avatar' => 'assets/tbs_logo.jpg',
            'level' => 99,
            'stars' => 250000,
            'status_message' => 'Officiële Kliniek Mededelingen',
            'profile_color' => 'purple',
        ]
    );

    $message = '📢 KLINIEK DIRECTIE MEDEDELING 🎬 Iedereen die onze nieuwe TikTok herplaatst, liked en reageert ontvangt 250 sterren! ⭐ Bekijk hem hier: https://vm.tiktok.com/ZGdCoXVLy/ ⚠️ Vergeet niet om in je TikTok reactie je in-game @username te plaatsen zodat we je beloning direct kunnen uitkeren!';

    $chatMessage = ChatMessage::create([
        'character_id' => $character->id,
        'channel' => 'global',
        'message' => $message,
    ]);

    $this->info("TikTok Directie announcement verstuurd naar Plein Chat (ID: {$chatMessage->id})");
})->purpose('Send TikTok promotion announcement to global chat as Kliniek Directie');

Schedule::command('gang:pay-wages')->everyMinute();
Schedule::command('battle:settle')->everyMinute();
Schedule::command('patients:pay-allowances')->everyFifteenMinutes();
Schedule::command('vault:evening-drop')->dailyAt('22:00')->timezone('Europe/Amsterdam');
Schedule::command('event:resolve-cash-hunt')->dailyAt('00:00')->timezone('Europe/Amsterdam');
Schedule::command('lottery:draw')->everyMinute();
