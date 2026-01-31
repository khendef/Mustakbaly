<?php

use Illuminate\Support\Facades\Route;
use Modules\UserManagementModule\Http\Controllers\Api\V1\AuditorController;
use Modules\UserManagementModule\Http\Controllers\Api\V1\InstructorController;
use Modules\UserManagementModule\Http\Controllers\Api\V1\StudentController;
use Modules\UserManagementModule\Http\Controllers\Api\V1\UserController;
use Modules\UserManagementModule\Http\Controllers\UserManagementModuleController;

Route::middleware(['auth:api'])->prefix('v1')->group(function () {
    Route::apiResource('usermanagementmodules', UserManagementModuleController::class)->names('usermanagementmodule');
});

Route::group(['prefix'=>'v1'],function(){

    //super-admin dashboard routes
    Route::group(['prefix'=>'super-admin','middleware'=>['auth:api','role:super-admin']],function(){
        //1. super admin reports route
        Route::get('/dashboard',[AdminDashboardController::class,'index']);
        //2. super-admin organizations management route
        Route::apiresource('/organizations',OrganizationController::class);
        //3. learning programs route
        Route::apiresource('/programs',ProgramController::class);
        //4. courses Routes
        Route::apiresource('/courses',CourseController::class);
        //5. course units routes
        Route::get('/courses/{course}/units',[UnitController::class,'index']);
        Route::get('/courses/{course}/units/{unit}',[UnitController::class,'show']);
        Route::post('/courses/{course}/units',[UnitController::class,'store']);
        Route::put('/courses/{course}/units/{unit}',[UnitController::class,'update']);
        Route::delete('/courses/{course}/units/{unit}',[UnitController::class,'destroy']);
        //6. course lessons routes
        Route::get('/courses/{course}/units/{unit}/lessons',[lessonController::class,'index']);
        Route::get('/courses/{course}/units/{unit}/lessons/{lesson}',[lessonController::class,'show']);
        Route::post('/courses/{course}/units/{unit}/lessons',[lessonController::class,'store']);
        Route::put('/courses/{course}/units/{unit}/lessons/{lesson}',[lessonController::class,'update']);
        Route::delete('/courses/{course}/units/{unit}/lessons/{lesson}',[lessonController::class,'destroy']);
        //7. users management routes
        Route::resource('/users',UserController::class);
       
    });

    // organization managers dashboard routes, organizations are scoped by slug
     Route::group(['prefix'=>'/{organization:slug}/manage','middleware'=>['auth:api','role:manager','can:manage-organization,organization']],function(){
        //1. reports route
        Route::get('/dashboard',[DashboardController::class,'index']);
        //2. organization programs management route
        Route::apiresource('/programs',ProgramController::class);//program scope
        //3. organization courses management route
        Route::apiResource('/courses',CourseController::class);//course scope + course policy(show, update, delete)
        //4. course units routes
        Route::get('/courses/{course}/units',[UnitController::class,'index']);//course policy
        Route::get('/courses/{course}/units/{unit}',[UnitController::class,'show']);
        Route::post('/courses/{course}/units',[UnitController::class,'store']);
        Route::put('/courses/{course}/units/{unit}',[UnitController::class,'update']);
        Route::delete('/courses/{course}/units/{unit}',[UnitController::class,'destroy']);
        //5. course lessons routes
        Route::get('/courses/{course}/units/{unit}/lessons',[lessonController::class,'index']);
        Route::get('/courses/{course}/units/{unit}/lessons/{lesson}',[lessonController::class,'show']);
        Route::post('/courses/{course}/units/{unit}/lessons',[lessonController::class,'store']);
        Route::put('/courses/{course}/units/{unit}/lessons/{lesson}',[lessonController::class,'update']);
        Route::delete('/courses/{course}/units/{unit}/lessons/{lesson}',[lessonController::class,'destroy']);

        //6. organization users management routes
        //6.1. instructors authorized routes
        Route::get('/instructors',[InstructorController::class,'index']);
        Route::post('/instructors',[InstructorController::class,'store']);
        Route::get('/instructors/{instructor}',[InstructorController::class,'show']);
        //6.2. students authorized routes
        Route::get('/students',[StudentController::class,'index']);
        Route::get('/students/{student}',[StudentController::class,'show']);
        //6.3. auditors authorized routes
        Route::get('/auditors',[AuditorController::class,'index']);
        Route::post('/auditors',[AuditorController::class,'store']);
        Route::get('/auditors/{auditor}',[AuditorController::class,'show']);   
     });

    //instructor dashboard routes
    Route::group(['middleware'=>['auth:api','role:instructor']],function(){

        //1. instructor all available courses
        Route::get('/my-courses',[CourseController::class,'index']);//scope
        Route::get('/my-courses/{course}',[CourseController::class,'show']);

        //2. instructor course units routes
        Route::get('/my-courses/{course}/units',[UnitController::class,'index']);//policy
        Route::get('/my-courses/{course}/units/{unit}',[UnitController::class,'show']);
        Route::post('/my-courses/{course}/units',[UnitController::class,'store']);
        Route::put('/my-courses/{course}/units/{unit}',[UnitController::class,'update']);
        Route::delete('/my-courses/{course}/units/{unit}',[UnitController::class,'destroy']);
        //3. instructor course lessons routes
        Route::get('/my-courses/{course}/units/{unit}/lessons',[lessonController::class,'index']);
        Route::get('/my-courses/{course}/units/{unit}/lessons/{lesson}',[lessonController::class,'show']);
        Route::post('/my-courses/{course}/units/{unit}/lessons',[lessonController::class,'store']);
        Route::put('/my-courses/{course}/units/{unit}/lessons/{lesson}',[lessonController::class,'update']);
        Route::delete('/my-courses/{course}/units/{unit}/lessons/{lesson}',[lessonController::class,'destroy']);
    });

    //student dashboard routes
    Route::group(['middleware'=>['auth:api','role:student']],function(){
        //courses
        Route::get('/my-learning',[CourseController::class,'index']);//scope
        Route::get('/my-learning/{course}',[CourseController::class,'show']);
        //units
        Route::get('/my-learning/{course}/units',[UnitController::class,'index']);
        Route::get('/my-learning/{course}/units/{unit}',[UnitController::class,'show']);
        //lessons
        Route::get('/my-learning/{course}/units/{unit}/lessons',[lessonController::class,'index']);
        Route::get('/my-learning/{course}/units/{unit}/lessons/{lesson}',[lessonController::class,'show']);
    });


});