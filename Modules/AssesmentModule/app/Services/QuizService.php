<?php

namespace Modules\AssesmentModule\Services;
use Illuminate\Support\Facades\DB;
use Throwable;
use Modules\AssesmentModule\Models\Quiz;

class QuizService extends BaseService
{
    public function handle() {}
    public function index(array $filters = [], int $perPage = 15): array
    {
        try {
            $q = Quiz::query();

            if (!empty($filters['course_id'])) {
                $q->where('course_id', $filters['course_id']);
            }

            if (!empty($filters['instructor_id'])) {
                $q->where('instructor_id', $filters['instructor_id']);
            }

            if (!empty($filters['status'])) {
                $q->where('status', $filters['status']);
            }

            $q->latest('id');
            $data = $q->paginate($perPage);

            return $this->ok('Quizzes fetched successfully.', $data);
        } catch (Throwable $e) {
            return $this->fail('Failed to fetch quizzes.', $e);
        }
    }

    public function show(int $id): array
    {
        try {
            $quiz = Quiz::query()->with(['questions.options'])->findOrFail($id);
            return $this->ok('Quiz fetched successfully.', $quiz);
        } catch (Throwable $e) {
            return $this->fail('Failed to fetch quiz.', $e);
        }
    }

    public function store(array $data): array
    {
        try {
            $quiz = DB::transaction(function () use ($data) {
                return Quiz::query()->create($data);
            });

            return $this->ok('Quiz created successfully.', $quiz);
        } catch (Throwable $e) {
            return $this->fail('Failed to create quiz.', $e);
        }
    }

    public function update(Quiz $quiz, array $data): array
    {
        try {
            DB::transaction(function () use ($quiz, $data) {
                $quiz->fill($data);
                $quiz->save();
            });

            return $this->ok('Quiz updated successfully.', $quiz->fresh());
        } catch (Throwable $e) {
            return $this->fail('Failed to update quiz.', $e);
        }
    }
    public function publish(Quiz $quiz):array{
        try{
            $quiz->update(['status'=>'published']);
            return $this->ok('Quiz published successfully.',$quiz);
        }catch(Throwable $e){
            return $this->fail('Failed to publish quiz',null,500, $e->getMessage());
        }
    }
    public function unpublish(Quiz $quiz):array{
        try {
        if ($quiz->status !== 'published') {
            return $this->ok(
                'Quiz is already unpublished (draft).',
                $quiz
            );
        }

        if ($quiz->attempts()
            ->where('status', 'in_progress')
            ->exists()
        ) {
            return $this->fail(
                'Cannot unpublish quiz because an attempt is currently in progress.',
                null,
                422
            );
        }

        $quiz->update([
            'status' => 'draft',
        ]);

        return $this->ok(
            'Quiz unpublished successfully.',
            $quiz
        );

    } catch (Throwable $e) {
        return $this->fail(
            'Failed to unpublish quiz',
            null,
            500,
            $e->getMessage()
        );
    }
    }

    public function destroy(Quiz $quiz): array
    {
        try {
            DB::transaction(function () use ($quiz) {
                $quiz->delete();
            });

            return $this->ok('Quiz deleted successfully.');
        } catch (Throwable $e) {
            return $this->fail('Failed to delete quiz.', $e);
        }
    }


}
