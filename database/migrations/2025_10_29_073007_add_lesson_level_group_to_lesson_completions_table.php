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
        Schema::table('lesson_completions', function (Blueprint $table) {
            $table->string('lesson_level_group', 50)->nullable()->after('lesson_level');

            // Add index for better query performance
            $table->index(['user_id', 'lesson_level_group']);
            $table->index(['lesson_level_group', 'fully_completed']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lesson_completions', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'lesson_level_group']);
            $table->dropIndex(['lesson_level_group', 'fully_completed']);
            $table->dropColumn('lesson_level_group');
        });
    }
};
