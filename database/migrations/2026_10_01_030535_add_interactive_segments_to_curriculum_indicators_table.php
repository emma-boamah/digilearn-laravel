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
            $table->json('interactive_segments')->nullable()->after('lesson_quiz');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('curriculum_indicators', function (Blueprint $table) {
            $table->dropColumn('interactive_segments');
        });
    }
};
