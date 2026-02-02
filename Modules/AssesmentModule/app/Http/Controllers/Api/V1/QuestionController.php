<?php

namespace Modules\AssesmentModule\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\AssesmentModule\Http\Requests\QuestionRequest\StoreQuestionRequest;
use Modules\AssesmentModule\Http\Requests\QuestionRequest\UpdateQuestionRequest;
use Modules\AssesmentModule\Models\Question;
use Modules\AssesmentModule\Services\V1\QuestionService;
use Modules\AssesmentModule\Transformers\QuestionResource;
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

    /**
     * Display a listing of the questions based on filters.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            // Extracting filters and pagination information
            $filters = $request->only(['quiz_id', 'type', 'is_required', 'order_index']);
            $perPage = (int) $request->integer('per_page', 15);

            // Fetching questions from the service
            $res = $this->questionService->index($filters, $perPage);

            // Checking if the operation was successful
            if (!($res['success'] ?? false)) {
                return self::error($res['message'] ?? 'Operation failed', $res['code'] ?? 400, $res);
            }

            $data = $res['data'] ?? null;

            // Handling pagination response
            if ($data instanceof LengthAwarePaginator) {
                $data->setCollection(
                    $data->getCollection()->map(fn ($q) => (new QuestionResource($q))->resolve())
                );
                return self::paginated($data, $res['message'] ?? 'Operation successful', $res['code'] ?? 200);
            }

            // Returning a collection of questions
            return self::success(
                $data ? QuestionResource::collection($data) : [],
                $res['message'] ?? 'Operation successful',
                $res['code'] ?? 200
            );
        } catch (Throwable $e) {
            // Handling errors
            return self::error($e->getMessage(), 500);
        }
    }

    /**
     * Store a newly created question in the database.
     *
     * @param StoreQuestionRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreQuestionRequest $request)
    {
        try {
            // Storing the question
            $res = $this->questionService->store($request->validated());

            // Checking if the operation was successful
            if (!($res['success'] ?? false)) {
                return self::error($res['message'] ?? 'Operation failed', $res['code'] ?? 400, $res);
            }

            $q = $res['data'] ?? null;

            // Returning the created question resource
            return self::success(
                $q ? new QuestionResource($q) : null,
                $res['message'] ?? 'Operation successful',
                $res['code'] ?? 201
            );
        } catch (Throwable $e) {
            // Handling errors
            return self::error($e->getMessage(), 500);
        }
    }

    /**
     * Display the specified question.
     *
     * @param Question $question
     * @return \Illuminate\Http\Response
     */
    public function show(Question $question)
    {
        try {
            // Fetching the specified question
            $res = $this->questionService->show((int) $question->id);

            // Checking if the operation was successful
            if (!($res['success'] ?? false)) {
                return self::error($res['message'] ?? 'Operation failed', $res['code'] ?? 404, $res);
            }

            $q = $res['data'] ?? null;

            // Returning the specified question resource
            return self::success(
                $q ? new QuestionResource($q) : null,
                $res['message'] ?? 'Operation successful',
                $res['code'] ?? 200
            );
        } catch (Throwable $e) {
            // Handling errors
            return self::error($e->getMessage(), 500);
        }
    }

    /**
     * Update the specified question in the database.
     *
     * @param UpdateQuestionRequest $request
     * @param Question $question
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateQuestionRequest $request, Question $question)
    {
        try {
            // Updating the question
            $res = $this->questionService->update((int) $question->id, $request->validated());

            // Checking if the operation was successful
            if (!($res['success'] ?? false)) {
                return self::error($res['message'] ?? 'Operation failed', $res['code'] ?? 400, $res);
            }

            $q = $res['data'] ?? null;

            // Returning the updated question resource
            return self::success(
                $q ? new QuestionResource($q) : null,
                $res['message'] ?? 'Operation successful',
                $res['code'] ?? 200
            );
        } catch (Throwable $e) {
            // Handling errors
            return self::error($e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified question from the database.
     *
     * @param Question $question
     * @return \Illuminate\Http\Response
     */
    public function destroy(Question $question)
    {
        try {
            // Deleting the question
            $res = $this->questionService->destroy((int) $question->id);

            // Checking if the operation was successful
            if (!($res['success'] ?? false)) {
                return self::error($res['message'] ?? 'Operation failed', $res['code'] ?? 400, $res);
            }

            // Returning success response
            return self::success(
                null,
                $res['message'] ?? 'Operation successful',
                $res['code'] ?? 200
            );
        } catch (Throwable $e) {
            // Handling errors
            return self::error($e->getMessage(), 500);
        }
    }
}
