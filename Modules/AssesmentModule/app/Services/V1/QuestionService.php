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
 */
    class QuestionService extends BaseService
    {
    /**
     * Fetch a paginated list of questions based on the given filters.
     *
     * @param array $filters The filters to apply to the question query.
     * @param int $perPage The number of questions per page.
     *
     * @return mixed The result with questions.
     */
    public function index(array $filters = [], int $perPage = 15)
    {
        try {
            // Applying filters and getting paginated data
            $data = Question::query()
                ->filter($filters)
                ->paginate($perPage);
            //return data
            return $data; 
        } catch (Throwable $e) {
            throw new \Exception('Failed to fetch questions: ' . $e->getMessage());
        }
    }

    /**
     * Store a new question with the provided data.
     *
     * @param array $data The data to create a new question.
     *
     * @return mixed The created question data.
     */
    public function store(array $data)
    {
        try {
            $question = Question::create($data);
            return $question; 
        } catch (Throwable $e) {
            throw new \Exception('Failed to create question: ' . $e->getMessage());
        }
    }

    /**
     * Fetch a single question by its ID.
     *
     * @param int $id The ID of the question.
     *
     * @return mixed The fetched question.
     */
    public function show(int $id)
    {
        try {
            $question = Question::query()->findOrFail($id);
            return $question; 
        } catch (ModelNotFoundException $e) {
            throw new \Exception('Question not found: ' . $e->getMessage());
        } catch (Throwable $e) {
            throw new \Exception('Failed to fetch question: ' . $e->getMessage());
        }
    }

    /**
     * Update an existing question with the provided data.
     *
     * @param int $id The ID of the question to update.
     * @param array $data The data to update the question.
     *
     * @return mixed The updated question data.
     */
    public function update(int $id, array $data)
    {
        try {
            $question = Question::query()->findOrFail($id);
            $question->update($data);
            return $question->fresh(); 
        } catch (ModelNotFoundException $e) {
            throw new \Exception('Question not found: ' . $e->getMessage());
        } catch (Throwable $e) {
            throw new \Exception('Failed to update question: ' . $e->getMessage());
        }
    }

    /**
     * Delete a question by its ID.
     *
     * @param int $id The ID of the question to delete.
     *
     * @return bool Whether the delete was successful or not.
     */
    public function destroy(int $id)
    {
        try {
            $question = Question::query()->findOrFail($id);
            $question->delete();
            return true; // Return true if deletion is successful
        } catch (ModelNotFoundException $e) {
            throw new \Exception('Question not found: ' . $e->getMessage());
        } catch (Throwable $e) {
            throw new \Exception('Failed to delete question: ' . $e->getMessage());
        }
    }
}
