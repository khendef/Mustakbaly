<?php

use Illuminate\Support\Facades\Route;
use Modules\NotificationModule\Http\Controllers\Api\V1\NotificationController;
use Modules\NotificationModule\Http\Controllers\NotificationModuleController;

Route::prefix('v1')->group(function () {
    Route::post('/send-notification',[NotificationController::class,'sendNotification']);
    Route::post('/send-notification-question',[NotificationController::class,'sendQuestionNotification']);
    Route::post('/send-notification-attempt',[NotificationController::class,'sendAssignmentNotification']);
    Route::get('notifications/{userId}',[NotificationController::class,'getNotificationByUserId']);
});


