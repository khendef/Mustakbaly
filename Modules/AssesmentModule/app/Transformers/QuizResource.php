<?php

namespace Modules\AssesmentModule\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuizResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        return [
            'id' => $this->id,
            'course_id' => $this->course_id,
            'instructor_id' => $this->instructor_id,
            'quizable_id' => $this->quizable_id,
            'quizable_type' => $this->quizable_type,

            'type' => $this->type,
            'title' => $this->getTranslations('title'),
            'description' => $this->getTranslations('description'),

            'max_score' => $this->max_score,
            'passing_score' => $this->passing_score,
            'status' => $this->status,
            'is_published' => $this->is_published,

            'auto_grade_enabled' => $this->auto_grade_enabled,
            'available_from' => optional($this->available_from)->toISOString(),
            'due_date' => optional($this->due_date)->toISOString(),
            'duration_minutes' => $this->duration_minutes,
            'duration_seconds' => $this->duration_seconds,

            'questions' => QuestionResource::collection($this->whenLoaded('questions')),
            'media' => MediaResource::collection($this->whenLoaded('media')),

            'created_at' => optional($this->created_at)->toISOString(),
            'updated_at' => optional($this->updated_at)->toISOString(),
        ];
    }
}
