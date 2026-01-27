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
        Schema::create('certificates', function (Blueprint $table) {
    $table->id();
    $table->foreignId('enrollment_id')->constrained('enrollments','enrollment_id')->cascadeOnDelete();
    $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
    $table->string('certificate_number')->unique();
    $table->date('completion_date');
    $table->date('issue_date');
    $table->timestamps();

    $table->index(['organization_id', 'issue_date','completion_date']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
