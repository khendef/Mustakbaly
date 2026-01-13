<?php

use Illuminate\Support\Facades\Route;
use Modules\CertificationModule\Http\Controllers\CertificationModuleController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('certificationmodules', CertificationModuleController::class)->names('certificationmodule');
});
