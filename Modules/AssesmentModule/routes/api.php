<?php

use Illuminate\Support\Facades\Route;
use Modules\AssesmentModule\Http\Controllers\AssesmentModuleController;

Route::middleware(['auth:api'])->prefix('v1')->group(function () {
    Route::apiResource('assesmentmodules', AssesmentModuleController::class)->names('assesmentmodule');
});
