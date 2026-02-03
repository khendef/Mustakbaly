<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Modules\ReportingModule\Http\Controllers\DonorDashboardController;

/**
 |----------------------------------------------------
 | Donor Dashboard Routes
 | ---------------------------------------------------
 * Routes for donors to access their dashboard.
 * Security:
 * 1. JWT Auth
 * 2. Donor Role
 * @prefix api/v1
 * @auth   Required (JWT)
 * @access Donor Only
 */
Route::group(['middleware' => ['auth:api', 'role:donor']], function () {

    /**
    |--------------------------------------------------------------------------
    | Donor Dashboard (Reporting Module)
    |--------------------------------------------------------------------------
     */
    /**
     * @name   Donor Dashboard
     * @path   GET /api/v1/dashboard
     * @desc   Retrieve dashboard data for the authenticated donor.
     * @controller DonorDashboardController@dashboard
     */
    Route::get('/dashboard', function () {
        return app(DonorDashboardController::class)->dashboard(Auth::id());
    });
});
