<?php

namespace Modules\AssesmentModule\Http\Controllers\Api\v2;
use Modules\AssesmentModule\Transformers\AttemptResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AssesmentModule\Http\Requests\AttemptRequest\GradeAttemptRequest;
use Modules\AssesmentModule\Http\Requests\AttemptRequest\StartAttemptRequest;
use Modules\AssesmentModule\Http\Requests\AttemptRequest\SubmitAttemptRequest;
use Modules\AssesmentModule\Http\Requests\AttemptRequest\StoreAttemptRequest;
use Modules\AssesmentModule\Http\Requests\AttemptRequest\UpdateAttemptRequest;
use Modules\AssesmentModule\Models\Attempt;
use Modules\AssesmentModule\Services\v2\AttemptService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Throwable;

class AttemptController extends Controller
{
 public function __construct(private AttemptService $attemptService) {}

    public function index(Request $request)
    {
        try {
            $filters = $request->only(['quiz_id', 'student_id', 'status','is_passed',
            'graded_by','attempt_number','start_at','ends_at','min_score','max_score','submitted_at',
            'graded_at','min_time_spent','max_time_spent','order']);
            $perPage = (int) $request->integer('per_page', 15);
            if($request->has('is_passed')){
                $filters ['is_passed'] = $request->query('is_passed');
            }
            $perPage = (int) $request->integer('per_page', 15);
            $res = $this->attemptService->index($filters, $perPage);
            if(!($res['success'] ?? false)) {
                return self::error($res['message'] ?? 'Operation failed', $res['code'] ?? 404, $res);
            }
            $data = $res['data'] ?? null;
            if ($data instanceof LengthAwarePaginator) {
            return self::paginated(
                $data,
                $res['message'] ?? 'Operation successful',
                $res['code'] ?? 200
            );
            }return self::success(
                $data,
                $res['message'] ?? 'Operation successful',
                $res['code'] ?? 200
            );
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
     public function store(StoreAttemptRequest $request){
    try{
        $res = $this->attemptService->store($request->validated());

        if (!($res['success'] ?? false)) {
            return self::error($res['message'] ?? 'Operation failed', $res['code'] ?? 400, $res);
        }

        return self::success($res['data'] ?? null, $res['message'] ??'Attempt created successfully', $res['code'] ?? 201);
    }catch(Throwable $e){
        return self::error($e->getMessage(),500);
    }
     }
      public function update(UpdateAttemptRequest $request,int $attempt){
      try {
            $res = $this->attemptService->update($attempt, $request->validated());
            if (!($res['success'] ?? false)) {
                return self::error(
                    $res['message'] ?? 'Operation failed',
                    $res['code'] ?? 400,
                    $res
                );
            }
            return self::success(
                $res['data'], $res['message'] ?? 'Attempt updated successful',
                $res['code'] ?? 200
            );
    }catch(Throwable $e){
  return self::error($e->getMessage(),500);
    }
      }

    public function start(StartAttemptRequest $request){
    try{
          $payload = $request->validated();
          $res = $this->attemptService->start($payload);

            if (!($res['success'] ?? false)) {
                return self::error($res['message'] ?? 'Operation failed', $res['code'] ?? 422, $res);
            }

            return self::success($res['data'] ?? null, $res['message'] ??'Operation successful', $res['code'] ?? 201);
    }     catch(Throwable $e){
          return self::error($e->getMessage(),500);
    }
    }

    public function submit(SubmitAttemptRequest $request,int $attempt){
     try{
            $res = $this->attemptService->submit($attempt, $request->validated());

            if (!($res['success'] ?? false)) {
                return self::error($res['message'] ?? 'Operation failed', $res['code'] ?? 422, $res);
            }

            return self::success($res['data'] ?? null, $res['message'] ?? 'Attempt submitted successful', $res['code'] ?? 200);
    }
          catch(Throwable $e){
          return self::error($e->getMessage(),500);
    }
    }

    public function grade(GradeAttemptRequest $request, int $attempt){
     try{   $graderId = Auth::id();
            $res = $this->attemptService->grade($attempt, $request->validated(),$graderId);

            if (!($res['success'] ?? false)) {
                return self::error($res['message'] ?? 'Operation failed', $res['code'] ?? 422, $res);
            }
            return self::success($res['data'] ?? null, $res['message'] ?? 'Attempt graded successful', $res['code'] ?? 200);
    } catch(Throwable $e){
          return self::error($e->getMessage(),500);
    }
    }
}
