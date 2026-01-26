<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Learning Module API Routes
|--------------------------------------------------------------------------
|
| All routes are versioned under /api/v1 prefix.
| All routes require authentication via JWT.
|
*/

Route::prefix('v1')->middleware('auth:api')->group(function () {
    // Import all versioned route files
    require __DIR__ . '/v1/course-types.php';
    require __DIR__ . '/v1/courses.php';
    require __DIR__ . '/v1/units.php';
    require __DIR__ . '/v1/lessons.php';
    require __DIR__ . '/v1/enrollments.php';
});
