<?php

use Illuminate\Support\Facades\Route;
use Modules\LearningModule\Http\Controllers\LearningModuleController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('learningmodules', LearningModuleController::class)->names('learningmodule');
});
