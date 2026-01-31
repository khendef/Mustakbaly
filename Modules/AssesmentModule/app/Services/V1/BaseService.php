<?php

namespace Modules\AssesmentModule\Services\V1;
use Illuminate\Support\Facades\Log;
use Throwable;
abstract class BaseService
{
    public function handle() {}
    protected function ok(string $message, mixed $data = null, int $code = 200): array
    {
        return [
            'success' => true,
            'message' => $message,
            'data'    => $data,
            'code'    => $code,
        ];
    }

    protected function fail(string $message, ?Throwable $e =null, int $code = 500): array
    {
        if ($e) {
            Log::error($message, ['exception' => $e]);
        }

        return [
            'success' => false,
            'message' => $message,
            'error'   => $e?->getMessage(),
            'code'    => $code,
        ];
    }


}
