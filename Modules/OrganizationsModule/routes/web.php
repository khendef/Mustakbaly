<?php

use Illuminate\Support\Facades\Route;
use Modules\OrganizationsModule\Http\Controllers\OrganizationsModuleController;
use Modules\OrganizationsModule\Http\Controllers\OrganizationController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('organizationsmodules', OrganizationsModuleController::class)->names('organizationsmodule');
    Route::resource('organizations', OrganizationController::class)->names('organizations');
});
