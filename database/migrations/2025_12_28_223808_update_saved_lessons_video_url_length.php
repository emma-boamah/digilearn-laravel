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
        Schema::table('saved_lessons', function (Blueprint $table) {
            $table->string('lesson_video_url', 1000)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('saved_lessons', function (Blueprint $table) {
            $table->string('lesson_video_url', 255)->change();
        });
    }
};
