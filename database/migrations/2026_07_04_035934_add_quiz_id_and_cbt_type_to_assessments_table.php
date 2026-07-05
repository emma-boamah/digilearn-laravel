<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('assessments', function (Blueprint $table) {
            // Link an assessment to an optional Quiz for CBT mode
            $table->foreignId('quiz_id')->nullable()->constrained('quizzes')->onDelete('set null')->after('academic_term_id');
        });

        // Expand the type enum to include 'cbt'
        DB::statement("ALTER TABLE assessments MODIFY COLUMN type ENUM('exercise', 'homework', 'project', 'exam', 'mid_term', 'cbt') DEFAULT 'exercise'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assessments', function (Blueprint $table) {
            $table->dropForeign(['quiz_id']);
            $table->dropColumn('quiz_id');
        });

        // Revert the enum
        DB::statement("ALTER TABLE assessments MODIFY COLUMN type ENUM('exercise', 'homework', 'project', 'exam', 'mid_term') DEFAULT 'exercise'");
    }
};
