<?php

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

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

        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->expectsJson() && !$request->is('api/*')) {
                return null;
            }

            if ($e instanceof HttpResponseException) {
                return $e->getResponse();
            }

            // Route-model binding "not found" often comes as NotFoundHttpException
            // with a ModelNotFoundException as its previous exception.
            if (
                $e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
                && $e->getPrevious() instanceof \Illuminate\Database\Eloquent\ModelNotFoundException
            ) {
                $e = $e->getPrevious();
            }

            Log::error(
                "API Exception: " . get_class($e) . " - Message: " . $e->getMessage() . " - File: " . $e->getFile() . " - line: " . $e->getLine()
            );

            // If a plain Exception was thrown with a valid HTTP status code (e.g. new Exception('...', 422)),
            // respect it instead of always returning 500.
            $exceptionCode = (int) $e->getCode();
            $fallbackStatusCode = ($exceptionCode >= 400 && $exceptionCode < 600) ? $exceptionCode : 500;

            $responseDetails = match (get_class($e)) {
                ValidationException::class => [
                    'statusCode' => 422,
                    'message' => 'Validation failed. Please check your input.',
                    /** @var ValidationException $e */
                    'errors' => $e->validator?->errors()?->toArray() ?? [],
                ],
                AuthenticationException::class => [
                    'statusCode' => 401,
                    'message' => 'Unauthenticated',
                ],
                AuthorizationException::class => [
                    'statusCode' => 403,
                    'message' => 'This action is unauthorized',
                ],
                \Spatie\Permission\Exceptions\UnauthorizedException::class => [
                    'statusCode' => 403,
                    'message' => 'User does not have the right permissions.',
                ],
                \Illuminate\Database\Eloquent\ModelNotFoundException::class => [
                    'statusCode' => 404,
                    'message' => 'This requested resource was not found',
                ],
                NotFoundResourceException::class => [
                    'statusCode' => 404,
                    'message' => 'This requested resource was not found',
                ],
                QueryException::class => [
                    // map common DB errors to clean API responses
                    'statusCode' => (function () use ($e) {
                        $message = $e->getMessage();
                        if (str_contains($message, 'Duplicate entry')) {
                            return 409;
                        }
                        if (str_contains($message, 'foreign key constraint') || str_contains($message, 'cannot be null')) {
                            return 422;
                        }
                        return 400;
                    })(),
                    'message' => (function () use ($e) {
                        $message = $e->getMessage();
                        if (str_contains($message, 'Duplicate entry')) {
                            return 'This record already exists.';
                        }
                        if (str_contains($message, 'foreign key constraint')) {
                            // Try to extract the FK column for a more helpful message.
                            if (preg_match("/FOREIGN KEY \\(`([^`]+)`\\)/", $message, $matches)) {
                                $column = $matches[1];
                                return "The selected {$column} is invalid (not found).";
                            }
                            return 'One of the referenced resources does not exist.';
                        }
                        if (str_contains($message, 'cannot be null')) {
                            if (preg_match("/Column '([^']+)' cannot be null/", $message, $matches)) {
                                return "Missing required field: {$matches[1]}.";
                            }
                            return 'Missing required field(s).';
                        }
                        return app()->isProduction() ? 'A database error occurred.' : $message;
                    })(),
                ],
                default => [
                    'statusCode' => $e instanceof HttpExceptionInterface ? $e->getStatusCode() : $fallbackStatusCode,
                    'message' => app()->isProduction() ? 'An unexpected server error occured' : $e->getMessage()

                ]
            };

            $payload = [
                'status' => 'error',
                'message' => $responseDetails['message']
            ];

            if (!empty($responseDetails['errors'])) {
                $payload['errors'] = $responseDetails['errors'];
            }

            $statusCode = ($responseDetails['statusCode'] >= 400 && $responseDetails['statusCode'] < 600)
                ? $responseDetails['statusCode'] : 500;

            return response()->json($payload, $statusCode);
        });
    })->create();
