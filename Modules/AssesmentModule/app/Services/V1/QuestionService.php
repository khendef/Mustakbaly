<?php

namespace Modules\AssesmentModule\Services\V1;

use Illuminate\Support\Facades\DB;
use Modules\AssesmentModule\Models\Question;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Throwable;

/**
 * Class QuestionService
 *
 * This service handles the business logic for managing questions, including:
 * - Fetching a list of questions with filters and pagination
 * - Creating, updating, and deleting questions
 * - Retrieving a single question by ID
 *
 * It encapsulates all question-related operations, ensuring that the business rules are respected.
 * The service uses exception handling to manage errors and provides consistent responses.
 *
 * @package Modules\AssesmentModule\Services\V1
 */
class QuestionService extends BaseService
{
    /**
     * Handle the service logic. Currently a placeholder for additional functionality.
     *
     * @return void
     */
    public function handle() {}

    /**
     * Fetch a paginated list of questions based on the given filters.
     *
     * @param array $filters The filters to apply to the question query (e.g., question type, status).
     * @param int $perPage The number of questions per page (default is 15).
     *
     * @return array<string, mixed> The result of the operation with status and data.
     */
    public function index(array $filters = [], int $perPage = 15): array
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

    /**
     * Store a new question with the provided data.
     *
     * @param array $data The data to create a new question.
     *
     * @return array<string, mixed> The result of the operation with status and the created question.
     */
    public function store(array $data): array
    {
        try {
            $question = Question::create($data);
            return $this->ok('Question created successfully.', $question, 201);
        } catch (Throwable $e) {
            return $this->fail('Failed to create question.', $e, 500);
        }
    }

    /**
     * Fetch a single question by its ID.
     *
     * @param int $id The ID of the question.
     *
     * @return array<string, mixed> The result of the operation with status and the fetched question.
     */
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

    /**
     * Update an existing question with the provided data.
     *
     * @param int $id The ID of the question to update.
     * @param array $data The data to update the question.
     *
     * @return array<string, mixed> The result of the operation with status and the updated question.
     */
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

    /**
     * Delete a question by its ID.
     *
     * @param int $id The ID of the question to delete.
     *
     * @return array<string, mixed> The result of the operation with status.
     */
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
