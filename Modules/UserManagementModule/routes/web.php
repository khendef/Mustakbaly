<?php

use Illuminate\Support\Facades\Route;
use Modules\UserManagementModule\Http\Controllers\UserManagementModuleController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('usermanagementmodules', UserManagementModuleController::class)->names('usermanagementmodule');
});
