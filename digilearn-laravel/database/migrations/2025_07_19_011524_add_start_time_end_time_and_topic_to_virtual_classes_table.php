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
        Schema::table('virtual_classes', function (Blueprint $table) {
            if (!Schema::hasColumn('virtual_classes', 'start_time')) {
                $table->timestamp('start_time')->nullable()->after('is_active');
            }
            if (!Schema::hasColumn('virtual_classes', 'end_time')) {
                $table->timestamp('end_time')->nullable()->after('start_time');
            }
            if (!Schema::hasColumn('virtual_classes', 'topic')) {
                $table->string('topic')->nullable()->after('grade_level');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('virtual_classes', function (Blueprint $table) {
            $table->dropColumn(['start_time', 'end_time', 'topic']);
        });
    }
};