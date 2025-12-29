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
        Schema::table('progression_standards', function (Blueprint $table) {
            $table->decimal('individual_level_lesson_threshold', 5, 2)->default(75.00)->after('lesson_watch_threshold_percentage');
            $table->decimal('individual_level_quiz_threshold', 5, 2)->default(60.00)->after('individual_level_lesson_threshold');
            $table->decimal('individual_level_score_threshold', 5, 2)->default(65.00)->after('individual_level_quiz_threshold');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('progression_standards', function (Blueprint $table) {
            $table->dropColumn([
                'individual_level_lesson_threshold',
                'individual_level_quiz_threshold',
                'individual_level_score_threshold'
            ]);
        });
    }
};