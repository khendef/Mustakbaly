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
        Schema::create('content_items', function (Blueprint $table) {
            $table->id('content_id');
            $table->foreignId('lesson_id')->constrained('lessons', 'lesson_id');
            $table->string('content_type', 50); // PDF, video, audio, presentation, interactive
            $table->string('title', 255);
            $table->bigInteger('file_size_bytes')->nullable();
            $table->integer('duration_seconds')->nullable();
            $table->boolean('is_downloadable')->default(true);
            $table->boolean('is_offline_available')->default(false);
            $table->integer('display_order');
            $table->timestamps();
            $table->softDeletes('deleted_at');

            // Unique constraint to ensure display order is unique per lesson
            $table->unique(['lesson_id', 'display_order']);
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_items');
    }
};
