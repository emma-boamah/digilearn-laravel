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
        Schema::create('user_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('project_type'); // 'lesson' or 'quiz'
            $table->string('project_id'); // lesson_id or quiz_id
            $table->string('project_title');
            $table->string('project_subject');
            $table->string('project_level');
            $table->enum('status', ['not_started', 'in_progress', 'completed', 'paused'])->default('not_started');
            $table->decimal('progress_percentage', 5, 2)->default(0);
            $table->json('project_data'); // Store lesson/quiz specific data
            $table->json('progress_data')->nullable(); // Store progress specific data
            $table->timestamp('started_at')->nullable();
            $table->timestamp('last_accessed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('paused_at')->nullable();
            $table->integer('time_spent_seconds')->default(0);
            $table->integer('access_count')->default(0);
            $table->boolean('is_favorite')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes for better performance
            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'project_type']);
            $table->index(['user_id', 'last_accessed_at']);
            $table->unique(['user_id', 'project_type', 'project_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_projects');
    }
};
