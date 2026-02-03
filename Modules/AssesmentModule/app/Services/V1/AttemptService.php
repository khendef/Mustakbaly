<?php

namespace Modules\AssesmentModule\Services\V1;

use Modules\AssesmentModule\Models\Attempt;
use Modules\AssesmentModule\Models\Quiz;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * AttemptService handles the business logic for managing attempts.
 * It provides methods for creating, updating, retrieving, starting, submitting, and grading attempts.
 *
 * @package Modules\AssesmentModule\Services\V1
 */
class AttemptService
{
    /**
     * Fetch a paginated list of attempts based on the given filters.
     *
     * @param array $filters The filters to apply to the attempt query (e.g., student_id, quiz_id).
     * @param int $perPage The number of attempts per page (default is 15).
     * @return mixed The paginated list of attempts.
     * @throws \Exception If an error occurs while fetching the attempts.
     */
    public function index(array $filters = [], int $perPage = 15)
    {
        try {
            return Attempt::query()->filter($filters)->paginate($perPage); // Return data only
        } catch (Throwable $e) {
            throw new \Exception('Failed to fetch attempts: ' . $e->getMessage());
        }
    }

/**
 * Store a new attempt with the provided data.
 *
 * This method creates a new attempt record in the database by determining the next available
 * attempt number for the specified quiz and student. It sets the initial status to 'in_progress'
 * and initializes other relevant fields such as start time, end time, score, and pass status.
 *
 * @param array $data The validated data used to create a new attempt. Expected keys:
 *        - quiz_id (int): The ID of the quiz.
 *        - student_id (int): The ID of the student.
 *        - score (int, optional): The score of the attempt (default: 0).
 *        - is_passed (bool, optional): Whether the attempt is passed (default: false).
 *        - start_at (string, optional): The start date and time of the attempt (default: current time).
 *        - ends_at (string, optional): The end date and time of the attempt (default: null).
 *
 * @return Attempt The created attempt object with the relevant details stored in the database.
 * 
 * @throws \Exception If there is an error while creating the attempt or any database-related issues.
 */
public function store(array $data)
{
    try {
        // Get the highest attempt number for the student and quiz combination
        $quizId = $data['quiz_id'];
        $studentId = $data['student_id'];

        // Fetch the last attempt number for the given quiz and student
        $attemptNumber = Attempt::where('quiz_id', $quizId)
            ->where('student_id', $studentId)
            ->max('attempt_number');

        // Create a new attempt with the next attempt number
        $attempt = Attempt::create([
            'quiz_id' => $quizId,
            'student_id' => $studentId,
            'attempt_number' => $attemptNumber + 1, // Increment attempt number
            'status' => 'in_progress',  // Initial status
            'score' => $data['score'] ?? 0,  // Set score, default to 0 if not provided
            'is_passed' => $data['is_passed'] ?? false,  // Set pass status, default to false
            'start_at' => $data['start_at'] ?? now(),  // Set start time, default to current time if not provided
            'ends_at' => $data['ends_at'] ?? null,  // Set end time, default to null if not provided
            'submitted_at' => null,  // Initially null
            'graded_at' => null,  // Initially null
            'graded_by' => null,  // Initially null
            'time_spent_seconds' => null,  // Initially null
        ]);

        // Return the created attempt object
        return $attempt;
    } catch (Throwable $e) {
        // Throw an exception if an error occurs
        throw new \Exception('Failed to create attempt: ' . $e->getMessage());
    }
}


    /**
     * Retrieve the details of a specific attempt.
     *
     * @param Attempt $attempt The attempt to retrieve.
     * @return Attempt The requested attempt.
     * @throws \Exception If an error occurs while retrieving the attempt.
     */
    public function show(Attempt $attempt)
    {
        try {
            $attempt->load('quiz');
            return $attempt; // Return the attempt data
        } catch (Throwable $e) {
            throw new \Exception('Failed to fetch attempt: ' . $e->getMessage());
        }
    }

    /**
     * Update an existing attempt with the provided data.
     *
     * @param int $id The ID of the attempt to update.
     * @param array $data The data to update the attempt.
     * @return Attempt The updated attempt.
     * @throws \Exception If an error occurs while updating the attempt.
     */
    public function update(int $id, array $data)
    {
        try {
            $attempt = Attempt::find($id);
            if (!$attempt) {
                throw new \Exception('Attempt not found');
            }

            $attempt->update($data);
            return $attempt; // Return the updated attempt data
        } catch (Throwable $e) {
            throw new \Exception('Failed to update attempt: ' . $e->getMessage());
        }
    }

    /**
 * Start a new attempt for the student and quiz.
 *
 * This method validates the `quiz_id` and `student_id`, then it calculates 
 * the next attempt number for the student and quiz. It creates a new attempt 
 * record with the status set to "in_progress" and returns the created attempt.
 *
 * @param array $data The validated data for the attempt, including 'quiz_id' and 'student_id'.
 * 
 * @return Attempt The newly created attempt record.
 * 
 * @throws \Exception If the quiz is not found or not published, or if any other error occurs.
 */
public function start(array $data)

    {
    try {
        $quiz = Quiz::find($data['quiz_id']);
        if (!$quiz || $quiz->status !== 'published') {
            throw new \Exception('Quiz is not published');
        }

        // Get the last attempt number for the student in the same quiz
        $attemptNumber = Attempt::where('quiz_id', $data['quiz_id'])
            ->where('student_id', $data['student_id'])
            ->max('attempt_number') + 1;

        // Create the new attempt with the provided data
        return Attempt::create([
            'quiz_id' => $data['quiz_id'],
            'student_id' => $data['student_id'],
            'attempt_number' => $attemptNumber,
            'status' => 'in_progress', // Default status
            'score' => 0,  // Default score
            'is_passed' => false,  // Default passed status
            'start_at' => now(),  // Current time as start
            'ends_at' => now()->addMinutes($quiz->duration_minutes),  // Add quiz duration to set end time
        ]);
    } catch (Throwable $e) {
        throw new \Exception('Failed to start attempt: ' . $e->getMessage());
    }
    }
    /**
 * Submit an attempt and update its status to 'submitted'.
 *
 * This method checks if the attempt is in progress, then updates the `status` 
 * to 'submitted', sets the `submitted_at` timestamp, and calculates the time spent.
 *
 * @param int $attemptId The ID of the attempt to submit.
 * @param array $data The validated data, including 'status' and 'submitted_at'.
 * @return Attempt The updated attempt object.
 * @throws \Exception If the attempt is not in progress or any other error occurs.
 */
    public function submit(int $attemptId, array $data)
{
    try {
        // Retrieve the attempt by ID
        $attempt = Attempt::find($attemptId);

        // Check if the attempt exists and if the status is 'in_progress'
        if (!$attempt || $attempt->status !== 'in_progress') {
            throw new \Exception('Attempt is not in progress');
        }

        // Update the attempt with the new status and timestamp
        $attempt->update([
            'submitted_at' => now(),  // Set the submission time
            'status' => 'submitted',  // Update status to submitted
            'time_spent_seconds' => $attempt->start_at->diffInSeconds(now()),  // Calculate time spent
        ]);

        // Return the updated attempt object
        return $attempt;
    } catch (Throwable $e) {
        // Throw an exception if any error occurs
        throw new \Exception('Failed to submit attempt: ' . $e->getMessage());
    }
}



    /**
     * Grade an attempt after it has been submitted.
     *
     * @param int $attemptId The ID of the attempt to grade.
     * @param array $data The grading data (score, pass/fail status).
     * @param int|null $graderId The ID of the grader (optional).
     * @return Attempt The graded attempt.
     * @throws \Exception If an error occurs while grading the attempt.
     */
    public function grade(int $attemptId, array $data, ?int $graderId)
    {
        try {
            $attempt = Attempt::find($attemptId);
            if (!$attempt || $attempt->status !== 'submitted') {
                throw new \Exception('Attempt is not submitted yet');
            }

            $attempt->update([
                'score' => $data['score'],
                'is_passed' => $data['is_passed'],
                'graded_at' => now(),
                'graded_by' => $graderId,
                'status' => 'graded',
            ]);
         // Return the graded attempt data
            return $attempt; 
        } catch (Throwable $e) {
            throw new \Exception('Failed to grade attempt: ' . $e->getMessage());
        }
    }
    /**
 * Delete the specified attempt.
 *
 * This method finds the attempt by ID and deletes it from the database. If the attempt
 * does not exist, an exception is thrown. Returns the deleted attempt data.
 *
 * @param int $attemptId The ID of the attempt to delete.
 * @return Attempt The deleted attempt object.
 * @throws \Exception If the attempt is not found or any other error occurs.
 */
   public function delete(int $attemptId)
   {
    try {
        // Find the attempt by ID
        $attempt = Attempt::find($attemptId);

        // If attempt doesn't exist, throw an exception
        if (!$attempt) {
            throw new \Exception('Attempt not found');
        }

        // Delete the attempt
        $attempt->delete();

        // Return the deleted attempt data (optional)
        return $attempt;
    } catch (Throwable $e) {
        // Throw an exception if any error occurs
        throw new \Exception('Failed to delete attempt: ' . $e->getMessage());
    }
    }

}
