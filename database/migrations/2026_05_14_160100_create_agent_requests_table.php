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
        Schema::create('agent_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('query', 500);
            $table->string('topic', 255)->nullable();
            $table->string('subject', 255)->nullable();
            $table->string('grade_level', 50)->nullable();
            $table->string('level_group', 50)->nullable();
            $table->foreignId('video_id')->nullable()->constrained()->onDelete('set null');
            $table->string('youtube_video_id', 50)->nullable();
            $table->enum('status', ['pending', 'analyzing', 'searching', 'found_existing', 'created', 'failed'])->default('pending');
            $table->json('gemini_response')->nullable();
            $table->json('youtube_results')->nullable();
            $table->text('error_message')->nullable();
            $table->integer('processing_time_ms')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['user_id', 'created_at']);
            $table->index(['topic', 'grade_level']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_requests');
    }
};
