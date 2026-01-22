<?php

namespace Modules\AssesmentModule\Services;
use Modules\AssesmentModule\Models\Answer;
use Modules\AssesmentModule\Models\Question;
use Modules\AssesmentModule\Models\QuestionOption;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Throwable;
class AnswerService extends BaseService
{
    public function handle() {}
    public function index(array $filters = [], int $perPage = 15): array
    {
        try {
            $q = Answer::query();

            $q->when($filters['attempt_id'] ?? null, fn (Builder $b, $v) => $b->where('attempt_id', $v));
            $q->when($filters['question_id'] ?? null, fn (Builder $b, $v) => $b->where('question_id', $v));
            $q->when(isset($filters['is_correct']), fn (Builder $b) => $b->where('is_correct', (bool)$filters['is_correct']));
            $q->when($filters['graded_by'] ?? null, fn (Builder $b, $v) => $b->where('graded_by', $v));

            $data = $q->latest()->paginate($perPage);

            return $this->ok('Answers fetched successfully.', $data);
        } catch (Throwable $e) {
            return $this->fail('Failed to fetch answers.', $e);
        }
    }

    public function show(int $id): array
    {
        try {
            $answer = Answer::query()->findOrFail($id);
            return $this->ok('Answer fetched successfully.', $answer);
        } catch (Throwable $e) {
            return $this->fail('Failed to fetch answer.', $e, 404);
        }
    }

    public function store(array $data): array
    {
        try {
            $answer = DB::transaction(function () use ($data) {
                $payload = $this->buildAnswerPayloadForCreateOrUpdate($data);

                return Answer::query()->create($payload);
            });

            return $this->ok('Answer created successfully.', $answer, 201);
        } catch (Throwable $e) {
            return $this->fail('Failed to create answer.', $e);
        }
    }

    public function update(int $id, array $data): array
    {
        try {
            $answer = DB::transaction(function () use ($id, $data) {
                $answer = Answer::query()->findOrFail($id);

                $payload = $this->buildAnswerPayloadForCreateOrUpdate([
                    'attempt_id' => $data['attempt_id'] ?? $answer->attempt_id,
                    'question_id' => $data['question_id'] ?? $answer->question_id,
                    'selected_option' => array_key_exists('selected_option', $data) ? $data['selected_option'] : $answer->selected_option,
                    'answer_text' => array_key_exists('answer_text', $data) ? $data['answer_text'] : $answer->answer_text,
                    'boolean_answer' => array_key_exists('boolean_answer', $data) ? $data['boolean_answer'] : $answer->boolean_answer,
                ], $answer);

                $answer->update($payload);

                return $answer->fresh();
            });

            return $this->ok('Answer updated successfully.', $answer);
        } catch (Throwable $e) {
            return $this->fail('Failed to update answer.', $e);
        }
    }

    public function destroy(int $id): array
    {
        try {
            $answer = Answer::query()->findOrFail($id);
            $answer->delete();

            return $this->ok('Answer deleted successfully.', null);
        } catch (Throwable $e) {
            return $this->fail('Failed to delete answer.', $e);
        }
    }

    private function buildAnswerPayloadForCreateOrUpdate(array $data, ?Answer $existing = null): array
    {
        $attemptId = (int)$data['attempt_id'];
        $questionId = (int)$data['question_id'];

        $selectedOptionId = $data['selected_option'] ?? null;

        $answerText = $data['answer_text'] ?? null;
        $booleanAnswer = array_key_exists('boolean_answer', $data) ? $data['boolean_answer'] : null;

        $isCorrect = null;
        $questionScore = null;

        try {
            $question = Question::query()->find($questionId);

            if ($question) {
                if (!is_null($selectedOptionId)) {
                    $opt = QuestionOption::query()->find($selectedOptionId);
                    $isCorrect = $opt ? (bool)$opt->is_correct : null;
                }
                elseif (!is_null($booleanAnswer) && isset($question->correct_boolean)) {
                    $isCorrect = ((bool)$booleanAnswer === (bool)$question->correct_boolean);
                }

                if (!is_null($isCorrect) && isset($question->score)) {
                    $questionScore = $isCorrect ? (int)$question->score : 0;
                }
            }
        } catch (Throwable $ignored) {

        }

        return [
            'attempt_id' => $attemptId,
            'question_id' => $questionId,
            'selected_option' => $selectedOptionId,
            'answer_text' => $answerText,
            'boolean_answer' => $booleanAnswer,

            'is_correct' => $isCorrect,
            'question_score' => $questionScore,

            'graded_by' => $existing?->graded_by,
            'graded_at' => $existing?->graded_at,
        ];
    }
}

