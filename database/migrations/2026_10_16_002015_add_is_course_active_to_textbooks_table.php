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
        Schema::table('textbooks', function (Blueprint $table) {
            $table->boolean('is_course_active')->default(false)->after('is_content_approved');
            $table->index(['subject_id', 'is_course_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('textbooks', function (Blueprint $table) {
            $table->dropIndex(['subject_id', 'is_course_active']);
            $table->dropColumn('is_course_active');
        });
    }
};
