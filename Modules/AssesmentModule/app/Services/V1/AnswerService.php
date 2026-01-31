<?php

namespace Modules\AssesmentModule\Services\V1;

use Modules\AssesmentModule\Models\Answer;
use Throwable;

/**
 * Class AnswerService
 *
 * This service handles the business logic for managing answers, including:
 * - Fetching a list of answers with filters and pagination
 * - Creating, updating, and deleting answers
 * - Retrieving a single answer by ID
 *
 * It encapsulates all operations related to answers, ensuring that business rules are respected.
 * The service uses exception handling to manage errors and provides consistent responses.
 *
 * @package Modules\AssesmentModule\Services\V1
 */
class AnswerService extends BaseService
{
    /**
     * Handle the service logic. Currently a placeholder for additional functionality.
     *
     * @return void
     */
    public function handle() {}

    /**
     * Fetch a paginated list of answers based on the given filters.
     *
     * @param array $filters The filters to apply to the answer query (e.g., attempt_id, question_id).
     * @param int $perPage The number of answers per page (default is 15).
     *
     * @return array<string, mixed> The result of the operation with status and data.
     */
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

    /**
     * Store a new answer or update an existing one with the provided data.
     *
     * @param array $payload The data to create or update the answer.
     *
     * @return array<string, mixed> The result of the operation with status and the answer.
     */
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

    /**
     * Fetch a single answer by its ID.
     *
     * @param int $id The ID of the answer to retrieve.
     *
     * @return array<string, mixed> The result of the operation with status and the fetched answer.
     */
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

    /**
     * Update an existing answer with the provided data.
     *
     * @param int $id The ID of the answer to update.
     * @param array $payload The data to update the answer.
     *
     * @return array<string, mixed> The result of the operation with status and the updated answer.
     */
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

    /**
     * Delete an answer by its ID.
     *
     * @param int $id The ID of the answer to delete.
     *
     * @return array<string, mixed> The result of the operation with status.
     */
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
