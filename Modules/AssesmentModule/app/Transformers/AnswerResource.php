<?php

namespace Modules\AssesmentModule\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnswerResource extends JsonResource
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
            'attempt_id' => $this->attempt_id,
            'question_id' => $this->question_id,

            'selected_option' => $this->selected_option,
            'answer_text' => $this->answer_text,
            'boolean_answer' => $this->boolean_answer,

            'is_correct' => $this->is_correct,
            'question_score' => $this->question_score,

            'graded_by' => $this->graded_by,
            'graded_at' => optional($this->graded_at)->toISOString(),
        ];
    }
}
