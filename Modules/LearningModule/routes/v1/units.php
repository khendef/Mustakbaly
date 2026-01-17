<?php

use Illuminate\Support\Facades\Route;
use Modules\LearningModule\Http\Controllers\UnitController;
use Modules\LearningModule\Models\Course;

/*
|--------------------------------------------------------------------------
| Unit Routes (v1)
|--------------------------------------------------------------------------
*/

Route::prefix('units')->group(function () {
    // Standard CRUD operations
    Route::get('/', [UnitController::class, 'index'])->name('units.index');
    Route::post('/', [UnitController::class, 'store'])->name('units.store');
    Route::get('/{unit}', [UnitController::class, 'show'])->name('units.show');
    Route::put('/{unit}', [UnitController::class, 'update'])->name('units.update');
    Route::delete('/{unit}', [UnitController::class, 'destroy'])->name('units.destroy');

    // Unit operations
    Route::get('/{unit}/duration', [UnitController::class, 'getDuration'])->name('units.get-duration');
    Route::get('/{unit}/can-delete', [UnitController::class, 'canBeDeleted'])->name('units.can-delete');

    // Units by course
    Route::get('/course/{course}', [UnitController::class, 'byCourse'])->name('units.by-course');
    Route::post('/course/{course}/reorder', [UnitController::class, 'reorder'])->name('units.reorder');
    Route::get('/course/{course}/count', [UnitController::class, 'getUnitCount'])->name('units.count');

    // Unit positioning
    Route::put('/{unit}/position', [UnitController::class, 'moveToPosition'])->name('units.move-to-position');
});
