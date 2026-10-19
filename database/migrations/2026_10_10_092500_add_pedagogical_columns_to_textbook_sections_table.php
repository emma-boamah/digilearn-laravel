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
        Schema::table('textbook_sections', function (Blueprint $table) {
            $table->json('lesson_quiz')->nullable()->after('content_markdown');
            $table->json('key_takeaways')->nullable()->after('lesson_quiz');
            $table->string('pedagogical_scheme')->default('math_first_principles')->after('key_takeaways');
            $table->boolean('is_published')->default(true)->after('pedagogical_scheme');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('textbook_sections', function (Blueprint $table) {
            $table->dropColumn(['lesson_quiz', 'key_takeaways', 'pedagogical_scheme', 'is_published']);
        });
    }
};
