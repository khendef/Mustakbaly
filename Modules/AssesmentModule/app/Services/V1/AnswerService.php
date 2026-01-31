<?php

namespace Modules\AssesmentModule\Services\V1;
use Modules\AssesmentModule\Models\Answer;
use Throwable;
class AnswerService extends BaseService
{
    public function handle() {}
     public function index(array $filters, int $perPage = 15): array
    {
        try {
            $q = Answer::query()->filter($filters)->latest('id');

            $data = $q->paginate($perPage);

            return $this->ok('Operation successful', $data, 200);
        } catch (Throwable $e) {
            return $this->fail('Failed to fetch answers.', $e, 500);
        }
    }

    public function store(array $payload): array
    {
        try {
            $attemptId  = (int)($payload['attempt_id'] ?? 0);
            $questionId = (int)($payload['question_id'] ?? 0);
            $answer = Answer::query()->updateOrCreate(
                [
                    'attempt_id'  => $attemptId,
                    'question_id' => $questionId,
                ],
                $payload
            );

            $code = $answer->wasRecentlyCreated ? 201 : 200;
            $msg  = $answer->wasRecentlyCreated ? 'Answer created successfully' : 'Answer saved successfully';

            return $this->ok($msg, $answer, $code);
        } catch (Throwable $e) {
            return $this->fail('Failed to create answer.', $e, 500);
        }
    }

    public function show(int $id): array
    {
        try {
            $answer = Answer::query()->find($id);

            if (!$answer) {
                return $this->fail('Answer not found.', null, 404);
            }

            return $this->ok('Operation successful', $answer, 200);
        } catch (Throwable $e) {
            return $this->fail('Failed to fetch answer.', $e, 500);
        }
    }

    public function update(int $id, array $payload): array
    {
        try {
            $answer = Answer::query()->find($id);
            if (!$answer) return $this->fail('Answer not found.', null, 404);

            $answer->fill($payload);
            $answer->save();

            return $this->ok('Answer updated successfully', $answer, 200);
        } catch (Throwable $e) {
            return $this->fail('Failed to update answer.', $e, 500);
        }
    }

    public function destroy(int $id): array
    {
        try {
            $answer = Answer::query()->find($id);
            if (!$answer) return $this->fail('Answer not found.', null, 404);

            $answer->delete();

            return $this->ok('Answer deleted successfully', null, 200);
        } catch (Throwable $e) {
            return $this->fail('Failed to delete answer.', $e, 500);
        }
    }
}
