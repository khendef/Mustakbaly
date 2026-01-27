<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates a pivot table to track lesson completion per enrollment.
     * This allows multiple learners to have independent completion status for the same lesson.
     */
    public function up(): void
    {
        Schema::create('enrollment_lesson', function (Blueprint $table) {
            $table->id('enrollment_lesson_id');
            $table->foreignId('enrollment_id')->constrained('enrollments', 'enrollment_id')->cascadeOnDelete();
            $table->foreignId('lesson_id')->constrained('lessons', 'lesson_id')->cascadeOnDelete();
            $table->timestamp('completed_at')->useCurrent();
            $table->timestamps();

            // Unique constraint to prevent duplicate completions
            $table->unique(['enrollment_id', 'lesson_id'], 'unique_enrollment_lesson');

            // Indexes for performance
            $table->index('enrollment_id');
            $table->index('lesson_id');
            $table->index('completed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollment_lesson');
    }
};
