<?php

use Illuminate\Support\Facades\Route;
use Modules\LearningModule\Http\Controllers\EnrollmentController;

/*
|--------------------------------------------------------------------------
| Enrollment Routes (v1)
|--------------------------------------------------------------------------
*/

Route::prefix('enrollments')->group(function () {
    // Standard CRUD operations
    Route::get('/', [EnrollmentController::class, 'index'])->name('enrollments.index');
    Route::post('/', [EnrollmentController::class, 'store'])->name('enrollments.store');
    Route::get('/{enrollment}', [EnrollmentController::class, 'show'])->name('enrollments.show');

    // Enrollment status management
    Route::put('/{enrollment}/status', [EnrollmentController::class, 'updateStatus'])->name('enrollments.update-status');
    Route::post('/{enrollment}/reactivate', [EnrollmentController::class, 'reactivate'])->name('enrollments.reactivate');

    // Enrollment progress
    Route::get('/{enrollment}/progress', [EnrollmentController::class, 'getProgress'])->name('enrollments.progress');
});
