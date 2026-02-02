<?php

use Illuminate\Support\Facades\Route;
use Modules\LearningModule\Http\Controllers\CourseController;
use Modules\LearningModule\Http\Controllers\LessonController;
use Modules\LearningModule\Http\Controllers\UnitController;
use Modules\OrganizationsModule\Http\Controllers\Api\V1\OrganizationController;
use Modules\OrganizationsModule\Http\Controllers\Api\V1\ProgramController;
use Modules\UserManagementModule\Http\Controllers\Api\V1\UserController;

/**
 | -----------------------------------------------------------------------------------------------
 |Super Admin Dashboard Routes
 | -----------------------------------------------------------------------------------------------
 * Handles users management, organization management, learning programs management,course management, quiz management
 * super admin have full controll, full access over system resources 
 * @auth   Required (JWT)
 * @prefix api/v1/super-admin
 * @access Super Admin Only 
 */

Route::group(['prefix'=>'/super-admin','middleware'=>['auth:api','role:super-admin']],function(){

    /**
     * @name   Super Admin Dashboard
     * @path   GET /api/v1/super-admin/dashboard
     * @desc   Retrieve statistics and reports on courses, organizations, students
     * @controller AdminDashboardController@index
     */
    Route::get('/dashboard',[AdminDashboardController::class,'index']);

    /** 
    | -----------------------------------------------------------------------------------------------
    | Organizations Management
    | -----------------------------------------------------------------------------------------------
     * @name   List organizations 
     * @path   GET /api/v1/super-admin/organizations
     * @desc   Retrieve a paginated list of all organizations across the platform. 
     * @controller OrganizationController@index
     */
    Route::get('/organizations',[OrganizationController::class,'index']);

     /**
     * @name   Organization details 
     * @path   GET /api/v1/super-admin/organizations
     * @desc   Retrieve full details about single organization
     * @controller OrganizationController@show
     */
    Route::get('/organizations/{organization}',[OrganizationController::class,'show']);

    /**
     * @name   Create Organization
     * @path   POST /api/v1/super-admin/organizations
     * @desc   Add a new organization onto the platform.
     * @body   {name: string, slug: string, ...}
     * @controller OrganizationController@store
    */
    Route::post('/organizations',[OrganizationController::class,'store']);

    /**
     * @name   Update Organization
     * @path   PUT /api/v1/super-admin/organizations/{organization}
     * @desc   Modify organization settings or profile data.
     * @controller OrganizationController@update
     */
    Route::put('/organizations/{organization}',[OrganizationController::class,'update']);

    /**
     * @name   Delete Organization
     * @path   DELETE /api/v1/super-admin/organizations/{organization}
     * @desc   Soft Delete an organization and its related data from the platform.
     * @controller OrganizationController@destroy
     */
    Route::delete('/organizations/{organization}',[OrganizationController::class,'destroy']);

    /** 
     * @name   Assign Manager to Organization
     * @path   POST /api/v1/super-admin/organizations/{organization}/assign-manager
     * @desc   Assign a user as manager to an organization.
     * @param  {organization}
     * @controller OrganizationController@assignManager
    */
    Route::post('/organizations/{organization}/assign-manager',[OrganizationController::class,'assignManager']);

    /** 
    |--------------------------------------------------------------------------
    |  Learning Program Management
    |--------------------------------------------------------------------------
    */

    /**
     * @name   List All Programs
     * @path   GET /api/v1/super-admin/programs
     * @desc   Retrieve a paginated list of all learning programs across all organizations.
     * @controller ProgramController@index
     */
    Route::get('/programs',[ProgramController::class,'index']);

    /**
     * @name   View Program Details
     * @path   GET /api/v1/super-admin/programs/{program}
     * @desc   Retrieve details of a single program.
     * @param  {program: slug}
     * @controller ProgramController@show
     */

    Route::get('/programs/{program}',[ProgramController::class,'show']);

    /**
     * @name   Create New Program
     * @path   POST /api/v1/super-admin/programs
     * @desc   Validate and store a new learning program in the system.
     * @body   {name: string, description: text, organization_id: int}
     * @controller ProgramController@store
     */
    Route::post('/programs',[ProgramController::class,'store']);

    /**
     * @name   Update Program
     * @path   PUT /api/v1/super-admin/programs/{program}
     * @desc   Update an existing attributes of a specific program.
     * @param  {program: slug}
     * @body   {name: string, description: text}
     * @controller ProgramController@update
     */
    Route::put('/programs/{program}',[ProgramController::class,'update']);

    /**
     * @name   Delete Program
     * @path   DELETE /api/v1/super-admin/programs/{program}
     * @desc   Soft Deletes a program from the database.
     * @param  {program: slug}
     * @controller ProgramController@destroy
     */
    Route::delete('/programs/{program}',[ProgramController::class,'destroy']);

    /** 
    |--------------------------------------------------------------------------
    |  Course Management
    |--------------------------------------------------------------------------
    */

    /**
    * @name   List All Courses
    * @path   GET /api/v1/super-admin/courses
    * @desc   Retrieve a paginated list of all courses across all organizations
    * @controller CourseController@index
    */
    Route::get('/courses',[CourseController::class,'index']);

    /**
    * @name   View Course Details
    * @path   GET /api/v1/super-admin/courses/{course}
    * @desc   Retrieve detailed information about a course
    * @param   {course: slug}
    * @controller   CourseController@show
    */ 
    Route::get('/courses/{course}',[CourseController::class,'show']);

    /**
    * @name   Create New Course
    * @path   POST /api/v1/super-admin/courses
    * @desc   create a new course. requires association with a Program ID.
    * @body   {title: string, program_id: int, difficulty_level: string}
    * @controller CourseController@store
    */
    Route::post('/courses',[CourseController::class,'store']);

    /**
    * @name   Update Course
    * @path   PUT /api/v1/super-admin/courses/{course}
    * @desc   Update an existing attributes of a specific course
    * @param  {course: slug}
    * @controller  CourseController@update
    */
    Route::put('/courses/{course}',[CourseController::class,'update']);

    /**
     * @name   Delete Course
     * @path   DELETE /api/v1/super-admin/courses/{course}
     * @desc   Soft Deletes a course.
     * @param {course: slug}
     * @controller CourseController@destroy
    */
    Route::delete('/courses/{course}',[CourseController::class,'destroy']);

    /** 
    |--------------------------------------------------------------------------
    | Course Units Management
    |--------------------------------------------------------------------------
    */

    /**
     * @name   List Units by Course
     * @path   GET /api/v1/super-admin/courses/{course}/units
     * @desc   Fetch all educational units associated with a specific course.
     * @param  {course: slug}
     * @controller UnitController@index
    */
    Route::get('/courses/{course}/units',[UnitController::class,'index']);

    /**
     * @name   View Unit Details
     * @path   GET /api/v1/super-admin/courses/{course}/units/{unit}
     * @desc   Retrieve details for a single unit within its course context.
     * @param {course: slug, unit: slug}
     * @controller UnitController@show
    */
    Route::get('/courses/{course}/units/{unit}',[UnitController::class,'show']);

    /**
     * @name   Create Course Unit
     * @path   POST /api/v1/super-admin/courses/{course}/units
     * @desc   Add a new unit to a specific course.
     * @body   {title: string,...}
     * @controller UnitController@store
    */
    Route::post('/courses/{course}/units',[UnitController::class,'store']);

    /**
     * @name   Update Course Unit
     * @path   PUT /api/v1/super-admin/courses/{course}/units/{unit}
     * @desc   Modify the title, content, or sequence of a specific unit.
     * @controller UnitController@update
    */
    Route::put('/courses/{course}/units/{unit}',[UnitController::class,'update']);

    /**
     * @name   Delete Course Unit
     * @path   DELETE /api/v1/super-admin/courses/{course}/units/{unit}
     * @desc   Soft Deletes a course unit.
     * @controller UnitController@destroy
    */
    Route::delete('/courses/{course}/units/{unit}',[UnitController::class,'destroy']);
    
    /** 
    |--------------------------------------------------------------------------
    | Course lessons Management
    |--------------------------------------------------------------------------
    */

    /**
     * @name   List Lessons belongs to a specific Unit
     * @path   GET /api/v1/super-admin/courses/{course}/units/{unit}/lessons
     * @desc   Retrieve all  lessons belonging to a specific course unit.
     * @controller LessonController@index
    */
    Route::get('/courses/{course}/units/{unit}/lessons',[LessonController::class,'index']);

    /**
     * @name   View Lesson Details
     * @path   GET /api/v1/super-admin/courses/{course}/units/{unit}/lessons/{lesson}
     * @desc   Fetch full content for a specific lesson.
     * @controller LessonController@show
    */
    Route::get('/courses/{course}/units/{unit}/lessons/{lesson}',[LessonController::class,'show']);

    /**
     * @name   Create Lesson for a course unit
     * @path   POST /api/v1/super-admin/courses/{course}/units/{unit}/lessons
     * @desc   Create new lesson  belongs to a course unit.
     * @body   {title: string,....}
     * @controller LessonController@store
    */
    Route::post('/courses/{course}/units/{unit}/lessons',[LessonController::class,'store']);

    /**
     * @name   Update Lesson
     * @path   PUT /api/v1/super-admin/courses/{course}/units/{unit}/lessons/{lesson}
     * @desc   Edit lesson content
     * @controller LessonController@update
    */
    Route::put('/courses/{course}/units/{unit}/lessons/{lesson}',[LessonController::class,'update']);

    /**
     * @name   Delete Lesson
     * @path   DELETE /api/v1/super-admin/courses/{course}/units/{unit}/lessons/{lesson}
     * @desc   Soft Deletes a lesson from the unit.
     * @controller LessonController@destroy
    */
    Route::delete('/courses/{course}/units/{unit}/lessons/{lesson}',[LessonController::class,'destroy']);

    /** 
    |--------------------------------------------------------------------------
    | User Management
    |--------------------------------------------------------------------------
    */

    /**
     * @name   List All Users
     * @path   GET /api/v1/super-admin/users
     * @desc   Retrieve a paginated list of all users (Admins, Managers, Students, Instructors, Auditors).
     * @controller UserController@index
    */
    Route::get('/users', [UserController::class, 'index']);

    /**
     * @name   View User Details
     * @path   GET /api/v1/super-admin/users/{user}
     * @desc   Retrieve a specific user's account details and his given role on the platform with role permissions.
     * @param  {user: id}
     * @controller  UserController@show
    */
    Route::get('/users/{user}', [UserController::class, 'show']);

    /**
     * @name   Create User Identity
     * @path   POST /api/v1/super-admin/users
     * @desc   Manually register a new user into the platform.
     * @body   {name: string, email: string, password: string,.....}
     * @controller UserController@store
    */
    Route::post('/users', [UserController::class, 'store']);

    /**
     * @name   Update User Information
     * @path   PUT /api/v1/super-admin/users/{user}
     * @desc   Update account details such as name, email,.....
     * @param  {user: id}
     * @controller UserController@update
    */
    Route::put('/users/{user}', [UserController::class, 'update']);

    /**
     * @name   Delete User Account
     * @path   DELETE /api/v1/super-admin/users/{user}
     * @desc   Soft Deletes a user from the platform. 
     * @controller UserController@destroy
    */
    Route::delete('/users/{user}', [UserController::class, 'destroy']);
    
});