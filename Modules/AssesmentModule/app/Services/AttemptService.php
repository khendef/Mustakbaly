<?php

namespace Modules\AssesmentModule\Services;
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
            $q = Attempt::query()
                ->with(['quiz'])
                ->latest('id');

            if (!empty($filters['quiz_id'])) {
                $q->where('quiz_id', $filters['quiz_id']);
            }
            if (!empty($filters['student_id'])) {
                $q->where('student_id', $filters['student_id']);
            }
            if (!empty($filters['status'])) {
                $q->where('status', $filters['status']);
            }

            $data = $q->paginate($perPage);

            return $this->ok('Attempts fetched successfully.', $data, 200);
        } catch (Throwable $e) {
            return $this->fail('Failed to fetch attempts.', $e, 500);
        }
    }
    public function store(array $data): array
    {
        try {
            $attempt = DB::transaction(function () use ($data) {

                $quizId    = (int) $data['quiz_id'];
                $studentId = (int) $data['student_id'];
                $attemptNumber = isset($data['attempt_number']) && $data['attempt_number'] !== null
                    ? (int) $data['attempt_number']
                    : (int) Attempt::query()
                        ->where('quiz_id', $quizId)
                        ->where('student_id', $studentId)
                        ->max('attempt_number') + 1;

                     $exists = Attempt::query()
                    ->where('quiz_id', $quizId)
                    ->where('student_id', $studentId)
                    ->where('attempt_number', $attemptNumber)
                    ->exists();

                if ($exists) {
                    return $this->fail('Attempt number already exists for this student in this quiz.', null, 422);
                }

                $attempt = Attempt::query()->create([
                    'quiz_id'             => $quizId,
                    'student_id'          => $studentId,
                    'attempt_number'      => $attemptNumber,
                    'status'              => $data['status'] ?? 'in_progress',
                    'score'               => $data['score'] ?? null,
                    'is_passed'           => $data['is_passed'] ?? null,
                    'start_at'            => $data['start_at'] ?? now(),
                    'ends_at'             => $data['ends_at'] ?? null,
                    'submitted_at'        => $data['submitted_at'] ?? null,
                    'graded_at'           => $data['graded_at'] ?? null,
                    'graded_by'           => $data['graded_by'] ?? null,
                    'time_spent_seconds'  => $data['time_spent_seconds'] ?? null,
                ]);

                return $this->ok('Attempt created successfully.', $attempt, 201);
            });

            return $attempt;
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

    public function start(array $data): array
    {
        try {
            $quizId = (int) $data['quiz_id'];
            $studentId = (int) ($data['student_id'] ?? Auth::id());

            $quiz = Quiz::query()->findOrFail($quizId);
            if (!empty($quiz->status) && $quiz->status !== 'published') {
                return [
                    'success' => false,
                    'message' => 'Quiz is not published.',
                    'data' => null,
                    'code' => 422,
                    'error' => 'Quiz is not published.',
                ];
            }

            $durationMinutes = (int) ($quiz->duration_minutes ?? 0);
            if ($durationMinutes <= 0) {
                return [
                    'success' => false,
                    'message' => 'Quiz duration_minutes is invalid.',
                    'data' => null,
                    'code' => 422,
                    'error' => 'Quiz duration_minutes is invalid.',
                ];
            }

            $startAt = now();
            $endsAt = $startAt->copy()->addMinutes($durationMinutes);

            $attempt = DB::transaction(function () use ($quizId, $studentId, $startAt, $endsAt) {

                $lastNumber = Attempt::query()
                    ->where('quiz_id', $quizId)
                    ->where('student_id', $studentId)
                    ->max('attempt_number');

                $nextNumber = ((int) $lastNumber) + 1;

                return Attempt::query()->create([
                    'quiz_id' => $quizId,
                    'student_id' => $studentId,
                    'attempt_number' => $nextNumber,
                    'status' => 'in_progress',
                    'score' => 0,
                    'is_passed' => false,
                    'start_at' => $startAt,
                    'ends_at' => $endsAt,
                ]);
            });

            $attempt->load(['quiz']);

            return $this->ok('Attempt started successfully.', $attempt, 201);
        } catch (Throwable $e) {
            return $this->fail('Failed to start attempt.', $e, 500);
        }
    }

    public function submit(Attempt $attempt, array $data = []): array
    {
        try {
            $studentId = (int) ($data['student_id'] ?? Auth::id());

            if ((int) $attempt->student_id !== $studentId) {
                return [
                    'success' => false,
                    'message' => 'Unauthorized.',
                    'data' => null,
                    'code' => 403,
                    'error' => 'Unauthorized.',
                ];
            }

            if (($attempt->status ?? null) !== 'in_progress') {
                return [
                    'success' => false,
                    'message' => 'Attempt is not in progress.',
                    'data' => null,
                    'code' => 422,
                    'error' => 'Attempt is not in progress.',
                ];
            }

            if (!empty($attempt->ends_at) && now()->greaterThan($attempt->ends_at)) {
                return [
                    'success' => false,
                    'message' => 'Attempt time is over.',
                    'data' => null,
                    'code' => 422,
                    'error' => 'Attempt time is over.',
                ];
            }

            $submittedAt = now();
            $startAt = $attempt->start_at ?? $submittedAt;

            $timeSpentSeconds = $submittedAt->diffInSeconds($startAt);

            $score = (int) ($attempt->score ?? 0);


            $isPassed = (bool) ($attempt->is_passed ?? false);

            $attempt->update([
                'status' => 'submitted',
                'submitted_at' => $submittedAt,
                'time_spent_seconds' => $timeSpentSeconds,
                'score' => $score,
                'is_passed' => $isPassed,
            ]);

            $attempt->refresh()->load(['quiz']);

            return $this->ok('Attempt submitted successfully.', $attempt, 200);
        } catch (Throwable $e) {
            return $this->fail('Failed to submit attempt.', $e, 500);
        }
    }

    public function grade(Attempt $attempt, array $data): array
    {
        try {
            $attempt->update([
                'score' => (int) $data['score'],
                'is_passed' => (bool) $data['is_passed'],
                'graded_at' => $data['graded_at'] ?? now(),
                'graded_by' => $data['graded_by'] ?? Auth::id(),
                'status' => 'graded',
            ]);

            $attempt->refresh()->load(['quiz']);

            return $this->ok('Attempt graded successfully.', $attempt, 200);
        } catch (Throwable $e) {
            return $this->fail('Failed to grade attempt.', $e, 500);
        }
    }
}


