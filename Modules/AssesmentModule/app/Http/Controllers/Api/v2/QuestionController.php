<?php

namespace Modules\AssesmentModule\Http\Controllers\Api\v2;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\AssesmentModule\Http\Requests\QuestionRequest\StoreQuestionRequest;
use Modules\AssesmentModule\Http\Requests\QuestionRequest\UpdateQuestionRequest;
use Modules\AssesmentModule\Models\Question;
use Modules\AssesmentModule\Services\v2\QuestionService;
use Modules\AssesmentModule\Transformers\QuestionResource;
use Throwable;

class QuestionController extends Controller
{
     private $questionService;

    public function __construct(QuestionService $questionService)
    {
        $this->questionService = $questionService;
    }

    public function index(Request $request)
    {
        try {
            $filters = $request->only(['quiz_id','type','is_required','order_index']);
            $perPage = (int) $request->integer('per_page', 15);

            $res = $this->questionService->index($filters, $perPage);

            if (!($res['success'] ?? false)) {
                return self::error($res['message'] ?? 'Operation failed', $res['code'] ?? 400, $res);
            }

            $data = $res['data'] ?? null;

            if ($data instanceof LengthAwarePaginator) {
                $data->setCollection(
                    $data->getCollection()->map(fn ($q) => (new QuestionResource($q))->resolve())
                );
                return self::paginated($data, $res['message'] ?? 'Operation successful', $res['code'] ?? 200);
            }

            return self::success(
                $data ? QuestionResource::collection($data) : [],
                $res['message'] ?? 'Operation successful',
                $res['code'] ?? 200
            );
        } catch (Throwable $e) {
            return self::error($e->getMessage(), 500);
        }
    }

    public function store(StoreQuestionRequest $request)
    {
        try {
            $res = $this->questionService->create($request->validated());

            if (!($res['success'] ?? false)) {
                return self::error($res['message'] ?? 'Operation failed', $res['code'] ?? 400, $res);
            }

            $q = $res['data'] ?? null;

            return self::success(
                $q ? new QuestionResource($q) : null,
                $res['message'] ?? 'Operation successful',
                $res['code'] ?? 201
            );
        } catch (Throwable $e) {
            return self::error($e->getMessage(), 500);
        }
    }

    public function show(Question $question)
    {
        try {
            $res = $this->questionService->show((int) $question->id);

            if (!($res['success'] ?? false)) {
                return self::error($res['message'] ?? 'Operation failed', $res['code'] ?? 404, $res);
            }

            $q = $res['data'] ?? null;

            return self::success(
                $q ? new QuestionResource($q) : null,
                $res['message'] ?? 'Operation successful',
                $res['code'] ?? 200
            );
        } catch (Throwable $e) {
            return self::error($e->getMessage(), 500);
        }
    }

    public function update(UpdateQuestionRequest $request, Question $question)
    {
        try {
            $res = $this->questionService->update((int) $question->id, $request->validated());

            if (!($res['success'] ?? false)) {
                return self::error($res['message'] ?? 'Operation failed', $res['code'] ?? 400, $res);
            }

            $q = $res['data'] ?? null;

            return self::success(
                $q ? new QuestionResource($q) : null,
                $res['message'] ?? 'Operation successful',
                $res['code'] ?? 200
            );
        } catch (Throwable $e) {
            return self::error($e->getMessage(), 500);
        }
    }

    public function destroy(Question $question)
    {
        try {
            $res = $this->questionService->destroy((int) $question->id);

            if (!($res['success'] ?? false)) {
                return self::error($res['message'] ?? 'Operation failed', $res['code'] ?? 400, $res);
            }

            return self::success(
                null,
                $res['message'] ?? 'Operation successful',
                $res['code'] ?? 200
            );
        } catch (Throwable $e) {
            return self::error($e->getMessage(), 500);
        }
    }
}
