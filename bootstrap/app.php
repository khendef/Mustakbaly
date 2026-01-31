<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
<<<<<<< HEAD
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
=======
>>>>>>> 8f82310be1ed3956233161a9a739ff5b62ca6e3c
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule): void {
        // Clean activity logs older than configured days (default: 365 days)
        $schedule->command('activitylog:clean')->daily();
    })
    ->withExceptions(function (Exceptions $exceptions): void {

    })->create();
