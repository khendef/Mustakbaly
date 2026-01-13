<?php

namespace App\Models\CourseManagement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseType extends Model
{
    /**
     * Represents a course type or category in the e-learning platform.
     * Defines the classification and characteristics of courses, such as certification programs, workshops, or online courses.
     */

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'course_type_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
        'target_audience',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    // Relationships

    /**
     * Get the courses for this course type.
     *
     * @return HasMany
     */
    public function courses(): HasMany
    {
        return $this->hasMany(Course::class, 'course_type_id', 'course_type_id');
    }
}
