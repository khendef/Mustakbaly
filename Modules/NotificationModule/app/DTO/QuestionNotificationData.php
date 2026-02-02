<?php

namespace Modules\NotificationModule\DTO;

/**
 * Data Transfer Object for Question Notification
 *
 * @package Modules\NotificationModule\DTO
 */
class QuestionNotificationData
{
    public int $questionId;
    public int $quizId;
    public string $questionText;

    /**
     * Create a new DTO instance.
     *
     * @param int $questionId
     * @param int $quizId
     * @param string $questionText
     */
    public function __construct(int $questionId, int $quizId, string $questionText)
    {
        $this->questionId = $questionId;
        $this->quizId = $quizId;
        $this->questionText = $questionText;
    }
}
