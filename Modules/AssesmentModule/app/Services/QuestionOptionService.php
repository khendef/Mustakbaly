<?php

namespace Modules\AssesmentModule\Services;
use Modules\AssesmentModule\Models\QuestionOption;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Throwable;


class QuestionOptionService extends BaseService
{
    public function handle() {}
    /**
     * Create a new class instance.
     */
    public function index(array $filters = [], int $perPage = 15): array
    {
        try {
            $q = QuestionOption::query();

            if (!empty($filters['question_id'])) {
                $q->where('question_id', $filters['question_id']);
            }

            if (array_key_exists('is_correct', $filters) && $filters['is_correct'] !== null && $filters['is_correct'] !== '') {
                $q->where('is_correct', (bool) $filters['is_correct']);
            }

            $data = $q->latest('id')->paginate($perPage);

            return $this->ok('Question options fetched successfully.', $data, 200);
        } catch (Throwable $e) {
            return $this->fail('Failed to fetch question options.', $e, 500);
        }
    }

    public function store(array $data): array
    {
        try {
            $option = QuestionOption::create($data);

            return $this->ok('Question option created successfully.', $option, 201);
        } catch (Throwable $e) {
            return $this->fail('Failed to create question option.', $e, 500);
        }
    }

    public function show(int $id): array
    {
        try {
            $option = QuestionOption::query()->findOrFail($id);

            return $this->ok('Question option fetched successfully.', $option, 200);
        } catch (ModelNotFoundException $e) {
            return $this->fail('Question option not found.', $e, 404);
        } catch (Throwable $e) {
            return $this->fail('Failed to fetch question option.', $e, 500);
        }
    }

    public function update(int $id, array $data): array
    {
        try {
            $option = QuestionOption::query()->findOrFail($id);
            $option->update($data);

            return $this->ok('Question option updated successfully.', $option->fresh(), 200);
        } catch (ModelNotFoundException $e) {
            return $this->fail('Question option not found.', $e, 404);
        } catch (Throwable $e) {
            return $this->fail('Failed to update question option.', $e, 500);
        }
    }

    public function destroy(int $id): array
    {
        try {
            $option = QuestionOption::query()->findOrFail($id);
            $option->delete();

            return $this->ok('Question option deleted successfully.', null, 200);
        } catch (ModelNotFoundException $e) {
            return $this->fail('Question option not found.', $e, 404);
        } catch (Throwable $e) {
            return $this->fail('Failed to delete question option.', $e, 500);
        }
    }
}

