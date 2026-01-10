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
        Schema::table('user_progress', function (Blueprint $table) {
            $table->boolean('is_active')->default(false)->after('eligible_for_next_level');
            $table->timestamp('last_accessed_at')->nullable()->after('level_completed_at');
            $table->integer('time_spent_seconds')->default(0)->after('last_accessed_at');
            $table->json('metadata')->nullable()->after('performance_metrics');
        });

        // Drop the unique constraint to allow multiple records per user
        Schema::table('user_progress', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'current_level']);
        });

        // Add new indexes
        Schema::table('user_progress', function (Blueprint $table) {
            $table->index(['user_id', 'is_active']);
            $table->index(['user_id', 'level_group', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_progress', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'is_active']);
            $table->dropIndex(['user_id', 'level_group', 'is_active']);
            $table->dropColumn(['is_active', 'last_accessed_at', 'time_spent_seconds', 'metadata']);
        });

        // Restore the unique constraint
        Schema::table('user_progress', function (Blueprint $table) {
            $table->unique(['user_id', 'current_level']);
        });
    }
};
