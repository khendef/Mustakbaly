<?php

use Illuminate\Support\Facades\Route;
use Modules\LearningModule\Http\Controllers\LessonController;
use Modules\LearningModule\Models\Unit;

/*
|--------------------------------------------------------------------------
| Lesson Routes (v1)
|--------------------------------------------------------------------------
*/

Route::prefix('lessons')->group(function () {
    // Standard CRUD operations
    Route::get('/', [LessonController::class, 'index'])->name('lessons.index');
    Route::post('/', [LessonController::class, 'store'])->name('lessons.store');
    Route::get('/{lesson}', [LessonController::class, 'show'])->name('lessons.show');
    Route::put('/{lesson}', [LessonController::class, 'update'])->name('lessons.update');
    Route::delete('/{lesson}', [LessonController::class, 'destroy'])->name('lessons.destroy');

    // Lesson operations
    Route::get('/{lesson}/duration', [LessonController::class, 'getDuration'])->name('lessons.get-duration');

    // Lessons by unit
    Route::get('/unit/{unit}', [LessonController::class, 'byUnit'])->name('lessons.by-unit');
    Route::post('/unit/{unit}/reorder', [LessonController::class, 'reorder'])->name('lessons.reorder');
    Route::get('/unit/{unit}/count', [LessonController::class, 'getLessonCount'])->name('lessons.count');

    // Lesson positioning
    Route::put('/{lesson}/position', [LessonController::class, 'moveToPosition'])->name('lessons.move-to-position');
});
