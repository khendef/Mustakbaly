<?php

namespace Modules\AssesmentModule\Services\V1;

use Modules\AssesmentModule\Models\Attempt;
use Modules\AssesmentModule\Models\Quiz;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Class AttemptService
 *
 * This service handles the business logic for managing attempts, including:
 * - Fetching attempts with filters and pagination
 * - Creating, updating, and deleting attempts
 * - Retrieving a single attempt by ID
 * - Starting, submitting, and grading attempts
 *
 * It encapsulates all attempt-related operations, ensuring that the business rules are respected.
 * The service uses exception handling to manage errors and provides consistent responses.
 *
 * @package Modules\AssesmentModule\Services\V1
 */
class AttemptService extends BaseService
{
    /**
     * Handle the service logic. Currently a placeholder for additional functionality.
     *
     * @return void
     */
    public function handle() {}

    /**
     * Fetch a paginated list of attempts based on the given filters.
     *
     * @param array $filters The filters to apply to the attempt query (e.g., student_id, quiz_id).
     * @param int $perPage The number of attempts per page (default is 15).
     *
     * @return array<string, mixed> The result of the operation with status and data.
     */
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

    /**
     * Store a new attempt with the provided data.
     *
     * @param array $data The data to create a new attempt.
     *
     * @return array<string, mixed> The result of the operation with status and the created attempt.
     */
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
                'score'               => (int)($data['score'] ?? 0),
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

    /**
     * Update an existing attempt with the provided data.
     *
     * @param int $id The ID of the attempt to update.
     * @param array $data The data to update the attempt.
     *
     * @return array<string, mixed> The result of the operation with status and the updated attempt.
     */
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

    /**
     * Fetch a single attempt by its ID, including its associated quiz.
     *
     * @param Attempt $attempt The attempt to retrieve.
     *
     * @return array<string, mixed> The result of the operation with status and the fetched attempt.
     */
    public function show(Attempt $attempt): array
    {
        try {
            $attempt->load(['quiz']);
            return $this->ok('Attempt fetched successfully.', $attempt, 200);
        } catch (Throwable $e) {
            return $this->fail('Failed to fetch attempt.', $e, 500);
        }
    }

    /**
     * Submit an attempt, updating its status to 'submitted' and recording the submission time.
     *
     * @param int $attemptId The ID of the attempt to submit.
     *
     * @return array<string, mixed> The result of the operation with status and the updated attempt.
     */
    public function submit(int $attemptId): array
    {
        try {
            $attempt = Attempt::findOrFail($attemptId);
            if ($attempt->status !== 'in_progress') {
                return $this->ok('Attempt is not in progress', $attempt, 200);
            }

            $attempt->submitted_at = now();
            $attempt->status = 'submitted';

            if ($attempt->start_at) {
                $attempt->time_spent_seconds = max(0, $attempt->start_at->diffInSeconds($attempt->submitted_at));
            }

            $attempt->save();
            return $this->ok('Attempt submitted successfully', $attempt->fresh(), 200);
        } catch (Throwable $e) {
            return $this->fail('Failed to submit attempt', null, $e->getMessage(), 500);
        }
    }

    /**
     * Start an attempt for a student, initializing the necessary fields and validating conditions.
     *
     * @param array $data The data required to start the attempt.
     *
     * @return array<string, mixed> The result of the operation with status and the created attempt.
     */
    public function start(array $data): array
    {
        try {
            $quizId = (int)($data['quiz_id'] ?? 0);
            if ($quizId <= 0) {
                return $this->ok('quiz_id is required.', null, 422);
            }

            $studentId = (int)($data['student_id'] ?? Auth::id());
            $quiz = Quiz::query()->findOrFail($quizId);

            if (($quiz->status ?? null) !== 'published') {
                return $this->ok('Quiz is not published.', null, 422);
            }

            $durationMinutes = (int)($quiz->duration_minutes ?? 0);
            if ($durationMinutes <= 0) {
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

                $nextNumber = ((int)$lastNumber) + 1;
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

    /**
     * Grade an attempt after it has been submitted.
     *
     * @param int $attemptId The ID of the attempt to grade.
     * @param array $data The grading data (score, pass/fail status).
     * @param int|null $graderId The ID of the grader (optional).
     *
     * @return array<string, mixed> The result of the operation with status and the graded attempt.
     */
    public function grade(int $attemptId, array $data, ?int $graderId = null): array
    {
        try {
            $attempt = Attempt::findOrFail($attemptId);

            if ($attempt->status !== 'submitted') {
                return $this->ok('Attempt is not submitted yet.', $attempt, 200);
            }

            $attempt->score = (int)($data['score'] ?? $attempt->score);
            $attempt->is_passed = (bool)($data['is_passed'] ?? $attempt->is_passed);
            $attempt->graded_at = now();
            $attempt->graded_by = $graderId;
            $attempt->status = 'graded';

            if (!$attempt->time_spent_seconds && $attempt->start_at) {
                $end = $attempt->submitted_at ?? now();
                $attempt->time_spent_seconds = max(0, $attempt->start_at->diffInSeconds($end));
            }

            $attempt->save();
            return $this->ok('Attempt graded successfully', $attempt->fresh(), 200);
        } catch (Throwable $e) {
            return $this->fail('Failed to grade attempt', null, $e->getMessage(), 500);
        }
    }
}
