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
        Schema::table('curriculum_indicators', function (Blueprint $table) {
            $table->longText('student_lesson_markdown')->nullable()->after('exemplars');
            $table->longText('student_lesson_html')->nullable()->after('student_lesson_markdown');
            $table->string('lesson_source', 30)->nullable()->after('student_lesson_html'); // textbook, ai, local, custom
            $table->json('lesson_key_takeaways')->nullable()->after('lesson_source');
            $table->timestamp('lesson_generated_at')->nullable()->after('lesson_key_takeaways');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('curriculum_indicators', function (Blueprint $table) {
            $table->dropColumn([
                'student_lesson_markdown',
                'student_lesson_html',
                'lesson_source',
                'lesson_key_takeaways',
                'lesson_generated_at',
            ]);
        });
    }
};
