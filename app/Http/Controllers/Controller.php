<?php

namespace App\Http\Controllers;
use Illuminate\Routing\Controller as BaseController;

abstract class Controller extends BaseController
{
    /**
     * Standardized success response.
     *
     * @param mixed $data
     * @param string $message
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected static function success($data, $message = 'Operation successful', $code = 200)
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ], $code);
    }

    /**
     * Standardized error response.
     *
     * @param string $message
     * @param int $code
     * @param mixed $errors
     * @return \Illuminate\Http\JsonResponse
     */
    protected static function error($message = 'An error occurred', $code = 500, $errors = null)
    {
        $response = [
            'status' => 'error',
            'message' => $message,
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

    /**
     * Standardized paginate response.
     *
     * @param \Illuminate\Pagination\LengthAwarePaginator $paginateData
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    protected static function paginate($paginateData, $message = 'Data retrieved successfully')
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $paginateData->items(),
            'meta' => [
                'current_page' => $paginateData->currentPage(),
                'per_page' => $paginateData->perPage(),
                'total' => $paginateData->total(),
                'last_page' => $paginateData->lastPage()
            ]
        ], 200);
    }
}
