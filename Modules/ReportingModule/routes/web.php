<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Reporting Module Web Routes
|--------------------------------------------------------------------------
|
| Web routes for the Reporting Module.
| Note: Add your web routes here when needed.
|
*/

Route::middleware(['auth', 'verified'])->group(function () {
    // Add web routes here as needed
    // Example: Route::get('/reporting', [YourController::class, 'index'])->name('reporting.index');
});
