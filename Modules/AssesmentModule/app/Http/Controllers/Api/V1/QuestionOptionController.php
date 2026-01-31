<?php

namespace Modules\AssesmentModule\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\AssesmentModule\Http\Requests\QuestionOptionRequest\StoreQuestionOptionRequest;
use Modules\AssesmentModule\Http\Requests\QuestionOptionRequest\UpdateQuestionOptionRequest;
use Modules\AssesmentModule\Models\QuestionOption;
use Modules\AssesmentModule\Services\v2\QuestionOptionService;
use Modules\AssesmentModule\Transformers\QuestionOptionResource;
use Throwable;

class QuestionOptionController extends Controller
{
    public function __construct(private QuestionOptionService $questionOptionService)
    {

    }

    public function index(Request $request)
    {
        try {
            $filters = $request->only([
                'question_id',
                'is_correct',
            ]);

            $perPage = (int) $request->integer('per_page', 15);

            $res = $this->questionOptionService->index($filters, $perPage);

            if (!($res['success'] ?? false)) {
                return self::error($res['message'] ?? 'Operation failed', $res['code'] ?? 400, $res);
            }

            $data = $res['data'] ?? null;

            if ($data instanceof LengthAwarePaginator) {
                $items = collect($data->items())->map(
                    fn ($opt) => (new QuestionOptionResource($opt))->resolve()
                )->all();

                $data->setCollection(collect($items));

                return self::paginated($data, $res['message'] ?? 'Operation successful', $res['code'] ?? 200);
            }
            if ($data !== null) {
                return self::success(
                    QuestionOptionResource::collection($data),
                    $res['message'] ?? 'Operation successful',
                    $res['code'] ?? 200
                );
            }

            return self::success(null, $res['message'] ?? 'Operation successful', $res['code'] ?? 200);
        } catch (Throwable $e) {
            return self::error($e->getMessage(), 500);
        }
    }

    public function store(StoreQuestionOptionRequest $request)
    {
        try {
            $res = $this->questionOptionService->store($request->validated());

            if (!($res['success'] ?? false)) {
                return self::error($res['message'] ?? 'Operation failed', $res['code'] ?? 400, $res);
            }

            $opt = $res['data'] ?? null;

            return self::success(
                $opt ? new QuestionOptionResource($opt) : null,
                $res['message'] ?? 'Operation successful',
                $res['code'] ?? 201
            );
        } catch (Throwable $e) {
            return self::error($e->getMessage(), 500);
        }
    }

    public function show(QuestionOption $questionOption)
    {
        try {
            $res = $this->questionOptionService->show($questionOption->id);

            if (!($res['success'] ?? false)) {
                return self::error($res['message'] ?? 'Operation failed', $res['code'] ?? 404, $res);
            }

            $opt = $res['data'] ?? $questionOption;

            return self::success(
                $opt ? new QuestionOptionResource($opt) : null,
                $res['message'] ?? 'Operation successful',
                $res['code'] ?? 200
            );
        } catch (Throwable $e) {
            return self::error($e->getMessage(), 500);
        }
    }

    public function update(UpdateQuestionOptionRequest $request, QuestionOption $questionOption)
    {
        try {
            $res = $this->questionOptionService->update($questionOption->id, $request->validated());

            if (!($res['success'] ?? false)) {
                return self::error($res['message'] ?? 'Operation failed', $res['code'] ?? 400, $res);
            }
            $opt = $res['data'] ?? null;

            return self::success(
                $opt ? new QuestionOptionResource($opt) : null,
                $res['message'] ?? 'Operation successful',
                $res['code'] ?? 200
            );
        } catch (Throwable $e) {
            return self::error($e->getMessage(), 500);
        }
    }
    public function destroy(QuestionOption $questionOption)
    {
        try {
            $res = $this->questionOptionService->destroy($questionOption->id);

            if (!($res['success'] ?? false)) {
                return self::error($res['message'] ?? 'Operation failed', $res['code'] ?? 400, $res);
            }

            return self::success(null, $res['message'] ?? 'Operation successful', $res['code'] ?? 200);
        } catch (Throwable $e) {
            return self::error($e->getMessage(), 500);
        }
    }
}
