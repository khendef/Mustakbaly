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
        Schema::create('answers', function (Blueprint $table) {
            $table->id();
             $table->foreignId('attempt_id')->constrained('attempts')->cascadeOnDelete();
             $table->foreignId('selected_option_id')
            ->nullable()
            ->constrained('question_options')
            ->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();
            $table->boolean('boolean_answer')->nullable();
            $table->json('answer_text')->nullable();
            $table->boolean('is_correct')->nullable();
            $table->unsignedInteger('question_score')->nullable();
            $table->timestamp('graded_at')->nullable();
            $table->foreignId('graded_by')->nullable()->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['attempt_id','question_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('answers');
    }
};
