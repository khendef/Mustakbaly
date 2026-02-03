<?php

namespace Modules\AssesmentModule\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\AssesmentModule\Events\QuestionCreated;
use Modules\AssesmentModule\Http\Requests\QuestionRequest\StoreQuestionRequest;
use Modules\AssesmentModule\Http\Requests\QuestionRequest\UpdateQuestionRequest;
use Modules\AssesmentModule\Models\Question;
use Modules\AssesmentModule\Services\V1\QuestionService;
use Modules\AssesmentModule\Transformers\QuestionResource;
use Modules\NotificationModule\DTO\QuestionNotificationData;
use Throwable;

/**
 * QuestionController handles CRUD operations for managing questions in the assessment module.
 * Provides endpoints to list, create, show, update, and delete questions.
 *
 * @package Modules\AssesmentModule\Http\Controllers\Api\V1
 */
    class QuestionController extends Controller
{
    /**
     * @var QuestionService
     */
    private $questionService;

    /**
     * QuestionController constructor.
     *
     * @param QuestionService $questionService
     */
    public function __construct(QuestionService $questionService)
    {
        $this->questionService = $questionService;
    }

        public function index(Request $request)
    {
        try {
            // Extract filters and pagination information
            $filters = $request->only(['quiz_id', 'type', 'is_required', 'order_index']);
            $perPage = (int) $request->integer('per_page', 15);

            // Fetching questions from the service
            $data = $this->questionService->index($filters, $perPage);

            // If pagination is needed, return paginated response
            if ($data instanceof LengthAwarePaginator) {
                return self::paginated($data, 'Questions fetched successfully', 200);
            }

            // If no pagination, return collection of questions
            return self::success($data, 'Questions fetched successfully', 200);
        } catch (Throwable $e) {
            return self::error($e->getMessage(), 500);
        }
    }

    public function store(StoreQuestionRequest $request)
    {
        try {
            // Storing the question
            $data = $this->questionService->store($request->validated());

            return self::success($data, 'Question created successfully', 201);
        } catch (Throwable $e) {
            return self::error($e->getMessage(), 500);
        }
    }

    public function show(Question $question)
    {
        try {
            // Fetching the specified question
            $data = $this->questionService->show((int) $question->id);

            return self::success($data, 'Question fetched successfully', 200);
        } catch (Throwable $e) {
            return self::error($e->getMessage(), 500);
        }
    }

    public function update(UpdateQuestionRequest $request, Question $question)
    {
        try {
            // Updating the question
            $data = $this->questionService->update((int) $question->id, $request->validated());

            return self::success($data, 'Question updated successfully', 200);
        } catch (Throwable $e) {
            return self::error($e->getMessage(), 500);
        }
    }

    public function destroy(Question $question)
    {
        try {
            // Deleting the question
            $this->questionService->destroy((int) $question->id);

            return self::success(null, 'Question deleted successfully', 200);
        } catch (Throwable $e) {
            return self::error($e->getMessage(), 500);
        }
    }
}