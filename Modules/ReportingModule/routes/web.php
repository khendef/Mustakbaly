<?php

use Illuminate\Support\Facades\Route;
use Modules\ReportingModule\Http\Controllers\ReportingModuleController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('reportingmodules', ReportingModuleController::class)->names('reportingmodule');
});
