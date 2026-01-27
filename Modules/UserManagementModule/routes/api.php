<?php

use Illuminate\Support\Facades\Route;
use Modules\UserManagementModule\Http\Controllers\Api\V1\StudentController;
use Modules\UserManagementModule\Http\Controllers\UserManagementModuleController;

include "./superAdmin.php";
include "./manager.php";
include "./student.php";
include "./instructor.php";

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('usermanagementmodules', UserManagementModuleController::class)->names('usermanagementmodule');
});
Route::group(['prefix'=>'v1'],function(){

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