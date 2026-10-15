<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Carbon::setLocale('nl');
        App::setLocale('nl');
        setlocale(LC_TIME, 'nl_NL.UTF-8', 'nl_NL', 'nl', 'nld_nld');
    }
}
