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
        Schema::create('user_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('current_level', 50); // e.g., 'primary-1', 'primary-2'
            $table->string('level_group', 50); // e.g., 'primary-lower', 'primary-upper'
            $table->integer('total_lessons_in_level')->default(0);
            $table->integer('completed_lessons')->default(0);
            $table->integer('total_quizzes_in_level')->default(0);
            $table->integer('completed_quizzes')->default(0);
            $table->decimal('average_quiz_score', 5, 2)->default(0.00); // Percentage
            $table->decimal('completion_percentage', 5, 2)->default(0.00);
            $table->boolean('level_completed')->default(false);
            $table->boolean('eligible_for_next_level')->default(false);
            $table->timestamp('level_started_at')->nullable();
            $table->timestamp('level_completed_at')->nullable();
            $table->json('performance_metrics')->nullable(); // Store detailed metrics
            $table->timestamps();

            // Ensure one progress record per user per level
            $table->unique(['user_id', 'current_level']);
            $table->index(['user_id', 'level_group']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_progress');
    }
};
