<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
 * Base controller class for all application controllers.
 * Provides a common foundation for controller functionality in the Laravel application.
 */
abstract class Controller
{
    use AuthorizesRequests;

    /**
     * JSON response options for consistent encoding
     */
    private const JSON_OPTIONS = JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION;

    /**
     * Return a standardized success JSON response.
     *
     * @param mixed $data
     * @param string $message
     * @param int $code
     * @return JsonResponse
     */
    protected function successResponse(
        mixed $data = null,
        string $message = 'Operation successful',
        int $code = Response::HTTP_OK
    ): JsonResponse {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ], $code, [], self::JSON_OPTIONS);
    }

    /**
     * Return a standardized error JSON response.
     *
     * @param string $message
     * @param int $code
     * @param array|null $errors
     * @param string|null $errorCode
     * @param string|null $hint
     * @return JsonResponse
     */
    protected function errorResponse(
        string $message = 'Operation failed',
        int $code = Response::HTTP_BAD_REQUEST,
        ?array $errors = null,
        ?string $errorCode = null,
        ?string $hint = null
    ): JsonResponse {
        $response = [
            'status' => 'error',
            'message' => $message,
            'data' => null,
        ];

        if ($errorCode !== null) {
            $response['error_code'] = $errorCode;
        }

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        if ($hint !== null) {
            $response['hint'] = $hint;
        }

        return response()->json($response, $code, [], self::JSON_OPTIONS);
    }

    /**
     * Return a success response for created resources (201).
     *
     * @param mixed $data
     * @param string $message
     * @return JsonResponse
     */
    protected function createdResponse(
        mixed $data = null,
        string $message = 'Resource created successfully'
    ): JsonResponse {
        return $this->successResponse($data, $message, Response::HTTP_CREATED);
    }

    /**
     * Return a success response with no content (204).
     *
     * @return JsonResponse
     */
    protected function noContentResponse(): JsonResponse
    {
        return response()->json(null, Response::HTTP_NO_CONTENT, [], self::JSON_OPTIONS);
    }

    /**
     * Return a validation error response (422).
     *
     * @param array $errors
     * @param string $message
     * @return JsonResponse
     */
    protected function validationErrorResponse(
        array $errors,
        string $message = 'Validation failed'
    ): JsonResponse {
        return $this->errorResponse($message, Response::HTTP_UNPROCESSABLE_ENTITY, $errors);
    }

    /**
     * Return an unauthorized error response (401).
     *
     * @param string $message
     * @return JsonResponse
     */
    protected function unauthorizedResponse(
        string $message = 'Unauthenticated. Please login to access this resource.'
    ): JsonResponse {
        return $this->errorResponse($message, Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Return a forbidden error response (403).
     *
     * @param string $message
     * @return JsonResponse
     */
    protected function forbiddenResponse(
        string $message = 'You do not have permission to access this resource.'
    ): JsonResponse {
        return $this->errorResponse($message, Response::HTTP_FORBIDDEN);
    }

    /**
     * Return a not found error response (404).
     *
     * @param string $message
     * @return JsonResponse
     */
    protected function notFoundResponse(
        string $message = 'The requested resource was not found.'
    ): JsonResponse {
        return $this->errorResponse($message, Response::HTTP_NOT_FOUND);
    }

    /**
     * Return a server error response (500).
     *
     * @param string $message
     * @param string|null $errorCode
     * @param string|null $hint
     * @param \Throwable|null $exception
     * @return JsonResponse
     */
    protected function serverErrorResponse(
        string $message = 'An error occurred while processing your request.',
        ?string $errorCode = 'INTERNAL_SERVER_ERROR',
        ?string $hint = null,
        ?\Throwable $exception = null
    ): JsonResponse {
        // If exception is provided, extract meaningful message
        if ($exception !== null) {
            $exceptionMessage = $this->extractExceptionMessage($exception);
            if ($exceptionMessage !== null) {
                $message = $exceptionMessage;
            }
        }

        $defaultHint = $hint ?? 'Please try again later. If the problem persists, contact support.';
        return $this->errorResponse($message, Response::HTTP_INTERNAL_SERVER_ERROR, null, $errorCode, $defaultHint);
    }

    /**
     * Extract readable message from exception.
     *
     * @param \Throwable $exception
     * @return string|null
     */
    protected function extractExceptionMessage(\Throwable $exception): ?string
    {
        $message = $exception->getMessage();

        // Handle database exceptions
        if ($exception instanceof \Illuminate\Database\QueryException) {
            if (str_contains($message, 'cannot be null')) {
                $column = $this->extractColumnName($message);
                return "The field '{$column}' is required and cannot be empty.";
            }
            if (str_contains($message, 'Duplicate entry')) {
                return "This record already exists. Please use a different value.";
            }
            if (str_contains($message, 'foreign key constraint')) {
                return "Cannot perform this operation. The referenced record does not exist.";
            }
        }

        // Handle validation exceptions
        if ($exception instanceof \Illuminate\Validation\ValidationException) {
            return "Validation failed. Please check your input.";
        }

        // Use exception message if it's user-friendly
        if (!empty($message) && !str_contains($message, 'SQLSTATE') && !str_contains($message, 'Call to')) {
            return $message;
        }

        return null;
    }

    /**
     * Extract column name from error message.
     *
     * @param string $message
     * @return string
     */
    protected function extractColumnName(string $message): string
    {
        if (preg_match("/Column '([^']+)' cannot be null/", $message, $matches)) {
            return str_replace('_', ' ', ucwords($matches[1], '_'));
        }
        return 'field';
    }
}
