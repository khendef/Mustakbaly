<?php

use Illuminate\Support\Facades\Route;
use Modules\MediaModule\Http\Controllers\MediaModuleController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('mediamodules', MediaModuleController::class)->names('mediamodule');
});
