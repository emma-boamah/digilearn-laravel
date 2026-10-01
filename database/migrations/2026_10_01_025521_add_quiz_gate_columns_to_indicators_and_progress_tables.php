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
        // 1. Add checkpoint quiz column to curriculum_indicators
        Schema::table('curriculum_indicators', function (Blueprint $table) {
            $table->json('lesson_quiz')->nullable()->after('lesson_key_takeaways');
        });

        // 2. Add quiz pass tracking columns to guided_learning_progress
        Schema::table('guided_learning_progress', function (Blueprint $table) {
            $table->unsignedTinyInteger('quiz_score')->nullable()->after('time_spent_seconds');
            $table->unsignedSmallInteger('quiz_attempts')->default(0)->after('quiz_score');
            $table->timestamp('quiz_passed_at')->nullable()->after('quiz_attempts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('curriculum_indicators', function (Blueprint $table) {
            $table->dropColumn('lesson_quiz');
        });

        Schema::table('guided_learning_progress', function (Blueprint $table) {
            $table->dropColumn([
                'quiz_score',
                'quiz_attempts',
                'quiz_passed_at',
            ]);
        });
    }
};
