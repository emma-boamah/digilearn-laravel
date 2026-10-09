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
            $table->text('content_standard')->nullable()->after('title');
            $table->text('core_competencies')->nullable()->after('exemplars');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('curriculum_indicators', function (Blueprint $table) {
            $table->dropColumn(['content_standard', 'core_competencies']);
        });
    }
};
