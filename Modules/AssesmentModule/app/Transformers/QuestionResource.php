<?php

namespace Modules\AssesmentModule\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'quiz_id' => $this->quiz_id,
            'type' => $this->type,
            'question_text' => $this->getTranslations('question_text'),
            'point' => $this->point,
            'order_index' => $this->order_index,
            'is_required' => $this->is_required,

            'options' => QuestionOptionResource::collection($this->whenLoaded('options')),
            'media' => MediaResource::collection($this->whenLoaded('media')),
        ];
    }
}
