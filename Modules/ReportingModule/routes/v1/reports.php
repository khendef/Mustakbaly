<?php

use Illuminate\Support\Facades\Route;
use Modules\ReportingModule\Http\Controllers\LearnerReportController;
use Modules\ReportingModule\Http\Controllers\CourseReportController;
use Modules\ReportingModule\Http\Controllers\DonorReportController;

/*
|--------------------------------------------------------------------------
| Report Routes (v1)
|--------------------------------------------------------------------------
*/

Route::prefix('reports')->group(function () {
    // Learner Reports
    Route::prefix('learners')->group(function () {
        Route::get('/performance', [LearnerReportController::class, 'performanceReport'])
            ->name('reports.learners.performance');
        
        Route::get('/completion-rates', [LearnerReportController::class, 'completionRates'])
            ->name('reports.learners.completion-rates');
        
        Route::get('/learning-time', [LearnerReportController::class, 'learningTime'])
            ->name('reports.learners.learning-time');
    });

    // Course Reports
    Route::prefix('courses')->group(function () {
        Route::get('/popularity', [CourseReportController::class, 'popularityReport'])
            ->name('reports.courses.popularity');
        
        Route::get('/content-performance', [CourseReportController::class, 'contentPerformance'])
            ->name('reports.courses.content-performance');
    });

    // Donor Reports
    Route::prefix('donors')->group(function () {
        Route::get('/comprehensive', [DonorReportController::class, 'comprehensiveReport'])
            ->name('reports.donors.comprehensive');
    });
});

