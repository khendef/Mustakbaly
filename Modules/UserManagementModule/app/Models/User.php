<?php

namespace Modules\UserManagementModule\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Modules\LearningModule\Models\Course;
use Modules\LearningModule\Models\CourseInstructor;
use Modules\LearningModule\Models\Enrollment;
use Modules\OrganizationsModule\Models\Organization;
use Modules\UserManagementModule\Models\Builders\UserBuilder;
use Modules\UserManagementModule\Models\Scopes\OrganizationScope;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Traits\HasRoles;
// use Modules\UserManagementModule\Database\Factories\UserFactory;

class User extends Authenticatable implements JWTSubject
{
/** @use HasFactory<\Database\Factories\UserFactory> */
  use HasFactory, Notifiable,  HasRoles, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address',
        'date_of_birth',
        'gender'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $guard_name = 'api';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // protected static function booted()
    // {
    //     static::addGlobalScope(new OrganizationScope);
    // }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
 

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function newEloquentBuilder($query): UserBuilder
    {
        return new UserBuilder($query);
    }

    public function studentProfile()
    {
        return $this->hasOne(Student::class);
    }
    public function instructorProfile()
    {
        return $this->hasOne(instructor::class);
    }
    public function auditorProfile()
    {
        return $this->hasOne(auditor::class);
    }
    public function organizations()
    {
        return $this->belongsToMany(Organization::class, 'organization_user')
                ->withPivot('role')
                ->withTimestamps();
    }

    // LearningModule relations (inverse of Course, CourseInstructor, Enrollment)

    /**
     * Courses created by this user.
     */
    public function createdCourses()
    {
        return $this->hasMany(Course::class, 'created_by', 'id');
    }

    /**
     * Courses where this user is assigned as instructor.
     */
    public function instructedCourses()
    {
        return $this->belongsToMany(Course::class, 'course_instructor', 'instructor_id', 'course_id')
            ->using(CourseInstructor::class)
            ->withPivot(['course_instructor_id', 'is_primary', 'assigned_at', 'assigned_by']);
    }

    /**
     * Courses where this user is enrolled as learner.
     */
    public function enrolledCourses()
    {
        return $this->belongsToMany(Course::class, 'enrollments', 'learner_id', 'course_id')
            ->using(Enrollment::class)
            ->withPivot([
                'enrollment_id',
                'enrollment_type',
                'enrollment_status',
                'enrolled_at',
                'enrolled_by',
                'completed_at',
                'progress_percentage',
                'final_grade',
            ]);
    }

    /**
     * Course-instructor pivot records where this user is the instructor.
     */
    public function courseInstructorAssignments()
    {
        return $this->hasMany(CourseInstructor::class, 'instructor_id', 'id');
    }

    /**
     * Course-instructor pivot records assigned by this user.
     */
    public function assignedCourseInstructors()
    {
        return $this->hasMany(CourseInstructor::class, 'assigned_by', 'id');
    }

    /**
     * Enrollments where this user is the learner.
     */
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'learner_id', 'id');
    }

    /**
     * Enrollments created by this user (e.g. admin enrolling a learner).
     */
    public function enrollmentsEnrolledBy()
    {
        return $this->hasMany(Enrollment::class, 'enrolled_by', 'id');
    }
}
