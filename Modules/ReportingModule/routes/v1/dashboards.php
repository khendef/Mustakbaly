<?php

use Illuminate\Support\Facades\Route;
use Modules\ReportingModule\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| Dashboard Routes (v1)
|--------------------------------------------------------------------------
*/

Route::prefix('dashboards')->group(function () {
    Route::get('/learner', [DashboardController::class, 'learnerDashboard'])
        ->name('dashboards.learner');
    
    Route::get('/instructor', [DashboardController::class, 'instructorDashboard'])
        ->name('dashboards.instructor');
    
    Route::get('/admin', [DashboardController::class, 'adminDashboard'])
        ->name('dashboards.admin');
});

