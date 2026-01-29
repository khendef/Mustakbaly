<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Application;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
        $exceptions->render(function (Request $request, Throwable $exception) {

           // Unauthenticated
        if ($exception instanceof AuthenticationException) {
            return Controller::error('Unauthenticated', 401);
        }

        // Not found
        if ($exception instanceof NotFoundHttpException) {
            return Controller::error('Not found', 404);
        }

        if ($exception instanceof ModelNotFoundException) {
            $model = class_basename($exception->getModel());
            return Controller::error("$model Not found", 404);
        }

        // Validation

  if ($exception instanceof ValidationException) {
            return Controller::error($exception->errors(), 422);
        }

        // HttpException
        if ($exception instanceof HttpException) {
            return Controller::error($exception->getMessage() ?: 'Error', $exception->getStatusCode());
        }

        //role
        if ($exception instanceof \Spatie\Permission\Exceptions\UnauthorizedException) {
            return Controller::error('Unauthorized', 403);
        }
        // Authorization
       if ($exception instanceof AuthorizationException) {
        return Controller::error($exception->getMessage(), 403);

    }

        return null;
    });

})->create();
