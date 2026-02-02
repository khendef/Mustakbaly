<?php

use Illuminate\Support\Facades\Route;
use Modules\AssesmentModule\Http\Requests;
use Modules\AssesmentModule\Http\Controllers\AssesmentModuleController;
use Modules\AssesmentModule\Http\Controllers\Api\V1\AnswerController;
use Modules\AssesmentModule\Http\Controllers\Api\V1\AttemptController;
use Modules\AssesmentModule\Http\Controllers\Api\V1\QuestionController;
use Modules\AssesmentModule\Http\Controllers\Api\V1\QuestionOptionController;
use Modules\AssesmentModule\Http\Controllers\Api\V1\QuizController;

Route::middleware(['auth:api'])->prefix('v1')->group(function () {
    Route::apiResource('assesmentmodules', AssesmentModuleController::class)->names('assesmentmodule');
});
Route::prefix('v2')->group(function () {

    /*Quizzes*/
    Route::apiResource('quizzes', QuizController::class);
    Route::post('quizzes/{quiz}/publish',   [QuizController::class, 'publish']);
    Route::post('quizzes/{quiz}/unpublish', [QuizController::class, 'unpublish']);

    /* Questions
    */
    Route::apiResource('questions', QuestionController::class);

    /*
     Question Options
    */
    Route::apiResource('question-options', QuestionOptionController::class);

    /*
     Attempts
    */
    Route::apiResource('attempts', AttemptController::class);
    Route::post('attempts/start',            [AttemptController::class, 'start']);
    Route::post('attempts/{attempt}/submit', [AttemptController::class, 'submit']);
    Route::post('attempts/{attempt}/grade',  [AttemptController::class, 'grade']);
/***Answer */
    Route::apiResource('answer', AnswerController::class);
});

