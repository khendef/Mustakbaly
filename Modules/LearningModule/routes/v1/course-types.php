<?php

use Illuminate\Support\Facades\Route;
use Modules\LearningModule\Http\Controllers\CourseTypeController;

/*
|--------------------------------------------------------------------------
| Course Type Routes (v1)
|--------------------------------------------------------------------------
*/

Route::prefix('course-types')->group(function () {
    // Standard CRUD operations
    Route::get('/', [CourseTypeController::class, 'index'])->name('course-types.index');
    Route::post('/', [CourseTypeController::class, 'store'])->name('course-types.store');
    Route::get('/{courseType}', [CourseTypeController::class, 'show'])->name('course-types.show');
    Route::put('/{courseType}', [CourseTypeController::class, 'update'])->name('course-types.update');
    Route::delete('/{courseType}', [CourseTypeController::class, 'destroy'])->name('course-types.destroy');

    // Course type activation
    Route::post('/{courseType}/activate', [CourseTypeController::class, 'activate'])->name('course-types.activate');
    Route::post('/{courseType}/deactivate', [CourseTypeController::class, 'deactivate'])->name('course-types.deactivate');
});
