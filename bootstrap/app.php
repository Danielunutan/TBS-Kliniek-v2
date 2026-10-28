<?php

use App\Http\Middleware\CheckMaintenanceMode;
use App\Http\Middleware\EnsureUserIsNotBanned;
use App\Http\Middleware\EnsureUserIsOnboarded;
use App\Http\Middleware\PreserveSessionFlashOnAjax;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\UpdateUserOnlineStatus;
use App\Models\CharacterItem;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'webhooks/*',
            'api/*',
        ]);

        $middleware->web(append: [
            SecurityHeaders::class,
            UpdateUserOnlineStatus::class,
            CheckMaintenanceMode::class,
            EnsureUserIsNotBanned::class,
            EnsureUserIsOnboarded::class,
            PreserveSessionFlashOnAjax::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (MethodNotAllowedHttpException $e, Request $request) {
            if ($request->isMethod('GET')) {
                $path = trim($request->path(), '/');
                $target = match (true) {
                    str_starts_with($path, 'gangs') || str_starts_with($path, 'bende') => route('gangs.index'),
                    str_starts_with($path, 'winkel') => route('shop.index'),
                    str_starts_with($path, 'liquidatie') => route('hitman.index'),
                    str_starts_with($path, 'bank') => route('bank.index'),
                    str_starts_with($path, 'casino') => route('casino.index'),
                    str_starts_with($path, 'heists') || str_starts_with($path, 'heist') => route('heists.index'),
                    str_starts_with($path, 'gevangenis') => route('prison'),
                    str_starts_with($path, 'ziekenboeg') => route('infirmary.index'),
                    str_starts_with($path, 'dagbesteding') => route('daycare.index'),
                    str_starts_with($path, 'training') => route('training.index'),
                    str_starts_with($path, 'missies') => route('missions.index'),
                    str_starts_with($path, 'smokkelen') => route('smuggle.index'),
                    str_starts_with($path, 'hosselen') => route('hustle.index'),
                    str_starts_with($path, 'diefstal') => route('theft.index'),
                    str_starts_with($path, 'zakkenrollen') => route('pickpocket.index'),
                    str_starts_with($path, 'luchtplaats') => route('fight.index'),
                    str_starts_with($path, 'loterij') => route('lottery.index'),
                    str_starts_with($path, 'kluis-kraak') => route('vault.index'),
                    str_starts_with($path, 'garage') => route('garage.index'),
                    str_starts_with($path, 'rad-van-fortuin') => route('daily.index'),
                    default => route('dashboard'),
                };

                return redirect($target)->with('info', 'De actie kon niet direct via GET worden verzonden. Je bent veilig teruggestuurd naar het overzicht.');
            }

            return response()->view('errors.405', [], 405);
        });

        $exceptions->render(function (ModelNotFoundException $e, Request $request) {
            if ($e->getModel() === CharacterItem::class || str_contains($e->getMessage(), 'CharacterItem')) {
                if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Dit voorwerp is al verbruikt of niet meer aanwezig in je inventaris.',
                    ], 200);
                }

                return redirect()->route('shop.index', ['tab' => 'inventaris'])
                    ->with('info', 'Dit voorwerp is al verbruikt of niet meer aanwezig in je inventaris.');
            }
        });

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
