<?php

use Illuminate\Support\Facades\Route;
use Modules\LearningModule\Http\Controllers\CourseController;

/*
|--------------------------------------------------------------------------
| Course Routes (v1)
|--------------------------------------------------------------------------
*/

Route::prefix('courses')->group(function () {
    // Standard CRUD operations
    Route::get('/', [CourseController::class, 'index'])->name('courses.index');
    Route::post('/', [CourseController::class, 'store'])->name('courses.store');
    Route::get('/{course}', [CourseController::class, 'show'])->name('courses.show');
    Route::put('/{course}', [CourseController::class, 'update'])->name('courses.update');
    Route::delete('/{course}', [CourseController::class, 'destroy'])->name('courses.destroy');

    // Course status and publishing
    Route::post('/{course}/publish', [CourseController::class, 'publish'])->name('courses.publish');
    Route::post('/{course}/unpublish', [CourseController::class, 'unpublish'])->name('courses.unpublish');
    Route::put('/{course}/status', [CourseController::class, 'changeStatus'])->name('courses.change-status');
    Route::get('/{course}/publishability', [CourseController::class, 'checkPublishability'])->name('courses.check-publishability');
    Route::get('/{course}/duration', [CourseController::class, 'getDuration'])->name('courses.get-duration');

    // Course enrollment
    Route::get('/enrollable/list', [CourseController::class, 'enrollable'])->name('courses.enrollable');

    // Course instructors
    Route::post('/{course}/instructors/assign', [CourseController::class, 'assignInstructor'])->name('courses.assign-instructor');
    Route::delete('/{course}/instructors/remove', [CourseController::class, 'removeInstructor'])->name('courses.remove-instructor');
    Route::put('/{course}/instructors/primary', [CourseController::class, 'setPrimaryInstructor'])->name('courses.set-primary-instructor');
    Route::delete('/{course}/instructors/primary', [CourseController::class, 'unsetPrimaryInstructor'])->name('courses.unset-primary-instructor');
    Route::get('/{course}/instructors', [CourseController::class, 'getInstructors'])->name('courses.get-instructors');

    // Course by instructor
    Route::get('/instructor/{instructorId}', [CourseController::class, 'byInstructor'])->name('courses.by-instructor');
});
