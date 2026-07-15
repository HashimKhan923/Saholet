<?php

use App\Http\Middleware\EnsureNotSuspended;
use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            SetLocale::class,
            SecurityHeaders::class,
        ]);

        $middleware->alias([
            'role' => EnsureUserHasRole::class,
            'guest' => RedirectIfAuthenticated::class,
            'not.suspended' => EnsureNotSuspended::class,
        ]);

        // JazzCash/EasyPaisa POST their return callback from off-site — they
        // can't carry our session's CSRF token. Signature verification in
        // PaymentReturnController is what actually authenticates these requests.
        $middleware->validateCsrfTokens(except: [
            'payments/*/return',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();