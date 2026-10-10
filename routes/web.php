<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GangController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\MissionController;
use App\Http\Controllers\PrisonController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\TheftController;
use App\Http\Controllers\TrainingController;
use Illuminate\Support\Facades\Route;

// Guest Authentication Routes
Route::middleware('guest')->group(function (): void {
    Route::get('/', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::post('/demo-login', [AuthController::class, 'demoLogin'])->name('login.demo');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
});

// Authenticated Game Routes
Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Missions
    Route::get('/missies', [MissionController::class, 'index'])->name('missions.index');
    Route::post('/missies/{mission}/uitvoeren', [MissionController::class, 'execute'])->name('missions.execute');

    // Training / Gym
    Route::get('/training', [TrainingController::class, 'index'])->name('training.index');
    Route::post('/training', [TrainingController::class, 'train'])->name('training.train');

    // Crime & Theft
    Route::get('/diefstal', [TheftController::class, 'index'])->name('theft.index');
    Route::post('/diefstal', [TheftController::class, 'steal'])->name('theft.steal');

    // Bank
    Route::get('/bank', [BankController::class, 'index'])->name('bank.index');
    Route::post('/bank/storten', [BankController::class, 'deposit'])->name('bank.deposit');
    Route::post('/bank/opnemen', [BankController::class, 'withdraw'])->name('bank.withdraw');

    // Shop & Inventory
    Route::get('/winkel', [ShopController::class, 'index'])->name('shop.index');
    Route::post('/winkel/{item}/kopen', [ShopController::class, 'buy'])->name('shop.buy');
    Route::post('/inventaris/{inventoryItem}/gebruiken', [ShopController::class, 'consume'])->name('shop.consume');

    // Prison / Isoleercel
    Route::get('/gevangenis', [PrisonController::class, 'index'])->name('prison');
    Route::post('/gevangenis/ontsnappen', [PrisonController::class, 'escape'])->name('prison.escape');

    // Player Profile & Guestbook
    Route::get('/profiel', [ProfileController::class, 'show'])->name('profile.me');
    Route::get('/speler/{name}', [ProfileController::class, 'show'])->name('profile.show');
    Route::post('/speler/{name}/gastenboek', [ProfileController::class, 'postGuestbook'])->name('guestbook.post');
    Route::post('/profiel/bijwerken', [ProfileController::class, 'updateBio'])->name('profile.update');

    // Gangs
    Route::get('/gangs', [GangController::class, 'index'])->name('gangs.index');

    // Leaderboard
    Route::get('/ranglijst', [LeaderboardController::class, 'index'])->name('leaderboard.index');
});
