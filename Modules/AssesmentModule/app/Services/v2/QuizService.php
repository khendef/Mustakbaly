<?php

namespace Modules\AssesmentModule\Services\v2;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Modules\AssesmentModule\Events\QuestionCreated;
use Modules\AssesmentModule\Models\Quiz;
use Modules\AssesmentModule\Models\Builders\QuizBuilder;
use Modules\AssesmentModule\Models\Question;

use Throwable;

class QuizService extends BaseService
{
    public function handle() {}
    public function index(array $filters = [],int $perPage =15): array
    {
        try {
            $data = Quiz::query()
            ->filter($filters)
            ->paginate($perPage);
                return $this->ok('Quiz fetched successfully.', $data, 200);
        } catch (Throwable $e) {
            return $this->fail('Failed to fetch quizzes.', $e, 500);
        }
    }

    public function show(int $id): array
    {
        try {
            $quiz = Quiz::query()->with(['questions.options'])->findOrFail($id);
            return $this->ok('Quiz fetched successfully.', $quiz);
        }catch (ModelNotFoundException $e) {
            return $this->fail('Quiz not found.', $e,404);
        } catch (Throwable $e) {
            return $this->fail('Failed to fetch quiz.', $e);
        }
    }

    public function create(array $data): array
    {
        try {
        $question = Question::create($data);

        event(new QuestionCreated($question));

        return $this->ok(
            'Question created successfully.',
            $question,
            201
        );
    } catch (Throwable $e) {
        return $this->fail(
            'Failed to create question.',
            $e,
            500
        );
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
