<?php

use Illuminate\Support\Facades\Route;
use Modules\LearningModule\Http\Controllers\CourseController;
use Modules\LearningModule\Http\Controllers\LessonController;
use Modules\LearningModule\Http\Controllers\UnitController;
use Modules\OrganizationsModule\Http\Controllers\Api\V1\ProgramController;
use Modules\UserManagementModule\Http\Controllers\Api\V1\AuditorController;
use Modules\UserManagementModule\Http\Controllers\Api\V1\InstructorController;
use Modules\UserManagementModule\Http\Controllers\Api\V1\StudentController;
  
 /**
 | ----------------------------------------
 | Organization Manager Dashboard Routes
 | ----------------------------------------
 * These routes manage resources specifically for one organization.
 * Security: 
 * 1. JWT Auth (Identity)
 * 2. Manager Role (Global Permission)
 * 3. 'manage-organization' Gate (Verifies that a user with a 'manager' role actually has management rights over the requested organization)
 * 4. OrganizationScope ( Data Isolation between organizations)
 * @prefix    api/v1/{organization}/manage
 * By using {organization:slug} in the route prefix, we ensure every request carries the tenant's identity, 
 * This allows the OrganizationScope to automatically inject WHERE organization_id = ? into every query
 * @auth   Required (JWT)
 * @access organization manager Only
 * @middleware can:manage-organization 
 * @scope   OrganizationScope (filters data based on requeste organization to insure data isolation between organizations)
 */
Route::group(['prefix'=>'/{organization}/manage','middleware'=>['auth:api','role:manager','can:manage-organization,organization','requested_organization']],function(){
  /** 
    |--------------------------------------------------------------------------
    | Dashboard & Reports
    |--------------------------------------------------------------------------
  */
  /**
    * @name   Organization Dashboard
    * @path   GET /api/v1/{org}/manage/dashboard
    * @desc   Retrieve organization reports and metrics (Student counts, course completion rates).
    * @controller DashboardController@index
  */
   Route::get('/dashboard',[DashboardController::class,'index']);
   
   /** 
    |--------------------------------------------------------------------------
    | 2. Learning Programs Management
    |--------------------------------------------------------------------------
   */

    /**
     * @name   List Organization Programs
     * @path   GET /api/v1/{org}/manage/programs
     * @desc   Returns all programs owned by this organization. Filtered by OrganizationScope.
     * @controller ProgramController@index
     */
    Route::get('/programs', [ProgramController::class, 'index']);

    /**
     * @name   Show Program Details
     * @path   GET /api/v1/{org}/manage/programs/{program}
     * @desc   Retrieve full details of a specific program.
     * @controller ProgramController@show
     */
    Route::get('/programs/{program}', [ProgramController::class, 'show']);

    /**
     * @name   Create Organization Program
     * @path   POST /api/v1/{org}/manage/programs
     * @desc   Store a new program
     * @body   {name: string, description: string,....}
     * @controller ProgramController@store
     */
    Route::post('/programs', [ProgramController::class, 'store']);

    /**
     * @name   Update Program
     * @path   PUT /api/v1/{org}/manage/programs/{program}
     * @desc   Modify program attributes.
     * @controller ProgramController@update
     */
    Route::put('/programs/{program}', [ProgramController::class, 'update']);

    /**
     * @name   Delete Program
     * @path   DELETE /api/v1/{org}/manage/programs/{program}
     * @desc   Soft Deletes the program from the organization.
     * @controller ProgramController@destroy
     */
    Route::delete('/programs/{program}', [ProgramController::class, 'destroy']);
   
    /**
    |--------------------------------------------------------------------------
    | 3. Organization Courses Management
    |--------------------------------------------------------------------------
    */
    /**
     * @name   List Organization Courses
     * @path   GET /api/v1/{org}/manage/courses
     * @desc   Retrieve all courses belonging to this organization.
     * @controller CourseController@index
    */
    Route::get('/courses', [CourseController::class, 'index']);

    /**
     * @name   Show Course Details
     * @path   GET /api/v1/{org}/manage/courses/{course}
     * @desc   Retrieve specific course content and settings.
     * @controller CourseController@show
    */
    Route::get('/courses/{course}', [CourseController::class, 'show']);

    /**
     * @name   Create New Course
     * @path   POST /api/v1/{org}/manage/courses
     * @desc   Add a new course to a program within this organization.
     * @controller CourseController@store
     */
    Route::post('/courses', [CourseController::class, 'store']);

    /**
     * @name   Update Course
     * @path   PUT /api/v1/{org}/manage/courses/{course}
     * @desc   Update course attributes
     * @controller CourseController@update
     */
    Route::put('/courses/{course}', [CourseController::class, 'update']);

    /**
     * @name   Delete Course
     * @path   DELETE /api/v1/{org}/manage/courses/{course}
     * @desc   Soft Deletes a course from the organization's program
     * @controller CourseController@destroy
     */
    Route::delete('/courses/{course}', [CourseController::class, 'destroy']);
    
   /** 
    |--------------------------------------------------------------------------
    | Course Content Management
    |--------------------------------------------------------------------------
    */

    /**
     * @name   Units Management
     * @path   /api/v1/{org}/manage/courses/{course}/units
     * @desc   List|Create|Update|Delete units for a specific course in the requested organization.
    */
   Route::get('/courses/{course}/units',[UnitController::class,'index']);
   Route::get('/courses/{course}/units/{unit}',[UnitController::class,'show']);
   Route::post('/courses/{course}/units',[UnitController::class,'store']);
   Route::put('/courses/{course}/units/{unit}',[UnitController::class,'update']);
   Route::delete('/courses/{course}/units/{unit}',[UnitController::class,'destroy']);
  
    /**
     * @name   Units Management
     * @path   /api/v1/{org}/manage/courses/{course}/units{unit}/lessons
     * @desc   List|Create|Update|Delete lessons for a specific course unit in the requested organization.
    */
   Route::get('/courses/{course}/units/{unit}/lessons',[LessonController::class,'index']);
   Route::get('/courses/{course}/units/{unit}/lessons/{lesson}',[LessonController::class,'show']);
   Route::post('/courses/{course}/units/{unit}/lessons',[LessonController::class,'store']);
   Route::put('/courses/{course}/units/{unit}/lessons/{lesson}',[LessonController::class,'update']);
   Route::delete('/courses/{course}/units/{unit}/lessons/{lesson}',[LessonController::class,'destroy']);

    /** 
    |--------------------------------------------------------------------------
    | User Management
    |--------------------------------------------------------------------------
    */

   /**
     * @name   List Organization Instructors
     * @path   GET /api/v1/{org}/manage/instructors
     * @desc   List all users assigned as instructors in this organization.
     * @controller InstructorController@index
     * 
    */
   Route::get('/instructors',[InstructorController::class,'index']);
   /**
     * @name   Assign Instructor
     * @path   POST /api/v1/{org}/manage/instructors
     * @desc   Invite a user to teach within this organization.
     * @controller InstructorController@store
    */
   Route::post('/instructors',[InstructorController::class,'store']);

   /**
     * @name   View Instructor Details
     * @path   GET /api/v1/super-admin/instructors/{instructor}
     * @desc   Retrieve Instructor's account informations and profile details
     * @param  {instructor: id}
     * @controller  InstructorController@show
    */
   Route::get('/instructors/{instructor}',[InstructorController::class,'show']);
   //6.2. students authorized routes
   /**
     * @name   List Enrolled Students
     * @path   GET /api/v1/{org}/manage/students
     * @desc   Retrieve a list of all students enrolled in this organization's courses.
     * @controller StudentController@index
    */
   Route::get('/students',[StudentController::class,'index']);

   /**
     * @name   View Student Details
     * @path   GET /api/v1/super-admin/students/{student}
     * @desc   Retrieve Student's account informations and profile details
     * @param  {student: id}
     * @controller  StudentController@show
    */
   Route::get('/students/{student}',[StudentController::class,'show']);

   /**
     * @name   Register Student in the organization 
     * @path   POST /api/v1/super-admin/users
     * @desc   Manually register a new student into the organization
     * @controller StudentController@store
    */
    Route::post('/students', [StudentController::class, 'store']);

   /**
     * @name   View Auditor List
     * @path   GET /api/v1/{org}/manage/auditors
     * @desc   List all auditors assigned to this organization.
     * @controller AuditorController@index
    */
   Route::get('/auditors',[AuditorController::class,'index']);

   /**
     * @name   Assign Auditor
     * @path   POST /api/v1/{org}/manage/auditors
     * @desc   Invite an auditor to this organization.
     * @controller AuditorController@store
    */
   Route::post('/auditors',[AuditorController::class,'store']);

   /**
     * @name   View Auditor Details
     * @path   GET /api/v1/super-admin/auditors/{auditor}
     * @desc   Retrieve Auditor's account informations and profile details
     * @param  {auditor: id}
     * @controller  AuditorController@show
    */
   Route::get('/auditors/{auditor}',[AuditorController::class,'show']);   
});