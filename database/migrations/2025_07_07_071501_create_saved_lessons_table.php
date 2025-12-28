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
        Schema::create('saved_lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('lesson_id'); // Since lessons are stored as arrays, not in DB
            $table->string('lesson_title');
            $table->string('lesson_subject');
            $table->string('lesson_instructor');
            $table->string('lesson_year');
            $table->string('lesson_duration');
            $table->string('lesson_thumbnail');
            $table->string('lesson_video_url', 1000); // Increased to allow longer URLs
            $table->string('selected_level');
            $table->timestamp('saved_at');
            $table->timestamps();

            // Ensuring a user can't save the same lesson twice
            $table->unique(['user_id', 'lesson_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saved_lessons');
    }
};
