<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Reporting Module API Routes
|--------------------------------------------------------------------------
|
| All routes are versioned under /api/v1 prefix.
|
*/

Route::prefix('v1')->group(function () {
    // Import all versioned route files
    require __DIR__ . '/v1/dashboards.php';
    require __DIR__ . '/v1/reports.php';
});
