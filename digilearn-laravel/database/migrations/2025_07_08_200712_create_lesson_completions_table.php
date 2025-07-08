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
        Schema::create('lesson_completions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('lesson_id');
            $table->string('lesson_title');
            $table->string('lesson_subject', 100);
            $table->string('lesson_level', 50);
            $table->integer('watch_time_seconds')->default(0); // Time spent watching
            $table->integer('total_duration_seconds')->default(0); // Total lesson duration
            $table->decimal('completion_percentage', 5, 2)->default(0.00);
            $table->boolean('fully_completed')->default(false);
            $table->integer('times_watched')->default(1);
            $table->timestamp('first_watched_at');
            $table->timestamp('last_watched_at');
            $table->timestamp('completed_at')->nullable();
            $table->json('watch_sessions')->nullable(); // Track individual watch sessions
            $table->timestamps();

            // Ensure one completion record per user per lesson
            $table->unique(['user_id', 'lesson_id']);
            $table->index(['user_id', 'lesson_level']);
            $table->index(['lesson_level', 'fully_completed']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lesson_completions');
    }
};
