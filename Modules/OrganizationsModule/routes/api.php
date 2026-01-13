<?php

use Illuminate\Support\Facades\Route;
use Modules\OrganizationsModule\Http\Controllers\OrganizationsModuleController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('organizationsmodules', OrganizationsModuleController::class)->names('organizationsmodule');
});
