<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'superuser' => \App\Http\Middleware\EnsureSuperuser::class,
        ]);
        
        // Also register as route middleware for Laravel 11/12
        $middleware->web(append: [
            // web middleware
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
