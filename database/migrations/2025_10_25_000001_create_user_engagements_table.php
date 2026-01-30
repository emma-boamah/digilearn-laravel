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
        Schema::createIfNotExists('user_engagements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('content_type'); // video, document, quiz, lesson
            $table->unsignedBigInteger('content_id');
            $table->string('action'); // view, start, complete, like, share, bookmark, pause, resume, skip
            $table->tinyInteger('engagement_score')->default(1); // 1-10 scale
            $table->unsignedInteger('duration_seconds')->default(0);
            $table->json('metadata')->nullable(); // additional context data
            $table->string('session_id')->nullable(); // to group related actions
            $table->string('device_type')->default('desktop'); // mobile, desktop, tablet
            $table->text('user_agent')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'content_type', 'created_at']);
            $table->index(['content_type', 'content_id']);
            $table->index(['user_id', 'engagement_score']);
            $table->index(['session_id']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_engagements');
    }
};