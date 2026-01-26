<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Http\Request;
use App\Exceptions\Handler;

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
        // Handle ModelNotFoundException before it gets converted to NotFoundHttpException
        $exceptions->render(function (Request $request, \Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Only handle for API requests
            if ($request->expectsJson() || $request->is('api/*') || $request->wantsJson() || str_starts_with($request->path(), 'api/')) {
                $handler = app(Handler::class);
                return $handler->handleModelNotFoundException($e);
            }
            // Let Laravel handle it for web requests
            return null;
        });
        
        // Delegate all other exception rendering to the custom Handler class
        $exceptions->render(function (Request $request, \Throwable $e) {
            $handler = app(Handler::class);
            return $handler->render($request, $e);
        });
    })
    ->create();
