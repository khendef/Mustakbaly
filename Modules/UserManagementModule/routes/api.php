<?php

use Illuminate\Support\Facades\Route;
use Modules\UserManagementModule\Http\Controllers\UserManagementModuleController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('usermanagementmodules', UserManagementModuleController::class)->names('usermanagementmodule');
});
