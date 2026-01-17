<?php

use Illuminate\Support\Facades\Route;
use Modules\MediaModule\Http\Controllers\MediaModuleController;

Route::middleware(['auth:api'])->prefix('v1')->group(function () {
    Route::apiResource('mediamodules', MediaModuleController::class)->names('mediamodule');
});
