<?php

use App\Exceptions\BookingException;
use App\Support\AdminExceptionNotifier;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Business-rule violations are expected outcomes, not errors.
        $exceptions->dontReport([BookingException::class]);

        // Anything else that reaches the handler is a bug: email the admin.
        $exceptions->report(fn (Throwable $e) => app(AdminExceptionNotifier::class)($e));
    })->create();
