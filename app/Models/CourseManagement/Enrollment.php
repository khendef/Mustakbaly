<?php

namespace App\Models\CourseManagement;

use App\Enums\EnrollmentStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Enrollment extends Model
{
    /**
     * Represents the enrollment relationship between users and courses.
     * Manages student enrollments, progress tracking, completion status, and enrollment lifecycle.
     *
     * enrollment_type: self, assigned
     * enrollment_status: active, completed, dropped, suspended
     */

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'enrollment_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'learner_id',
        'course_id',
        'enrollment_type',
        'enrollment_status',
        'enrolled_at',
        'enrolled_by',
        'completed_at',
        'progress_percentage',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'enrollment_type' => 'string',
            'enrollment_status' => EnrollmentStatus::class,
            'enrolled_at' => 'datetime',
            'completed_at' => 'datetime',
            'progress_percentage' => 'decimal:2',
        ];
    }

    // Relationships

    /**
     * Get the course that the learner is enrolled in.
     *
     * @return BelongsTo
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id', 'course_id');
    }

    /**
     * Get the learner (user) who is enrolled.
     *
     * @return BelongsTo
     */
    public function learner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'learner_id', 'user_id');
    }

    /**
     * Get the user who enrolled this learner (could be admin or self).
     *
     * @return BelongsTo
     */
    public function enrolledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'enrolled_by', 'user_id');
    }
}
