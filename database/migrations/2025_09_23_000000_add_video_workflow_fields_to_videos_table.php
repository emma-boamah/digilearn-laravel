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
            // Video workflow status
            $table->enum('status', ['pending', 'approved', 'rejected', 'processing'])->default('pending')->after('is_featured');
            
            // Vimeo integration
            $table->string('vimeo_id')->nullable()->after('status');
            $table->string('vimeo_embed_url')->nullable()->after('vimeo_id');
            
            // Temporary file management
            $table->string('temp_file_path')->nullable()->after('video_path');
            $table->timestamp('temp_expires_at')->nullable()->after('temp_file_path');
            
            // Review metadata
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null')->after('uploaded_by');
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            $table->text('review_notes')->nullable()->after('reviewed_at');
            
            // File size for cleanup management
            $table->bigInteger('file_size_bytes')->nullable()->after('duration_seconds');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->dropColumn([
                'status',
                'vimeo_id', 
                'vimeo_embed_url',
                'temp_file_path',
                'temp_expires_at',
                'reviewed_by',
                'reviewed_at',
                'review_notes',
                'file_size_bytes'
            ]);
        });
    }
};
