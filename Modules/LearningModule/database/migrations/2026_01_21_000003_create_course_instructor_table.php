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
        Schema::create('course_instructor', function (Blueprint $table) {
            $table->id('course_instructor_id');
            $table->foreignId('course_id')->constrained('courses', 'course_id')->restrictOnDelete();
            $table->foreignId('instructor_id')->constrained('users', 'id')->cascadeOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users', 'id')->cascadeOnDelete();
            $table->boolean('is_primary')->default(false);
            $table->timestamp('assigned_at');
            $table->softDeletes('deleted_at');

            // Unique constraint to prevent duplicate assignments
            $table->unique(['course_id', 'instructor_id']);
            $table->index('is_primary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_instructor');
    }
};
