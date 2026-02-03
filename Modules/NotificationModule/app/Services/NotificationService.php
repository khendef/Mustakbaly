<?php
namespace Modules\NotificationModule\Services;

use Modules\AssesmentModule\Models\Quiz;
use Modules\NotificationModule\DTO\AssignmentNotificationData;
use Modules\NotificationModule\DTO\NotificationData;
use Modules\NotificationModule\DTO\QuestionNotificationData;
use Modules\NotificationModule\Notifications\InstructorAssignmentSubmitted;
use Modules\NotificationModule\Notifications\QuestionCreatedNotification;
use Modules\NotificationModule\Notifications\QuizAttemptGraded;
use Modules\UserManagementModule\Models\User;

/**
 * Service responsible for sending notifications.
 */
class NotificationService
{
    /**
     * Get notification for a given user by notification type
     * 
     * @param int $userId
     * @return mixed
     */
    public function getNotificationByUserId(int $userId){
         $user = User::find($userId);
          if(!$user){
            return null;
                }
            return $user->notifications;
    }

    /**
     * Send notification to the student when quiz is graded.
     *
     * @param NotificationData 
     * @return void
     */
    public function sendQuizAttemptGradedNotification(NotificationData $data): void
    {
        $student = User::find($data->studentId);

        if ($student) {
            $student->notify(new QuizAttemptGraded($data));
        }
    }

     /**
     * Send notification for a new question created.
     *
     * @param QuestionNotificationData $data The data for the notification
     * @return void
     */

    public function sendQuestionCreatedNotification(QuestionNotificationData $data): void
    {
      // Retrieve the quiz to get the associated instructor
     $quiz = Quiz::find($data->quizId); // Ensure that quizId exists and the quiz is found

    if ($quiz && $quiz->instructor) {
        // Send the notification to the instructor
        $quiz->instructor->notify(new QuestionCreatedNotification($data));
    }
    }

     /**
     * Send notification to the instructor when a student submits an assignment.
     *
     * @param AssignmentNotificationData $data The data for the notification
     * @return void
     */
    public function sendAssignmentSubmittedNotification(AssignmentNotificationData $data): void
    {
        // Get the instructor related to the quiz
        $instructor = User::find($data->quizId)->instructor;

        if ($instructor) { 
            // Send the notification
            $instructor->notify(new InstructorAssignmentSubmitted($data));
        }
    }
}