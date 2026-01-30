<?php

namespace Modules\AssesmentModule\Http\Controllers\Api\v2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AssesmentModule\Transformers\QuizResource;
use Modules\AssesmentModule\Models\Quiz;
use Modules\AssesmentModule\Services\v2\QuizService;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\AssesmentModule\Http\Requests\QuizRequest\StoreQuizRequest;
use Modules\AssesmentModule\Http\Requests\QuizRequest\UpdateQuizRequest;
use Throwable;

class QuizController extends Controller
{
    /**
     * Display a listing of the resource.
     */


    public function __construct(private QuizService $quizService)
    {
    }

    public function index(Request $request)
    {
        $res = $this->quizService->index(
            filters: $request->all(),
            perPage: (int)($request->integer('per_page') ?? 15)
        );

        return $this->respond($res, paginated: true, resource: QuizResource::class);
    }

    public function store(StoreQuizRequest $request)
    {
        $res = $this->quizService->store($request->validated());

        return $this->respond($res, resource: QuizResource::class);
    }

    public function show(Quiz $quiz)
    {
        $res = $this->quizService->show($quiz->id);

        return $this->respond($res, resource: QuizResource::class);
    }

    public function update(UpdateQuizRequest $request, Quiz $quiz)
    {
        $res = $this->quizService->update($quiz, $request->validated());

        return $this->respond($res, resource: QuizResource::class);
    }
    public function destroy(Quiz $quiz)
    {
        $res = $this->quizService->destroy($quiz);

        return $this->respond($res);
    }

    public function publish(Quiz $quiz)
    {
        $res = $this->quizService->publish($quiz);

        return $this->respond($res, resource: QuizResource::class);
    }


    public function unpublish(Quiz $quiz)
    {
        $res = $this->quizService->unpublish($quiz);

        return $this->respond($res, resource: QuizResource::class);
    }

    private function respond(array $res, bool $paginated = false, ?string $resource = null)
    {
        $ok      = (bool)($res['success'] ?? false);
        $message = (string)($res['message'] ?? ($ok ? 'Operation successful' : 'Operation failed'));
        $code    = (int)($res['code'] ?? ($ok ? 200 : 400));
        $data    = $res['data'] ?? null;

        if (!$ok) {

            return self::error($message, $code, $data);
        }


        if ($paginated) {
            if ($data instanceof LengthAwarePaginator) {

                if ($resource) {
                    $collection = $resource::collection($data->getCollection());
                    $data->setCollection(collect($collection->resolve()));
                }
                return self::paginated($data, $message, $code);
            }

        }
        if ($resource && $data) {
            if ($data instanceof \Illuminate\Support\Collection) {
                $data = $resource::collection($data);
            } else {
                $data = new $resource($data);
            }
        }

        return self::success($data, $message, $code);
    }
}
