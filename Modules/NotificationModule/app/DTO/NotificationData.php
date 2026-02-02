<?php
namespace Modules\NotificationModule\DTO;

class NotificationData{
    public int $attemptId;
    public int $score;
    public bool $is_passed;
    public int $studentId;

    /**
     * Create a new DTO instance.
     * 
     * @param int $attemptId
     * @param int $score
     * @param int $is_passed
     * @param int $studentId
     * 
     */


    public function __construct(
        int $attemptId,int $score,bool $is_passed,int $studentId
    )
    {
       $this->attemptId = $attemptId;
       $this->score = $score;
       $this->is_passed = $is_passed;
       $this->studentId = $studentId;
    }
}