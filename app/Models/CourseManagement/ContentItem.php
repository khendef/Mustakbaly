<?php

namespace App\Models\CourseManagement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContentItem extends Model
{
    /**
     * Represents a content item within a lesson in the e-learning platform.
     * Manages various types of content (videos, documents, etc.) with metadata like file size, duration, and availability options, supporting soft deletion.
     */
    use SoftDeletes;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'content_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'lesson_id',
        'content_type',
        'title',
        'file_size_bytes',
        'duration_seconds',
        'is_downloadable',
        'is_offline_available',
        'display_order',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_downloadable' => 'boolean',
            'is_offline_available' => 'boolean',
            'deleted_at' => 'datetime',
        ];
    }

    // Relationships

    /**
     * Get the lesson that owns the content item.
     *
     * @return BelongsTo
     */
    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class, 'lesson_id', 'lesson_id');
    }
}
