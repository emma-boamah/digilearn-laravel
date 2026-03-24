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
            $table->foreignId('quiz_attempt_id')->nullable()->after('quiz_id')->constrained()->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quiz_violations', function (Blueprint $table) {
            $table->dropForeign(['quiz_attempt_id']);
            $table->dropColumn('quiz_attempt_id');
        });
    }
};
