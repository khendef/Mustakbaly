<?php

use Illuminate\Support\Facades\Route;
use Modules\SystemModule\Http\Controllers\SystemModuleController;

Route::middleware(['auth:api'])->prefix('v1')->group(function () {
    Route::apiResource('systemmodules', SystemModuleController::class)->names('systemmodule');
});
