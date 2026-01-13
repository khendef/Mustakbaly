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
        Schema::create('lessons', function (Blueprint $table) {
            $table->id('lesson_id');
            $table->foreignId('unit_id')->constrained('units', 'unit_id');
            $table->integer('lesson_order');
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->text('content')->nullable();
            $table->string('lesson_type', 50); // lecture, video, interactive, reading
            $table->boolean('is_required')->default(true);
            $table->integer('estimated_duration_minutes')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');

            // Unique constraint to ensure order is unique per unit
            $table->unique(['unit_id', 'lesson_order']);
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
