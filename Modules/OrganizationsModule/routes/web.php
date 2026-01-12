<?php

use Illuminate\Support\Facades\Route;
use Modules\OrganizationsModule\Http\Controllers\OrganizationsModuleController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('organizationsmodules', OrganizationsModuleController::class)->names('organizationsmodule');
});
