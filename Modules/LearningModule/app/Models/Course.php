<?php

namespace Modules\LearningModule\Models;

use App\Models\User;
use App\Traits\LogsActivity;
use Modules\LearningModule\Builders\CourseBuilder;
use Modules\LearningModule\Models\CourseInstructor;
use Modules\LearningModule\Models\Enrollment;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;

class Course extends Model
{
    /**
     * Represents a course in the e-learning platform.
     * Contains course information, metadata, relationships with instructors, units, and manages course lifecycle including publishing and soft deletion.
     */
    use SoftDeletes, LogsActivity;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'course_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'slug',
        'description',
        'objectives',
        'prerequisites',
        'actual_duration_hours',
        'course_type_id',
        'language',
        'status',
        'min_score_to_pass',
        'is_offline_available',
        'course_delivery_type',
        'difficulty_level',
        'average_rating',
        'total_ratings',
        'created_by',
        'published_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_offline_available' => 'boolean',
            'min_score_to_pass' => 'decimal:2',
            'average_rating' => 'decimal:2',
            'published_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Create a new Eloquent query builder for the model.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @return CourseBuilder
     */
    public function newEloquentBuilder($query): CourseBuilder
    {
        return new CourseBuilder($query);
    }

    // Relationships

    /**
     * Get the course type that owns the course.
     *
     * @return BelongsTo
     */
    public function courseType(): BelongsTo
    {
        return $this->belongsTo(CourseType::class, 'course_type_id', 'course_type_id');
    }

    /**
     * Get the user who created the course.
     *
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    /**
     * Get the instructors for the course.
     *
     * @return BelongsToMany
     */
    public function instructors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'course_instructor', 'course_id', 'instructor_id')
            ->using(CourseInstructor::class);
    }

    /**
     * Get the units for the course.
     *
     * @return HasMany
     */
    public function units(): HasMany
    {
        return $this->hasMany(Unit::class, 'course_id', 'course_id')->orderBy('unit_order');
    }

    /**
     * Get the enrollments for the course.
     *
     * @return HasMany
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class, 'course_id', 'course_id');
    }

    /**
     * Get the enrolled learners (users) for the course.
     *
     * @return BelongsToMany
     */
    public function learners(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'enrollments', 'course_id', 'learner_id')
            ->using(Enrollment::class);
    }

    /**
     * Alias for learners() - Get the enrolled students (users) for the course.
     *
     * @return BelongsToMany
     */
    public function students(): BelongsToMany
    {
        return $this->learners();
    }

    /**
     * Configure activity logging for Course model.
     *
     * @return LogOptions
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'title',
                'description',
                'status',
                'language',
                'difficulty_level',
                'is_offline_available',
                'published_at',
                'min_score_to_pass',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(function (string $eventName) {
                return "Course '{$this->title}' was {$eventName}";
            });
    }
}
