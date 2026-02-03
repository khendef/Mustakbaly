<?php

namespace Modules\AssesmentModule\Services\V1;

use Modules\AssesmentModule\Models\Answer;
use Throwable;

/**
 * AnswerService handles the business logic for managing answers, including:
 * - Storing new answers.
 * - Retrieving a specific answer by ID.
 * - Updating an existing answer.
 * - Deleting an answer.
 *
 * @package Modules\AssesmentModule\Services\V1
 */
class AnswerService extends BaseService
{
    /**
     * Fetch a paginated list of answers based on the given filters.
     *
     * @param array $filters The filters to apply to the answer query (e.g., student_id, quiz_id).
     * @param int $perPage The number of answers per page (default is 15).
     * @return mixed The paginated list of answers.
     *
     * @throws \Exception If an error occurs while fetching the answers.
     */
    public function index(array $filters = [], int $perPage = 15)
    {
        try {
            return Answer::query()->filter($filters)->paginate($perPage);
        } catch (Throwable $e) {
            throw new \Exception('Failed to fetch answers: ' . $e->getMessage());
        }
    }

    /**
     * Store a new answer with the provided data.
     *
     * @param array $data The data to create the answer.
     * @return \Modules\AssesmentModule\Models\Answer The created answer.
     *
     * @throws Throwable If an error occurs while saving the answer.
     */
    public function store(array $data)
    {
        try {
            $attemptId = isset($data['attempt_id']) ? (int) $data['attempt_id'] : null;
            $questionId = (int)($data['question_id'] ?? 0);

            // Create the new answer in the database
            $answer = Answer::create([
                'attempt_id' => $attemptId,
                'question_id' => $questionId,
                'selected_option_id' => $data['selected_option_id'] ?? null,
                'answer_text' => $data['answer_text'] ?? null,
                'boolean_answer' => $data['boolean_answer'] ?? null,
                'is_correct' => $data['is_correct'] ?? null,
                'question_score' => $data['question_score'] ?? null,
                'graded_at' => $data['graded_at'] ?? null,
                'graded_by' => $data['graded_by'] ?? null,
            ]);

            return $answer;
        } catch (Throwable $e) {
            throw new \Exception('Failed to create answer: ' . $e->getMessage());
        }
    }

    /**
     * Retrieve a specific answer by its ID.
     *
     * @param int $id The ID of the answer to retrieve.
     * @return \Modules\AssesmentModule\Models\Answer The retrieved answer.
     *
     * @throws Throwable If an error occurs while retrieving the answer.
     */
    public function show($id)
    {
        try {
            $answer = Answer::find($id);

            if (!$answer) {
                throw new \Exception('Answer not found');
            }

            return $answer;
        } catch (Throwable $e) {
            throw new \Exception('Failed to retrieve answer: ' . $e->getMessage());
        }
    }

    /**
     * Update an existing answer with the provided data.
     *
     * @param int $id The ID of the answer to update.
     * @param array $data The data to update the answer.
     * @return \Modules\AssesmentModule\Models\Answer The updated answer.
     *
     * @throws Throwable If an error occurs while updating the answer.
     */
    public function update($id, array $data)
    {
        try {
            $answer = Answer::findOrFail($id);

            // Update the answer with new data
            $answer->update($data);

            return $answer;
        } catch (Throwable $e) {
            throw new \Exception('Failed to update answer: ' . $e->getMessage());
        }
    }

    /**
     * Delete an answer by its ID.
     *
     * @param int $id The ID of the answer to delete.
     * @return void
     *
     * @throws Throwable If an error occurs while deleting the answer.
     */
    public function destroy($id)
    {
        try {
            $answer = Answer::findOrFail($id);
            $answer->delete();
        } catch (Throwable $e) {
            throw new \Exception('Failed to delete answer: ' . $e->getMessage());
        }
    }
}
