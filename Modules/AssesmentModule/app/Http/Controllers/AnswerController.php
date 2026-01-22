<?php

namespace Modules\AssesmentModule\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\AssesmentModule\Models\Answer;
use Modules\AssesmentModule\Services\AnswerService;
use Modules\AssesmentModule\Http\Requests\StoreAnswerRequest;
use Modules\AssesmentModule\Http\Requests\UpdateAnswerRequest;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Throwable;
use Modules\AssesmentModule\Transformers\AnswerResource;

class AnswerController extends Controller
{

    public function __construct(private AnswerService $answerService) {}

    public function index(Request $request)
    {
        try {
            $filters = $request->only(['attempt_id', 'question_id', 'is_correct', 'graded_by']);
            $perPage = (int) $request->integer('per_page', 15);

            $res = $this->answerService->index($filters, $perPage);

            if (!($res['success'] ?? false)) {
                return self::error($res['message'] ?? 'Operation failed', $res['code'] ?? 400, $res);
            }

            $data = $res['data'] ?? null;

            if ($data instanceof LengthAwarePaginator) {
                $data->setCollection(
                    $data->getCollection()->map(fn ($a) => (new AnswerResource($a))->resolve())
                );

                return self::paginated($data, $res['message'] ?? 'Operation successful', $res['code'] ?? 200);
            }

            return self::success(
                $data ? AnswerResource::collection($data) : null,
                $res['message'] ?? 'Operation successful',
                $res['code'] ?? 200
            );
        } catch (Throwable $e) {
            return self::error($e->getMessage(), 500);
        }
    }

    public function store(StoreAnswerRequest $request)
    {
        try {
            $res = $this->answerService->store($request->validated());

            if (!($res['success'] ?? false)) {
                return self::error($res['message'] ?? 'Operation failed', $res['code'] ?? 400, $res);
            }

            $answer = $res['data'] ?? null;

            return self::success(
                $answer ? new AnswerResource($answer) : null,
                $res['message'] ?? 'Operation successful',
                $res['code'] ?? 201
            );
        } catch (Throwable $e) {
            return self::error($e->getMessage(), 500);
        }
    }

    public function show(Answer $answer)
    {
        try {
            $res = $this->answerService->show($answer->id);

            if (!($res['success'] ?? false)) {
                return self::error($res['message'] ?? 'Operation failed', $res['code'] ?? 404, $res);
            }

            $data = $res['data'] ?? null;

            return self::success(
                $data ? new AnswerResource($data) : null,
                $res['message'] ?? 'Operation successful',
                $res['code'] ?? 200
            );
        } catch (Throwable $e) {
            return self::error($e->getMessage(), 500);
        }
    }

    public function update(UpdateAnswerRequest $request, Answer $answer)
    {
        try {
            $res = $this->answerService->update($answer->id, $request->validated());

            if (!($res['success'] ?? false)) {
                return self::error($res['message'] ?? 'Operation failed', $res['code'] ?? 400, $res);
            }

            $data = $res['data'] ?? null;

            return self::success(
                $data ? new AnswerResource($data) : null,
                $res['message'] ?? 'Operation successful',
                $res['code'] ?? 200
            );
        } catch (Throwable $e) {
            return self::error($e->getMessage(), 500);
        }
    }

    public function destroy(Answer $answer)
    {
        try {
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
