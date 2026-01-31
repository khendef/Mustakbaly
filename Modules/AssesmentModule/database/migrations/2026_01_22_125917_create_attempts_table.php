<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained('quizzes')->restrictOnDelete();
            $table->foreignId('student_id')->constrained('users')->restrictOnDelete();
            $table->unsignedInteger('attempt_number')->default(1);
            $table->unsignedInteger('score')->nullable();
            $table->enum('status',['in_progress','submitted','graded'])->default('in_progress');
            $table->boolean('is_passed')->nullable();
            $table->unsignedInteger('time_spent_seconds')->nullable();
            $table->timestamp('start_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('graded_at')->nullable();
            $table->foreignId('graded_by')->nullable()->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['quiz_id','student_id','attempt_number']);
            $table->index(['quiz_id','student_id','status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attempts');
    }
};
