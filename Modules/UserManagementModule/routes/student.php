<?php

use Illuminate\Support\Facades\Route;

/**
 |----------------------------------------------------
 | Student Dashboard Routes
 | ---------------------------------------------------
 * Routes for learners to access their enrolled content.
 * Security: 
 * 1. JWT Auth
 * 2. Student Role
 * 3. CourseAccessScope: Filters all queries to the 'enrollment' table.
 * @prefix api/v1
 * @auth   Required (JWT)
 * @access Student Only
 * @scope  CourseAccessScope (filters courses by student to insure students can only access their enrolled courses)
 */
Route::group(['prefix'=>'v1','middleware'=>['auth:api','role:student']],function(){
    /**
     * @name   Student's Enrolled Courses
     * @path   GET /api/v1/student/my-learning
     * @desc   List all courses the student has successfully enrolled in.
     * @controller CourseController@index
     */
    Route::get('/my-learning',[CourseController::class,'index']);//scope

    /**
     * @name   View Course Content
     * @path   GET /api/v1/student/my-learning/{course}
     * @desc   View the details of a specific enrolled course.
     * @controller CourseController@show
    */
    Route::get('/my-learning/{course}',[CourseController::class,'show']);
    
    /**
     * @name   List Course Units
     * @path   GET /api/v1/my-learning/{course}/units
     * @desc   Fetch the units for a specific course.
     * @param {course: slug}
     */
    Route::get('/my-learning/{course}/units',[UnitController::class,'index']);

    /**
     * @name   View Unit
     * @path   GET /api/v1/my-learning/{course}/units/{unit}
     * @desc   view unit detailes
     */
    Route::get('/my-learning/{course}/units/{unit}',[UnitController::class,'show']);
    /**
     * @name   List Unit Lessons
     * @path   GET /api/v1/my-learning/{course}/units/{unit}/lessons
     * @desc   List all learning materials in a specific unit within student's requested course
    */
    Route::get('/my-learning/{course}/units/{unit}/lessons',[LessonController::class,'index']);

    /**
     * @name   View Lesson
     * @path   GET /api/v1/student/my-courses/{course}/units/{unit}/lessons/{lesson}
     * @desc   Access the actual lesson for a specific course.
     * @param {course, unit, lesson}
     * @controller LessonController@show
    */
    Route::get('/my-learning/{course}/units/{unit}/lessons/{lesson}',[LessonController::class,'show']);
});