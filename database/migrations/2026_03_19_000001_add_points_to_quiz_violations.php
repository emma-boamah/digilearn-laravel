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
        Schema::table('quiz_violations', function (Blueprint $table) {
            if (!Schema::hasColumn('quiz_violations', 'points')) {
                $table->integer('points')->default(1)->after('details');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quiz_violations', function (Blueprint $table) {
            $table->dropColumn('points');
        });
    }
};
