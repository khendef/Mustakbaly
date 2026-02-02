<?php
namespace Modules\NotificationModule\DTO;

/**
 * Data Transfer Object for Assignment Submitted Notification.
 *
 * @package Modules\NotificationModule\DTO
 */
class AssignmentNotificationData
{
    public int $attemptId;
    public int $quizId;
    public string $studentName;

    /**
     * Create a new DTO instance.
     *
     * @param int $attemptId
     * @param int $quizId
     * @param string $studentName
     */
    public function __construct(int $attemptId, int $quizId, string $studentName)
    {
        $this->attemptId = $attemptId;
        $this->quizId = $quizId;
        $this->studentName = $studentName;
    }
}