<?php

namespace Modules\AssesmentModule\Services\v2;
use Modules\AssesmentModule\Models\Attempt;
use Modules\AssesmentModule\Models\Quiz;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;
class AttemptService extends BaseService
{
    public function handle() {}

    public function index(array $filters = [], int $perPage = 15): array
    {
        try {
           $data = Attempt::query()
            ->filter($filters)
            ->paginate($perPage);
                return $this->ok('Attempts fetched successfully.', $data, 200);
        } catch (Throwable $e) {
            return $this->fail('Failed to fetch attempts.', $e, 500);
        }
    }
    public function store(array $data): array
    {
        try {
           $attemptNumber = Attempt::query()
           ->where('quiz_id', (int)$data['quiz_id'])
           ->where('student_id', (int)$data['student_id'])
           ->max('attempt_number');

                $attempt = Attempt::create([
                    'quiz_id'             => (int) $data['quiz_id'],
                    'student_id'          => (int) $data['student_id'],
                    'attempt_number'      => (int)($attemptNumber ?? 0) + 1,
                    'status'              => 'in_progress',
                    'score'              => (int)($data['score'] ?? 0),
                    'is_passed'           => (bool)($data['is_passed'] ?? false),
                    'start_at'            => $data['start_at'] ?? now(),
                    'ends_at'             => $data['ends_at'] ?? null,
                    'submitted_at'        => null,
                    'graded_at'           => null,
                    'graded_by'           => null,
                    'time_spent_seconds'  => null,
                ]);

                return $this->ok('Attempt created successfully.', $attempt, 201);
        } catch (Throwable $e) {
            return $this->fail('Failed to create attempt.', $e, 500);
        }
    }
     public function update(int $id, array $data): array
    {
        try {
            return DB::transaction(function () use ($id, $data) {
                $attempt = Attempt::query()->find($id);

                if (!$attempt) {
                    return $this->fail('Attempt not found.', null, 404);
                }
                $data = array_intersect_key($data, array_flip($attempt->getFillable()));

                if (empty($data)) {
                    return $this->ok('Nothing to update.', $attempt);
                }

                $attempt->fill($data);
                $attempt->save();

                return $this->ok('Attempt updated successfully.', $attempt);
            });
        } catch (Throwable $e) {
            return $this->fail('Failed to update attempt.', $e);
        }
    }

    public function show(Attempt $attempt): array
    {
        try {
            $attempt->load(['quiz']);
            return $this->ok('Attempt fetched successfully.', $attempt, 200);
        } catch (Throwable $e) {
            return $this->fail('Failed to fetch attempt.', $e, 500);
        }
    }

    public function submit(Attempt $attempt): array
    {
        try {
            DB::transaction(function () use ($attempt) {
                $attempt->update([
                    'status'       => 'submitted',
                    'submitted_at' => now(),
                ]);
            });

            return $this->ok(
                'Attempt submitted successfully.',
                $attempt->fresh()
            );
        } catch (Throwable $e) {
            return $this->fail(
                'Failed to submit attempt.',
                $e
            );
        }
    }

      public function start(array $data):array
    {
       try {
            $quizId = (int) ($data['quiz_id'] ?? 0);
            if ($quizId <= 0) {
                return $this->ok('quiz_id is required.', null, 422);
            }
            $studentId = (int) ($data['student_id'] ?? Auth::id());

            $quiz = Quiz::query()->findOrFail($quizId);
           if(($quiz->status ?? null) !== 'published'){
            return $this->ok('Quiz is not published.', null, 422);
           }
           $durationMinutes = (int)($quiz->duration_minutes ?? 0);
              if($durationMinutes <= 0){
                return $this->ok('Quiz duration is invalid.', null, 422);
              }
              $startAt = now();
              $endsAt = $startAt->clone()->addMinutes($durationMinutes);

            $attempt = DB::transaction(function () use ($quizId, $studentId, $startAt, $endsAt) {
                $lastNumber = Attempt::query()
                    ->where('quiz_id', $quizId)
                    ->where('student_id', $studentId)
                    ->lockForUpdate()
                    ->max('attempt_number');

            $nextNumber = ((int) $lastNumber) +1;
            return Attempt::query()->create([
                'quiz_id'        => $quizId,
                'student_id'     => $studentId,
                'attempt_number' => $nextNumber,
                'status'         => 'in_progress',
                'is_passed'      => false,
                'score'          => 0,
                'start_at'       => $startAt,
                'ends_at'        => $endsAt,
            ]);

            });
            $attempt->load('quiz');
            return $this->ok('Attempt started successfully.', $attempt, 201);


        } catch (Throwable $e) {
            return $this->fail('Failed to start attempt.', $e, 500);
        }
    }
    public function grade(Attempt $attempt,int $score){
         try {
            DB::transaction(function () use ($attempt, $score) {
                $attempt->update([
                    'score'     => $score,
                    'is_passed' => $score >= $attempt->quiz->passing_score,
                    'status'    => 'graded',
                ]);
            });

            return $this->ok(
                'Attempt graded successfully.',
                $attempt->fresh()
            );
        } catch (Throwable $e) {
            return $this->fail(
                'Failed to grade attempt.',
                $e
            );
        }
    }
}

