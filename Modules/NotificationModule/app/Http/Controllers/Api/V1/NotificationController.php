<?php
namespace Modules\NotificationModule\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\NotificationModule\DTO\AssignmentNotificationData;
use Modules\NotificationModule\DTO\NotificationData;
use Modules\NotificationModule\DTO\QuestionNotificationData;
use Modules\NotificationModule\Services\NotificationService;
use Modules\UserManagementModule\Models\User;
use Throwable;

/**
 * API Controller for listing and updating notifications.
 * Business logic is handled by NotificationService.
 */
class NotificationController extends Controller
{
    protected $notificationService;

     /**
     * create a new Controller instance
     * 
     * @param NotificationService $notificationService
     */
    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Get notification for a given user
     * 
     * @param int $userId
     * @return \Illuminate\Http\Response
     */
    public function getNotificationByUserId($userId){
        $notifications = $this->notificationService->getNotificationByUserId($userId);

        if (!$notifications){
            return response()->json(['message' => 'No notifications found for this user'],404);
        }
        return response()->json($notifications);
    }
     /**
     * Handle sending the notification via the API.
     *
     * @param Request $request
     * @param NotificationService $notificationService
     * @return \Illuminate\Http\Response
     */
    public function sendNotification(Request $request, NotificationService $notificationService)
    {
        $validated = $request->validate([
            'attempt_id' => 'required|integer',
            'student_id' => 'required|integer',
            'score' => 'required|integer',
            'is_passed' => 'required|boolean',
        ]);

        $notificationData = new NotificationData(
            attemptId: $validated['attempt_id'],
            score: $validated['score'],
            is_passed: $validated['is_passed'],
            studentId: $validated['student_id']
        );

       //used notification service from send notification
        $notificationService->sendQuizAttemptGradedNotification($notificationData);

        return response()->json(['message' => 'Notification sent successfully!']);
    }
     /**
     * Handle sending the question created notification.
     *
     * @param Request $request
     * @param NotificationService $notificationService
     * @return \Illuminate\Http\Response
     */
    public function sendQuestionNotification(Request $request, NotificationService $notificationService)
    {
        // Validate the request
        $validated = $request->validate([
            'question_id' => 'required|integer',
            'quiz_id' => 'required|integer',
            'question_text' => 'required|array',
            'question_text.en' => 'required|string',
            'question_text.ar' => 'required|string',
        ]);

        // Create DTO from validated data
        $notificationData = new QuestionNotificationData(
            $validated['question_id'],
            $validated['quiz_id'],
            $validated['question_text']
        );

        try {
            // Send the notification
            $notificationService->sendQuestionCreatedNotification($notificationData);

            return response()->json(['message' => 'Notification sent successfully.'], 200);
        } catch (Throwable $e) {
            // Handle any errors during notification sending
            return response()->json(['message' => 'Error sending notification: ' . $e->getMessage()], 500);
        }
    }

    
       public function sendAssignmentNotification(Request $request, NotificationService $notificationService)
    {
        $validated = $request->validate([
            'attempt_id' => 'required|integer',
            'quiz_id' => 'required|integer',
            'student_name' => 'required|string',
        ]);

        $notificationData = new AssignmentNotificationData(
            $validated['attempt_id'],
            $validated['quiz_id'],
            $validated['student_name']
        );


        $notificationService->sendAssignmentSubmittedNotification($notificationData);

        return response()->json(['message' => 'تم إرسال الإشعار بنجاح!']);
    }
}

