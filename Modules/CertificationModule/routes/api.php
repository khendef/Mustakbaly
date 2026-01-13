<?php

use Illuminate\Support\Facades\Route;
use Modules\CertificationModule\Http\Controllers\CertificationModuleController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('certificationmodules', CertificationModuleController::class)->names('certificationmodule');
});
