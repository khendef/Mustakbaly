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
        Schema::create('quizzes', function (Blueprint $table) {
            $table->foreignId('course_id')
        ->constrained('courses')
        ->cascadeOnDelete();

         $table->foreignId('instructor_id')
         ->constrained('users')
         ->cascadeOnDelete();

    $table->unsignedBigInteger('quizable_id')->nullable();
    $table->string('quizable_type')->nullable();

    $table->json('title');
    $table->json('description')->nullable();

    $table->enum('type', ['quiz','assignment','practice'])->default('quiz');
    $table->unsignedInteger('max_score')->default(100);
    $table->unsignedInteger('passing_score')->nullable();
    $table->enum('status', ['published','draft'])->default('draft');
    $table->boolean('auto_grade_enabled')->default(true);

    $table->timestamp('available_from')->nullable();
    $table->timestamp('due_date')->nullable();
    $table->unsignedBigInteger('duration_minutes')->nullable();

    $table->timestamps();

    $table->index(['course_id','status']);
         });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};
