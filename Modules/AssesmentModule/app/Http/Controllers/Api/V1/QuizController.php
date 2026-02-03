<?php

namespace Modules\AssesmentModule\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AssesmentModule\Transformers\QuizResource;
use Modules\AssesmentModule\Models\Quiz;
use Modules\AssesmentModule\Services\V1\QuizService;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\AssesmentModule\Http\Requests\QuizRequest\StoreQuizRequest;
use Modules\AssesmentModule\Http\Requests\QuizRequest\UpdateQuizRequest;

/**
 * QuizController handles the CRUD operations for managing quizzes.
 * Provides endpoints to list, create, show, update, delete, and publish/unpublish quizzes.
 *
 * @package Modules\AssesmentModule\Http\Controllers\Api\V1
 */
class QuizController extends Controller
{
    /**
     * QuizController constructor.
     *
     * @param QuizService $quizService
     */
    public function __construct(private QuizService $quizService)
    {
         $this->middleware('permission:list-quiz')->only('index');
        $this->middleware('permission:show-quiz')->only('show');
        $this->middleware('permission:create-quiz')->only('store');
        $this->middleware('permission:update-quiz')->only('update');
        $this->middleware('permission:delete-quiz')->only('destroy');
    }

    /**
     * Display a listing of the quizzes.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
          $filters=$request->only(['instructor_id','course_id','type','status','available_now']);
           $perPage =(int) $request->integer('per_page', 15);
           $res = $this->quizService->index(
            filters:$filters,
            perPage:$perPage
         );

        return $this->respond($res, paginated: true, resource: QuizResource::class);
    }

    /**
     * Store a newly created quiz in the database.
     *
     * @param StoreQuizRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreQuizRequest $request)
    {
        $res = $this->quizService->store($request->validated());

        return $this->respond($res, resource: QuizResource::class);
    }

    /**
     * Display the specified quiz.
     *
     * @param Quiz $quiz
     * @return \Illuminate\Http\Response
     */
    public function show(Quiz $quiz)
    {
        $res = $this->quizService->show($quiz->id);

        return $this->respond($res, resource: QuizResource::class);
    }

    /**
     * Update the specified quiz in the database.
     *
     * @param UpdateQuizRequest $request
     * @param Quiz $quiz
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateQuizRequest $request, Quiz $quiz)
    {
        $res = $this->quizService->update($quiz, $request->validated());

        return $this->respond($res, resource: QuizResource::class);
    }

    /**
     * Remove the specified quiz from the database.
     *
     * @param Quiz $quiz
     * @return \Illuminate\Http\Response
     */
    public function destroy(Quiz $quiz)
    {
        $res = $this->quizService->destroy($quiz);

        return $this->respond($res);
    }

    /**
     * Publish the specified quiz.
     *
     * @param Quiz $quiz
     * @return \Illuminate\Http\Response
     */
    public function publish(Quiz $quiz)
    {
        $res = $this->quizService->publish($quiz);

        return $this->respond($res, resource: QuizResource::class);
    }

    /**
     * Unpublish the specified quiz.
     *
     * @param Quiz $quiz
     * @return \Illuminate\Http\Response
     */
    public function unpublish(Quiz $quiz)
    {
        $res = $this->quizService->unpublish($quiz);

        return $this->respond($res, resource: QuizResource::class);
    }

    /**
     * Format and return the response.
     *
     * @param array $res
     * @param bool $paginated
     * @param string|null $resource
     * @return \Illuminate\Http\Response
     */
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
