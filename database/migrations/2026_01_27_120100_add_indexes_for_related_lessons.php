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
            // Composite indexes for related lessons performance
            $table->index(['subject_id', 'grade_level', 'created_at'], 'idx_videos_subject_grade_created');
            $table->index(['subject_id', 'views'], 'idx_videos_subject_views');
            $table->index(['grade_level', 'views', 'created_at'], 'idx_videos_grade_views_created');
            $table->index(['uploaded_by', 'grade_level'], 'idx_videos_instructor_grade');
            
            // Index for subscription access control
            $table->index(['grade_level', 'status'], 'idx_videos_subscription_access');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->dropIndex('idx_videos_subject_grade_created');
            $table->dropIndex('idx_videos_subject_views');
            $table->dropIndex('idx_videos_grade_views_created');
            $table->dropIndex('idx_videos_instructor_grade');
            $table->dropIndex('idx_videos_subscription_access');
        });
    }
};