<?php

use Illuminate\Support\Facades\Route;
use Modules\ReportingModule\Http\Controllers\AdminDashboardController;
use Modules\ReportingModule\Http\Controllers\StudentDashboardController;
use Modules\ReportingModule\Http\Controllers\TeacherDashboardController;
use Modules\ReportingModule\Http\Controllers\DonorDashboardController;
use Modules\ReportingModule\Http\Controllers\ManagerPartnerOrganizationDashboardController;

/*
|--------------------------------------------------------------------------
| Dashboard Routes (v1)
|--------------------------------------------------------------------------
*/

Route::prefix('dashboards')->group(function () {
    // Admin Dashboard
    Route::get('/admin-dashboard', [AdminDashboardController::class, 'dashboard'])
        ->name('dashboards.admin');

    // Admin Dashboard - Course Reports
    Route::prefix('admin-dashboard')->group(function () {
        Route::get('/generate-course-popularity-report', [AdminDashboardController::class, 'generateCoursePopularityReport'])
            ->name('dashboards.admin.generate-course-popularity-report');
        
        Route::get('/identify-learning-gaps', [AdminDashboardController::class, 'identifyLearningGaps'])
            ->name('dashboards.admin.identify-learning-gaps');
        
        Route::get('/get-content-performance/{courseId}', [AdminDashboardController::class, 'getContentPerformance'])
            ->name('dashboards.admin.get-content-performance');
        
        // Admin Dashboard - Learner Reports
        Route::get('/generate-learner-performance-report', [AdminDashboardController::class, 'generatePerformanceReport'])
            ->name('dashboards.admin.generate-learner-performance-report');
        
        Route::get('/get-completion-rates', [AdminDashboardController::class, 'getCompletionRates'])
            ->name('dashboards.admin.get-completion-rates');
        
        Route::get('/get-learning-time-analysis', [AdminDashboardController::class, 'getLearningTimeAnalysis'])
            ->name('dashboards.admin.get-learning-time-analysis');
    });

    // Student Dashboard
    Route::get('/student-dashboard/{learnerId}', [StudentDashboardController::class, 'dashboard'])
        ->name('dashboards.student');

    // Teacher Dashboard
    Route::get('/teacher-dashboard/{instructorId}', [TeacherDashboardController::class, 'dashboard'])
        ->name('dashboards.teacher');

    // Donor Dashboard
    Route::get('/donor-dashboard/{donorId}', [DonorDashboardController::class, 'dashboard'])
        ->name('dashboards.donor');

    // Manager/Partner Organization Dashboard
    Route::prefix('manager-partner-organization-dashboard')->group(function () {
        Route::get('/{organizationId}', [ManagerPartnerOrganizationDashboardController::class, 'dashboard'])
            ->name('dashboards.manager-partner-organization');
        
        Route::get('/{organizationId}/generate-course-popularity-report', [ManagerPartnerOrganizationDashboardController::class, 'generateCoursePopularityReport'])
            ->name('dashboards.manager-partner-organization.generate-course-popularity-report');
        
        Route::get('/{organizationId}/identify-learning-gaps', [ManagerPartnerOrganizationDashboardController::class, 'identifyLearningGaps'])
            ->name('dashboards.manager-partner-organization.identify-learning-gaps');
        
        Route::get('/{organizationId}/get-content-performance/{courseId}', [ManagerPartnerOrganizationDashboardController::class, 'getContentPerformance'])
            ->name('dashboards.manager-partner-organization.get-content-performance');
    });
});
