<?php

namespace App\Models\CourseManagement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lesson extends Model
{
    /**
     * Represents a lesson within a unit in the e-learning platform.
     * Contains lesson content, metadata, and relationships with content items, supporting various lesson types and soft deletion.
     */
    use SoftDeletes;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'lesson_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'unit_id',
        'lesson_order',
        'title',
        'description',
        'content',
        'lesson_type',
        'is_required',
        'estimated_duration_minutes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'deleted_at' => 'datetime',
        ];
    }

    // Relationships

    /**
     * Get the unit that owns the lesson.
     *
     * @return BelongsTo
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id', 'unit_id');
    }

    /**
     * Get the content items for the lesson.
     *
     * @return HasMany
     */
    public function contentItems(): HasMany
    {
        return $this->hasMany(ContentItem::class, 'lesson_id', 'lesson_id')->orderBy('display_order');
    }
}
