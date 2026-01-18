<?php

namespace Modules\AssesmentModule\Http\Controllers;
use Modules\AssesmentModule\Transformers\AttemptResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AssesmentModule\Http\Requests\GradeAttemptRequest;
use Modules\AssesmentModule\Http\Requests\StartAttemptRequest;
use Modules\AssesmentModule\Http\Requests\SubmitAttemptRequest;
use Modules\AssesmentModule\Http\Requests\StoreAttemptRequest;
use Modules\AssesmentModule\Http\Requests\UpdateAttemptRequest;
use Modules\AssesmentModule\Models\Attempt;
use Modules\AssesmentModule\Services\AttemptService;
use Illuminate\Pagination\LengthAwarePaginator;
use Throwable;


class AttemptController extends Controller
{
 public function __construct(private AttemptService $attemptService) {}

    public function index(Request $request)
    {
        try {
            $filters = $request->only(['quiz_id', 'student_id', 'status']);
            $perPage = (int) $request->integer('per_page', 15);

            $res = $this->attemptService->index($filters, $perPage);

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

    public function show(Attempt $attempt)
    {
        try {
            $res = $this->attemptService->show($attempt);

            if (!($res['success'] ?? false)) {
                return self::error($res['message'] ?? 'Operation failed', $res['code'] ?? 404, $res);
            }

            return self::success($res['data'] ?? null, $res['message'] ?? 'Operation successful', $res['code'] ?? 200);
        } catch (Throwable $e) {
            return self::error($e->getMessage(), 500);
        }
    }
     public function store(StoreAttemptRequest $request)
    {
        $res = $this->attemptService->store($request->validated());

        if (!($res['success'] ?? false)) {
            return self::error($res['message'] ?? 'Operation failed', $res['code'] ?? 400, $res);
        }

        return self::success($res['data'] ?? null, $res['message'] ?? 'Operation successful', $res['code'] ?? 201);
    }
      public function update(UpdateAttemptRequest $request, Attempt $attempt)
    {
        try {
            $res = $this->attemptService->update($attempt->id, $request->validated());

            if (!($res['success'] ?? false)) {
                return self::error(
                    $res['message'] ?? 'Operation failed',
                    $res['code'] ?? 400,
                    $res
                );
            }

            $a = $res['data'] ?? null;

            return self::success(
                $a ? new AttemptResource($a) : null,
                $res['message'] ?? 'Operation successful',
                $res['code'] ?? 200
            );
        } catch (Throwable $e) {
            return self::error($e->getMessage(), 500);
        }
    }



    public function start(StartAttemptRequest $request)
    {
        try {
            $res = $this->attemptService->start($request->validated());

            if (!($res['success'] ?? false)) {
                return self::error($res['message'] ?? 'Operation failed', $res['code'] ?? 422, $res);
            }

            return self::success($res['data'] ?? null, $res['message'] ?? 'Operation successful', $res['code'] ?? 201);
        } catch (Throwable $e) {
            return self::error($e->getMessage(), 500);
        }
    }

    public function submit(SubmitAttemptRequest $request, Attempt $attempt)
    {
        try {
            $res = $this->attemptService->submit($attempt, $request->validated());

            if (!($res['success'] ?? false)) {
                return self::error($res['message'] ?? 'Operation failed', $res['code'] ?? 422, $res);
            }

            return self::success($res['data'] ?? null, $res['message'] ?? 'Operation successful', $res['code'] ?? 200);
        } catch (Throwable $e) {
            return self::error($e->getMessage(), 500);
        }
    }


    public function grade(GradeAttemptRequest $request, Attempt $attempt)
    {
        try {
            $res = $this->attemptService->grade($attempt, $request->validated());

            if (!($res['success'] ?? false)) {
                return self::error($res['message'] ?? 'Operation failed', $res['code'] ?? 422, $res);
            }

            return self::success($res['data'] ?? null, $res['message'] ?? 'Operation successful', $res['code'] ?? 200);
        } catch (Throwable $e) {
            return self::error($e->getMessage(), 500);
        }
    }
}
