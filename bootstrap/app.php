<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Exceptions\Handler;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        channels: __DIR__ . '/../routes/channels.php',
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('api')->prefix('api')->group(
                base_path('Modules/UserManagementModule/routes/api.php')
            );
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'requested_organization' => \Modules\UserManagementModule\Http\Middleware\GetRequestedOrganization::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule): void {
        // Clean activity logs older than configured days (default: 365 days)
        $schedule->command('activitylog:clean')->daily();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        /*
         * API and JSON exception handling is delegated to App\Exceptions\Handler
         * so all API errors use a single, consistent format (status, message, error_code, hint, errors).
         */
        $exceptions->render(function (Throwable $e, Request $request) {
            // Preserve responses thrown explicitly (e.g. RoleService, PermissionService).
            if ($e instanceof HttpResponseException) {
                return $e->getResponse();
            }

            // Delegate API/JSON requests to the Handler for consistent error format and messages.
            if (Handler::requestExpectsApiResponse($request)) {
                return null;
            }

            return null;
        });
    })->create();
