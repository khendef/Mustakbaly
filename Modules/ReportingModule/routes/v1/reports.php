<?php

use Illuminate\Support\Facades\Route;
use Modules\ReportingModule\Http\Controllers\AdminDashboardController;
use Modules\ReportingModule\Http\Controllers\ManagerPartnerOrganizationDashboardController;

/*
|--------------------------------------------------------------------------
| Report Routes (v1)
|--------------------------------------------------------------------------
*/

Route::prefix('reports')->group(function () {
    // Learner Reports (using AdminDashboardController)
    Route::prefix('learners')->group(function () {
        Route::get('/performance', [AdminDashboardController::class, 'generatePerformanceReport'])
            ->name('reports.learners.performance');
        
        Route::get('/completion-rates', [AdminDashboardController::class, 'getCompletionRates'])
            ->name('reports.learners.completion-rates');
        
        Route::get('/learning-time', [AdminDashboardController::class, 'getLearningTimeAnalysis'])
            ->name('reports.learners.learning-time');
    });

    // Course Reports (using AdminDashboardController and ManagerPartnerOrganizationDashboardController)
    Route::prefix('courses')->group(function () {
        Route::get('/popularity', [AdminDashboardController::class, 'generateCoursePopularityReport'])
            ->name('reports.courses.popularity');
        
        Route::get('/content-performance/{courseId}', [AdminDashboardController::class, 'getContentPerformance'])
            ->name('reports.courses.content-performance');
        
        Route::get('/learning-gaps', [AdminDashboardController::class, 'identifyLearningGaps'])
            ->name('reports.courses.learning-gaps');
    });

    // Partner Organization Course Reports
    Route::prefix('partner-organization')->group(function () {
        Route::prefix('courses')->group(function () {
            Route::get('/popularity', [ManagerPartnerOrganizationDashboardController::class, 'generateCoursePopularityReport'])
                ->name('reports.partner-organization.courses.popularity');
            
            Route::get('/content-performance/{courseId}', [ManagerPartnerOrganizationDashboardController::class, 'getContentPerformance'])
                ->name('reports.partner-organization.courses.content-performance');
            
            Route::get('/learning-gaps', [ManagerPartnerOrganizationDashboardController::class, 'identifyLearningGaps'])
                ->name('reports.partner-organization.courses.learning-gaps');
        });
    });
});

