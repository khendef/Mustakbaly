<?php

namespace Modules\AssesmentModule\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\AssesmentModule\Http\Requests\AnswerRequest\StoreAnswerRequest;
use Modules\AssesmentModule\Services\V1\AnswerService;
use Throwable;

/**
 * AnswerController handles CRUD operations for managing answers in the assessment module.
 * Provides endpoints for listing, creating, updating, and deleting answers.
 *
 * @package Modules\AssesmentModule\Http\Controllers\Api\V1
 */
class AnswerController extends Controller
{
    private $answerService;

    /**
     * AnswerController constructor.
     *
     * @param AnswerService $answerService
     */
    public function __construct(AnswerService $answerService)
    {
        $this->answerService = $answerService;
    }

    /**
     * Store a newly created answer.
     *
     * @param StoreAnswerRequest $request The validated request data.
     * @return \Illuminate\Http\Response A JSON response indicating the success or failure of the operation.
     *
     * @throws Throwable If an unexpected error occurs during the request.
     */
    public function store(StoreAnswerRequest $request)
    {
        try {
            // Pass validated data to the service for processing
            $data = $request->validated();

            // Call service to store the answer
            $answer = $this->answerService->store($data);

            return self::success($answer, 'Answer created successfully', 201);
        } catch (Throwable $e) {
            return self::error($e->getMessage(), 500);
        }
    }

    /**
     * Display the specified answer.
     *
     * @param int $id The ID of the answer to retrieve.
     * @return \Illuminate\Http\Response A JSON response containing the answer.
     *
     * @throws Throwable If an unexpected error occurs during the request.
     */
    public function show($id)
    {
        try {
            $answer = $this->answerService->show($id);
            return self::success($answer, 'Operation successful', 200);
        } catch (Throwable $e) {
            return self::error($e->getMessage(), 500);
        }
    }

    /**
     * List all answers with pagination.
     *
     * @param Request $request The request containing filtering and pagination parameters.
     * @return \Illuminate\Http\JsonResponse JSON response with paginated data or error.
     */
    public function index(Request $request)
    {
        try {
            $filters = $request->only([
                'quiz_id', 'student_id', 'question_id', 'is_correct', 'graded_by'
            ]);

            $perPage = (int) $request->integer('per_page', 15);
            $answers = $this->answerService->index($filters, $perPage);

            return self::paginated($answers, 'Operation successful', 200);
        } catch (Throwable $e) {
            return self::error($e->getMessage(), 500);
        }
    }

    /**
     * Update the specified answer.
     *
     * @param StoreAnswerRequest $request The validated request data.
     * @param int $id The ID of the answer to update.
     * @return \Illuminate\Http\Response A JSON response indicating the success or failure of the operation.
     *
     * @throws Throwable If an unexpected error occurs during the request.
     */
    public function update(StoreAnswerRequest $request, $id)
    {
        try {
            $data = $request->validated();

            $answer = $this->answerService->update($id, $data);

            return self::success($answer, 'Answer updated successfully', 200);
        } catch (Throwable $e) {
            return self::error($e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified answer from storage.
     *
     * @param int $id The ID of the answer to delete.
     * @return \Illuminate\Http\Response A JSON response indicating the success or failure of the operation.
     *
     * @throws Throwable If an unexpected error occurs during the request.
     */
    public function destroy($id)
    {
        try {
            $this->answerService->destroy($id);

            return self::success(null, 'Answer deleted successfully', 200);
        } catch (Throwable $e) {
            return self::error($e->getMessage(), 500);
        }
    }
}
