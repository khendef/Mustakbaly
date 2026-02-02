<?php

namespace Modules\AssesmentModule\Http\Controllers\Api\V1;

use Modules\AssesmentModule\Transformers\AttemptResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AssesmentModule\Http\Requests\AttemptRequest\GradeAttemptRequest;
use Modules\AssesmentModule\Http\Requests\AttemptRequest\StartAttemptRequest;
use Modules\AssesmentModule\Http\Requests\AttemptRequest\SubmitAttemptRequest;
use Modules\AssesmentModule\Http\Requests\AttemptRequest\StoreAttemptRequest;
use Modules\AssesmentModule\Http\Requests\AttemptRequest\UpdateAttemptRequest;
use Modules\AssesmentModule\Models\Attempt;
use Modules\AssesmentModule\Services\V1\AttemptService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Throwable;

/**
 * AttemptController handles the CRUD operations for managing attempts.
 * Provides endpoints for listing, creating, updating, starting, submitting, and grading attempts.
 *
 * @package Modules\AssesmentModule\Http\Controllers\Api\V1
 */
class AttemptController extends Controller
{

    /**
     * AttemptController constructor.
     *
     * @param AttemptService $attemptService
     */
    public function __construct(private AttemptService $attemptService) {}

    /**
     * Display a listing of attempts based on filters.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            // Extracting filters and pagination information
            $filters = $request->only([
                'quiz_id', 'student_id', 'status', 'is_passed', 'graded_by', 'attempt_number',
                'start_at', 'ends_at', 'min_score', 'max_score', 'submitted_at', 'graded_at',
                'min_time_spent', 'max_time_spent', 'order'
            ]);

            // Handling pagination
            $perPage = (int) $request->integer('per_page', 15);
            if($request->has('is_passed')){
                $filters['is_passed'] = $request->query('is_passed');
            }

            $res = $this->attemptService->index($filters, $perPage);

            if (!($res['success'] ?? false)) {
                return self::error($res['message'] ?? 'Operation failed', $res['code'] ?? 404, $res);
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
     * Display the specified attempt.
     *
     * @param Attempt $attempt
     * @return \Illuminate\Http\Response
     */
    public function show(Attempt $attempt)
    {
        try {
            // Fetching the specified attempt
            $res = $this->attemptService->show($attempt);

            if (!($res['success'] ?? false)) {
                return self::error($res['message'] ?? 'Operation failed', $res['code'] ?? 404, $res);
            }

            return self::success($res['data'] ?? null, $res['message'] ?? 'Operation successful', $res['code'] ?? 200);

        } catch (Throwable $e) {
            return self::error($e->getMessage(), 500);
        }
    }

    /**
     * Store a newly created attempt.
     *
     * @param StoreAttemptRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAttemptRequest $request)
    {
        try {
            // Storing the attempt
            $res = $this->attemptService->store($request->validated());

            if (!($res['success'] ?? false)) {
                return self::error($res['message'] ?? 'Operation failed', $res['code'] ?? 400, $res);
            }

            return self::success($res['data'] ?? null, $res['message'] ?? 'Attempt created successfully', $res['code'] ?? 201);

        } catch (Throwable $e) {
            return self::error($e->getMessage(), 500);
        }
    }

    /**
     * Update the specified attempt.
     *
     * @param UpdateAttemptRequest $request
     * @param int $attempt
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateAttemptRequest $request, int $attempt)
    {
        try {
            // Updating the attempt
            $res = $this->attemptService->update($attempt, $request->validated());

            if (!($res['success'] ?? false)) {
                return self::error($res['message'] ?? 'Operation failed', $res['code'] ?? 400, $res);
            }

            return self::success($res['data'], $res['message'] ?? 'Attempt updated successfully', $res['code'] ?? 200);

        } catch (Throwable $e) {
            return self::error($e->getMessage(), 500);
        }
    }

    /**
     * Start a new attempt.
     *
     * @param StartAttemptRequest $request
     * @return \Illuminate\Http\Response
     */
    public function start(StartAttemptRequest $request)
    {
        try {
            // Starting the attempt
            $payload = $request->validated();
            $res = $this->attemptService->start($payload);

            if (!($res['success'] ?? false)) {
                return self::error($res['message'] ?? 'Operation failed', $res['code'] ?? 422, $res);
            }

            return self::success($res['data'] ?? null, $res['message'] ?? 'Operation successful', $res['code'] ?? 201);

        } catch (Throwable $e) {
            return self::error($e->getMessage(), 500);
        }
    }

    /**
     * Submit the attempt.
     *
     * @param SubmitAttemptRequest $request
     * @param int $attempt
     * @return \Illuminate\Http\Response
     */
    public function submit(SubmitAttemptRequest $request, int $attempt)
    {
        try {
            // Submitting the attempt
            $res = $this->attemptService->submit($attempt, $request->validated());

            if (!($res['success'] ?? false)) {
                return self::error($res['message'] ?? 'Operation failed', $res['code'] ?? 422, $res);
            }

            return self::success($res['data'] ?? null, $res['message'] ?? 'Attempt submitted successfully', $res['code'] ?? 200);

        } catch (Throwable $e) {
            return self::error($e->getMessage(), 500);
        }
    }

    /**
     * Grade the attempt.
     *
     * @param GradeAttemptRequest $request
     * @param int $attempt
     * @return \Illuminate\Http\Response
     */
    public function grade(GradeAttemptRequest $request, int $attempt)
    {
        try {
            // Grading the attempt
            $graderId = Auth::id();
            $res = $this->attemptService->grade($attempt, $request->validated(), $graderId);

            if (!($res['success'] ?? false)) {
                return self::error($res['message'] ?? 'Operation failed', $res['code'] ?? 422, $res);
            }

            return self::success($res['data'] ?? null, $res['message'] ?? 'Attempt graded successfully', $res['code'] ?? 200);

        } catch (Throwable $e) {
            return self::error($e->getMessage(), 500);
        }
    }
}
