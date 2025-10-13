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
        Schema::create('level_progressions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('from_level', 50);
            $table->string('to_level', 50);
            $table->string('from_level_group', 50);
            $table->string('to_level_group', 50);
            $table->decimal('final_score', 5, 2); // Final score that triggered progression
            $table->integer('lessons_completed');
            $table->integer('quizzes_passed');
            $table->decimal('average_quiz_score', 5, 2);
            $table->json('progression_criteria')->nullable(); // Store criteria met
            $table->boolean('auto_progressed')->default(true); // Auto vs manual progression
            $table->timestamp('progressed_at');
            $table->timestamps();

            $table->index(['user_id', 'from_level']);
            $table->index(['user_id', 'to_level']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('level_progressions');
    }
};
