<?php

namespace App\Models;


use App\Models\CourseManagement\Course;
use App\Models\CourseManagement\CourseInstructor;
use App\Models\CourseManagement\Enrollment;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    /**
     * Represents a user account in the e-learning platform.
     * Handles authentication, user profiles, and relationships with courses, enrollments, and various role-specific profiles.
     */

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'user_id';

    /*
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

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

    // Relationships

    /**
     * Get the courses created by this user.
     *
     * @return HasMany
     */
    public function createdCourses(): HasMany
    {
        return $this->hasMany(Course::class, 'created_by', 'user_id');
    }

    /**
     * Get the courses where this user is an instructor.
     *
     * @return BelongsToMany
     */
    public function instructorCourses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_instructor', 'instructor_id', 'course_id')
            ->using(CourseInstructor::class);
    }

    /**
     * Get the enrollments for this user (as a learner).
     *
     * @return HasMany
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class, 'learner_id', 'user_id');
    }

    /**
     * Get the courses that this user is enrolled in (as a learner).
     *
     * @return BelongsToMany
     */
    public function enrolledCourses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'enrollments', 'learner_id', 'course_id')
            ->using(Enrollment::class);
    }
}
