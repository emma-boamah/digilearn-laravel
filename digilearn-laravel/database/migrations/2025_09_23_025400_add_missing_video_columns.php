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
        Schema::table('videos', function (Blueprint $table) {
            // Check and add missing columns from previous migrations
            if (!Schema::hasColumn('videos', 'uploader_user_agent')) {
                $table->text('uploader_user_agent')->nullable()->after('uploader_ip');
            }
            
            if (!Schema::hasColumn('videos', 'views')) {
                $table->integer('views')->default(0)->after('uploader_user_agent');
            }
            
            if (!Schema::hasColumn('videos', 'document_path')) {
                $table->string('document_path')->nullable()->after('views');
            }
            
            if (!Schema::hasColumn('videos', 'quiz_id')) {
                $table->foreignId('quiz_id')->nullable()->constrained('quizzes')->onDelete('set null')->after('document_path');
            }
            
            // Add new workflow columns if they don't exist
            if (!Schema::hasColumn('videos', 'status')) {
                $table->enum('status', ['pending', 'approved', 'rejected', 'processing'])->default('pending')->after('is_featured');
            }
            
            if (!Schema::hasColumn('videos', 'vimeo_id')) {
                $table->string('vimeo_id')->nullable()->after('status');
            }
            
            if (!Schema::hasColumn('videos', 'vimeo_embed_url')) {
                $table->string('vimeo_embed_url')->nullable()->after('vimeo_id');
            }
            
            if (!Schema::hasColumn('videos', 'temp_file_path')) {
                $table->string('temp_file_path')->nullable()->after('video_path');
            }
            
            if (!Schema::hasColumn('videos', 'temp_expires_at')) {
                $table->timestamp('temp_expires_at')->nullable()->after('temp_file_path');
            }
            
            if (!Schema::hasColumn('videos', 'reviewed_by')) {
                $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null')->after('uploaded_by');
            }
            
            if (!Schema::hasColumn('videos', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            }
            
            if (!Schema::hasColumn('videos', 'review_notes')) {
                $table->text('review_notes')->nullable()->after('reviewed_at');
            }
            
            if (!Schema::hasColumn('videos', 'file_size_bytes')) {
                $table->bigInteger('file_size_bytes')->nullable()->after('duration_seconds');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $columns = [
                'uploader_user_agent', 'views', 'document_path', 'quiz_id',
                'status', 'vimeo_id', 'vimeo_embed_url', 'temp_file_path', 
                'temp_expires_at', 'reviewed_by', 'reviewed_at', 'review_notes', 
                'file_size_bytes'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('videos', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
