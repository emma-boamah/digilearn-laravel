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
            $table->integer('required_number_of_lessons_individual')->default(10)->after('lesson_watch_threshold_percentage');
            $table->integer('required_number_of_quizzes_individual')->default(5)->after('required_number_of_lessons_individual');
            $table->integer('required_number_of_lessons_group')->default(20)->after('required_number_of_quizzes_individual');
            $table->integer('required_number_of_quizzes_group')->default(10)->after('required_number_of_lessons_group');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('progression_standards', function (Blueprint $table) {
            $table->dropColumn([
                'required_number_of_lessons_individual',
                'required_number_of_quizzes_individual',
                'required_number_of_lessons_group',
                'required_number_of_quizzes_group'
            ]);
        });
    }
};