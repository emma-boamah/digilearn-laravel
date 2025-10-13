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
        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('quiz_id');
            $table->string('quiz_title');
            $table->string('quiz_subject', 100);
            $table->string('quiz_level', 50);
            $table->integer('total_questions');
            $table->integer('correct_answers')->default(0);
            $table->integer('incorrect_answers')->default(0);
            $table->decimal('score_percentage', 5, 2)->default(0.00);
            $table->integer('time_taken_seconds')->default(0);
            $table->boolean('passed')->default(false); // Based on passing criteria
            $table->integer('attempt_number')->default(1); // Track multiple attempts
            $table->json('answers')->nullable(); // Store user answers
            $table->json('question_details')->nullable(); // Store questions and correct answers
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'quiz_level']);
            $table->index(['quiz_level', 'passed']);
            $table->index(['user_id', 'quiz_id', 'attempt_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_attempts');
    }
};
