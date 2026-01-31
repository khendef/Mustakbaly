<?php

use Illuminate\Support\Facades\Route;
use Modules\NotificationModule\Http\Controllers\NotificationModuleController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('notificationmodules', NotificationModuleController::class)->names('notificationmodule');
});
