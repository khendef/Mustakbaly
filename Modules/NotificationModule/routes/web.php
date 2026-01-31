<?php

use Illuminate\Support\Facades\Route;
use Modules\NotificationModule\Http\Controllers\NotificationModuleController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('notificationmodules', NotificationModuleController::class)->names('notificationmodule');
});
