<?php

namespace Modules\AssesmentModule\Http\Controllers\Api\V1;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\AssesmentModule\Http\Requests\AnswerRequest\StoreAnswerRequest;
use Modules\AssesmentModule\Http\Requests\AnswerRequest\UpdateAnswerRequest;
use Modules\AssesmentModule\Models\Answer;
use Modules\AssesmentModule\Services\v2\AnswerService;
use Modules\AssesmentModule\Transformers\AnswerResource;
use Throwable;

class AnswerController extends Controller
{

    public function __construct(private AnswerService $answerService) {}

    public function index(Request $request)
    {
        try {
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

            $perPage = (int) $request->integer('per_page', 15);

            $res = $this->answerService->index($filters, $perPage);

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

    public function store(StoreAnswerRequest $request)
    {
        try {
            $res = $this->answerService->store($request->validated());

            if (!($res['success'] ?? false)) {
                return self::error($res['message'] ?? 'Operation failed', $res['code'] ?? 400, $res);
            }

            return self::success($res['data'] ?? null, $res['message'] ?? 'Operation successful', $res['code'] ?? 201);
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

            return self::success($res['data'] ?? null, $res['message'] ?? 'Operation successful', $res['code'] ?? 200);
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

            return self::success($res['data'] ?? null, $res['message'] ?? 'Operation successful', $res['code'] ?? 200);
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
