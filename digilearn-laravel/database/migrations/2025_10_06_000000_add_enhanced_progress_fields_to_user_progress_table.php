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
            $table->unsignedBigInteger('total_time_spent_seconds')->default(0)->after('level_completed_at');
            $table->timestamp('last_activity_at')->nullable()->after('total_time_spent_seconds');
            $table->unsignedInteger('current_streak_days')->default(0)->after('last_activity_at');
            $table->unsignedInteger('longest_streak_days')->default(0)->after('current_streak_days');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_progress', function (Blueprint $table) {
            $table->dropColumn([
                'total_time_spent_seconds',
                'last_activity_at',
                'current_streak_days',
                'longest_streak_days'
            ]);
        });
    }
};