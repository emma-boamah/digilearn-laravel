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
        Schema::createIfNotExists('progression_standards', function (Blueprint $table) {
            $table->id();
            $table->string('level_group'); // e.g., 'primary-lower', 'jhs', 'shs'
            $table->decimal('required_lesson_completion_percentage', 5, 2)->default(80.00);
            $table->decimal('required_quiz_completion_percentage', 5, 2)->default(70.00);
            $table->decimal('required_average_quiz_score', 5, 2)->default(70.00);
            $table->decimal('minimum_quiz_score', 5, 2)->default(70.00);
            $table->decimal('lesson_watch_threshold_percentage', 5, 2)->default(90.00);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['level_group', 'is_active'], 'unique_active_standard_per_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('progression_standards');
    }
};