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
        Schema::create('units', function (Blueprint $table) {
            $table->id('unit_id');
            $table->foreignId('course_id')->constrained('courses', 'course_id');
            $table->integer('unit_order');
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->integer('estimated_duration_minutes')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            // Unique constraint to ensure order is unique per course
            $table->unique(['course_id', 'unit_order']);
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
