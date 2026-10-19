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
            $table->json('media_resources')->nullable()->after('lesson_quiz');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('textbook_sections', function (Blueprint $table) {
            $table->dropColumn('media_resources');
        });
    }
};
