<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('gang:pay-wages')->hourly();
Schedule::command('patients:pay-allowances')->everyFifteenMinutes();
Schedule::command('vault:evening-drop')->dailyAt('22:00')->timezone('Europe/Amsterdam');
