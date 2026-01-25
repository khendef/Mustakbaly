<?php

namespace Modules\AssesmentModule\Services\v2;
use Illuminate\Support\Facades\DB;
use Modules\AssesmentModule\Models\Question;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Throwable;
class QuestionService extends BaseService
{
    public function handle() {}
    public function index(array $filters = [],int $perPage =15): array
    {
        try {
            $data = Question::query()
            ->filter($filters)
            ->paginate($perPage);
                return $this->ok('Questions fetched successfully.', $data, 200);
        } catch (Throwable $e) {
            return $this->fail('Failed to fetch questions.', $e, 500);
        }
    }

    public function store(array $data): array
    {
        try {
            $question = Question::create($data);
            return $this->ok('Question created successfully.', $question, 201);
        } catch (Throwable $e) {
            return $this->fail('Failed to create question.', $e, 500);
        }
    }

    public function show(int $id): array
    {
        try {
            $question = Question::query()->findOrFail($id);
            return $this->ok('Question fetched successfully.', $question, 200);
        } catch (ModelNotFoundException $e) {
            return $this->fail('Question not found.', $e, 404);
        } catch (Throwable $e) {
            return $this->fail('Failed to fetch question.', $e, 500);
        }
    }

    public function update(int $id, array $data): array
    {
        try {
            $question = Question::query()->findOrFail($id);
            $question->update($data);

            return $this->ok('Question updated successfully.', $question->fresh(), 200);
        } catch (ModelNotFoundException $e) {
            return $this->fail('Question not found.', $e, 404);
        } catch (Throwable $e) {
            return $this->fail('Failed to update question.', $e, 500);
        }
    }

    public function destroy(int $id): array
    {
        try {
            $question = Question::query()->findOrFail($id);
            $question->delete();

            return $this->ok('Question deleted successfully.', null, 200);
        } catch (ModelNotFoundException $e) {
            return $this->fail('Question not found.', $e, 404);
        } catch (Throwable $e) {
            return $this->fail('Failed to delete question.', $e, 500);
        }
    }
}


