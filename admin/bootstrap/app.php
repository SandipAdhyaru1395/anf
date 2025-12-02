<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Do NOT override the core 'auth' middleware; Sanctum uses it for API guards.
        // Use a different alias for your web auth redirect middleware.
        $middleware->alias([
            'auth.web' => App\Http\Middleware\AuthMiddleware::class,
            'permission' => App\Http\Middleware\PermissionMiddleware::class,
            'sidebar' => App\Http\Middleware\SidebarMiddleware::class,
            'store.maintenance' => App\Http\Middleware\StoreMaintenanceMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
