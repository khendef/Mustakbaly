<?php

use Illuminate\Support\Facades\Route;
use Modules\LearningModule\Http\Controllers\CourseController;
use Modules\LearningModule\Http\Controllers\EnrollmentController;
use Modules\UserManagementModule\Http\Controllers\Api\V1\StudentController;


Route::group(['prefix'=>'v1'],function(){


    require __DIR__ . '/V1/instructor.php';
    require __DIR__ . '/V1/student.php';
    require __DIR__ . '/V1/manager.php';
    require __DIR__ . '/V1/superAdmin.php';

    // authenticated user routes
    Route::group(['middleware'=>['auth:api']],function(){
        // course discovery
        Route::get('/courses',[CourseController::class,'index']);
        Route::get('/courses/{course}',[CourseController::class,'show']);

        //course enrollment
        Route::post('{organization}/courses/{course}/enroll',[EnrollmentController::class,'enroll']);
        Route::post('/complete-profile',[StudentController::class,'createProfile']);

    }); 
});