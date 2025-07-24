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
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('subject')->nullable(); // e.g., "Mathematics", "English"
            $table->foreignId('video_id')->nullable()->constrained('videos')->onDelete('set null'); // Link to a specific video
            $table->string('grade_level')->nullable(); // e.g., "Primary 1", "JHS 2"
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade'); // User who uploaded the quiz
            $table->text('quiz_data')->nullable(); // Store quiz questions/structure as JSON or text
            $table->unsignedBigInteger('views_count')->default(0);
            $table->unsignedBigInteger('attempts_count')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};
