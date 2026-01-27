<?php

use Illuminate\Support\Facades\Route;


/**
 |----------------------------------------------------
 | Instructor Dashboard Routes
 | ---------------------------------------------------
 * Routes for instructors to manage their assigned courses
 * Security: 
 * 1. JWT Auth (Identity)
 * 2. Instructor Role (Global/Org Role)
 * 3. CourseAccessScope: Filters queries to only show courses assigned to this user.
 * @prefix    api/v1
 * @auth   Required (JWT)
 * @access Instructor Only
 * @scope  CourseAccessScope (filters courses by instructor to insure instructors can only access their assigned courses)
 */
Route::group(['prefix'=>'v1','middleware'=>['auth:api','role:instructor']],function(){

    /** 
    |--------------------------------------------------------------------------
    | 1. Assigned Course Overview
    |--------------------------------------------------------------------------
    */

    /**
     * @name   List Instructor Courses
     * @path   GET /api/v1/my-courses
     * @desc   Retrieve all courses where the authenticated user is the assigned instructor.
     * @controller CourseController@index
     */
    Route::get('/my-courses',[CourseController::class,'index']);

    /**
     * @name   View Course details
     * @path   GET /api/v1/my-courses/{course}
     * @desc   Retrieve full details for a specific assigned course.
     * @controller CourseController@show
    */
    Route::get('/my-courses/{course}',[CourseController::class,'show']);

    /** 
    |--------------------------------------------------------------------------
    | 2. Unit Management
    |--------------------------------------------------------------------------
    */

    /**
     * @name   List Course Units
     * @path   GET /api/v1/my-courses/{course}/units
     * @desc   Fetch the units for a specific course.
     * @param {course: slug}
     */
    Route::get('/my-courses/{course}/units',[UnitController::class,'index']);//policy?

    /**
     * @name   View Unit
     * @path   GET /api/v1/my-courses/{course}/units/{unit}
     * @desc   view details of a specific unit.
     */
    Route::get('/my-courses/{course}/units/{unit}',[UnitController::class,'show']);

    /**
     * @name   Add Course Unit
     * @path   POST /api/v1/my-courses/{course}/units
     * @desc   Create a new unit within an instructor's course.
     */
    Route::post('/my-courses/{course}/units',[UnitController::class,'store']);

    /**
     * @name   Update Course Unit
     * @path   PUT /api/v1/my-courses/{course}/units/{unit}
     * @desc   Modify unit attributes by course instructor.
     */
    Route::put('/my-courses/{course}/units/{unit}',[UnitController::class,'update']);//policy

    /**
     * @name   Delete Course Unit
     * @path   DELETE /api/v1/my-courses/{course}/units/{unit}
     * @desc   Soft Deletes a module and its associated lessons.
    */
    Route::delete('/my-courses/{course}/units/{unit}',[UnitController::class,'destroy']);

    /**
    |--------------------------------------------------------------------------
    | 3. Lesson Management 
    |--------------------------------------------------------------------------
    */

    /**
     * @name   List Unit Lessons
     * @path   GET /api/v1/my-courses/{course}/units/{unit}/lessons
     * @desc   List all learning materials in a specific unit within instructor's requested course
    */
    Route::get('/my-courses/{course}/units/{unit}/lessons',[lessonController::class,'index']);

    /**
     * @name   View Lesson
     * @path   GET /api/v1/my-courses/{course}/units/{unit}/lessons/{lesson}
     * @desc   Retrieve lesson content for review or editing.
     */
    Route::get('/my-courses/{course}/units/{unit}/lessons/{lesson}',[lessonController::class,'show']);

    /**
     * @name   Create Lesson
     * @path   POST /api/v1/my-courses/{course}/units/{unit}/lessons
     * @desc   Add a new lesson
    */
    Route::post('/my-courses/{course}/units/{unit}/lessons',[lessonController::class,'store']);

    /**
     * @name   Update Lesson
     * @path   PUT /api/v1/my-courses/{course}/units/{unit}/lessons/{lesson}
     * @desc   Modify lesson content
     */
    Route::put('/my-courses/{course}/units/{unit}/lessons/{lesson}',[lessonController::class,'update']);

    /**
     * @name   Delete Lesson
     * @path   DELETE /api/v1/my-courses/{course}/units/{unit}/lessons/{lesson}
     * @desc   Soft Deletes lesson content.
     */
    Route::delete('/my-courses/{course}/units/{unit}/lessons/{lesson}',[lessonController::class,'destroy']);

});