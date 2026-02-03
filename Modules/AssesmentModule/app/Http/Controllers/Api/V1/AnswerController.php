<?php

namespace Modules\AssesmentModule\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\AssesmentModule\Http\Requests\AnswerRequest\StoreAnswerRequest;
use Modules\AssesmentModule\Http\Requests\AnswerRequest\UpdateAnswerRequest;
use Modules\AssesmentModule\Models\Answer;
use Modules\AssesmentModule\Services\V1\AnswerService;
use Modules\AssesmentModule\Transformers\AnswerResource;
use Throwable;

/**
 * AnswerController handles CRUD operations for managing answers in the assessment module.
 * Provides endpoints for listing, creating, updating, and deleting answers.
 *
 * @package Modules\AssesmentModule\Http\Controllers\Api\V1
 */
class AnswerController extends Controller
{

    /**
     * AnswerController constructor.
     *
     * @param AnswerService $answerService
     */
    public function __construct(private AnswerService $answerService) {
        $this->middleware('permission:list-answers')->only('index');
        $this->middleware('permission:show-answer')->only('show');
        $this->middleware('permission:create-answer')->only('store');
        $this->middleware('permission:update-answer')->only('update');
        $this->middleware('permission:delete-answer')->only('destroy');
    }

    /**
     * Display a listing of the answers based on filters.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            // Extracting filters for querying answers
            $filters = $request->only([
                'attempt_id',
                'question_id',
                'selected_option_id',
                'answer_text',
                'min_score',
                'max_score',
                'graded_by',
                'graded_at',
            ]);

            if ($request->has('is_correct')) {
                $filters['is_correct'] = $request->query('is_correct');
            }
            if ($request->has('boolean_answer')) {
                $filters['boolean_answer'] = $request->query('boolean_answer');
            }

            // Handling pagination
            $perPage = (int) $request->integer('per_page', 15);

            // Fetching answers
            $res = $this->answerService->index($filters, $perPage);

            // Checking if the operation was successful
            if (!($res['success'] ?? false)) {
                return self::error($res['message'] ?? 'Operation failed', $res['code'] ?? 400, $res);
            }

            $data = $res['data'] ?? null;

            if ($data instanceof LengthAwarePaginator) {
                return self::paginated($data, $res['message'] ?? 'Operation successful', $res['code'] ?? 200);
            }

            return self::success($data, $res['message'] ?? 'Operation successful', $res['code'] ?? 200);

        } catch (Throwable $e) {
            return self::error($e->getMessage(), 500);
        }
    }

    /**
     * Store a newly created answer.
     *
     * @param StoreAnswerRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAnswerRequest $request)
    {
        try {
            // Storing the answer
            $res = $this->answerService->store($request->validated());

            if (!($res['success'] ?? false)) {
                return self::error($res['message'] ?? 'Operation failed', $res['code'] ?? 400, $res);
            }

            return self::success($res['data'] ?? null, $res['message'] ?? 'Operation successful', $res['code'] ?? 201);

        } catch (Throwable $e) {
            return self::error($e->getMessage(), 500);
        }
    }

    /**
     * Display the specified answer.
     *
     * @param Answer $answer
     * @return \Illuminate\Http\Response
     */
    public function show(Answer $answer)
    {
        try {
            // Fetching the specified answer
            $res = $this->answerService->show($answer->id);

            if (!($res['success'] ?? false)) {
                return self::error($res['message'] ?? 'Operation failed', $res['code'] ?? 404, $res);
            }

            return self::success($res['data'] ?? null, $res['message'] ?? 'Operation successful', $res['code'] ?? 200);

        } catch (Throwable $e) {
            return self::error($e->getMessage(), 500);
        }
    }

    /**
     * Update the specified answer.
     *
     * @param UpdateAnswerRequest $request
     * @param Answer $answer
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateAnswerRequest $request, Answer $answer)
    {
        try {
            // Updating the answer
            $res = $this->answerService->update($answer->id, $request->validated());

            if (!($res['success'] ?? false)) {
                return self::error($res['message'] ?? 'Operation failed', $res['code'] ?? 400, $res);
            }

            return self::success($res['data'] ?? null, $res['message'] ?? 'Operation successful', $res['code'] ?? 200);

        } catch (Throwable $e) {
            return self::error($e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified answer from the database.
     *
     * @param Answer $answer
     * @return \Illuminate\Http\Response
     */
    public function destroy(Answer $answer)
    {
        try {
            // Deleting the specified answer
            $res = $this->answerService->destroy($answer->id);

            if (!($res['success'] ?? false)) {
                return self::error($res['message'] ?? 'Operation failed', $res['code'] ?? 400, $res);
            }

            return self::success(null, $res['message'] ?? 'Operation successful', $res['code'] ?? 200);

        } catch (Throwable $e) {
            return self::error($e->getMessage(), 500);
        }
    }
}
