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
        if (!Schema::hasTable('content_upload_tasks')) {
            Schema::create('content_upload_tasks', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->string('content_type', 30)->default('package'); // video, document, quiz, package
                $table->unsignedBigInteger('related_video_id')->nullable()->index();
                $table->string('title');
                $table->string('status', 30)->default('uploading'); // uploading, queued, processing, completed, failed, cancelled
                $table->unsignedTinyInteger('progress')->default(0); // 0 - 100
                $table->string('step_description')->default('Initializing upload...');
                $table->text('error_message')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'status']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_upload_tasks');
    }
};
