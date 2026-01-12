<?php

use Illuminate\Support\Facades\Route;
use Modules\SystemModule\Http\Controllers\SystemModuleController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('systemmodules', SystemModuleController::class)->names('systemmodule');
});
