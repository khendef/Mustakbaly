<?php
namespace Modules\AssesmentModule\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttemptResource extends JsonResource
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
            'quiz_id' => $this->quiz_id,
            'student_id' => $this->student_id,
            'attempt_number' => $this->attempt_number,
            'status' => $this->status,

            'start_at' => optional($this->start_at)->toISOString(),
            'ends_at' => optional($this->ends_at)->toISOString(),
            'remaining_seconds' => $this->remaining_seconds,
            'is_time_up' => $this->is_time_up,

            'score' => $this->score,
            'is_passed' => $this->is_passed,

            'submitted_at' => optional($this->submitted_at)->toISOString(),
            'graded_at' => optional($this->graded_at)->toISOString(),
            'graded_by' => $this->graded_by,

            'answers' => AnswerResource::collection($this->whenLoaded('answers')),
            'media' => MediaResource::collection($this->whenLoaded('media')),
        ];
    }
}
