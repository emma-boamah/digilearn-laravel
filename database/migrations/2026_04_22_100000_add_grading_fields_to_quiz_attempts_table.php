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
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->string('status')->default('completed')->after('passed'); // 'completed', 'pending', 'graded'
            $table->json('grading_details')->nullable()->after('answers');
            $table->string('graded_by')->nullable()->after('grading_details');
            $table->timestamp('graded_at')->nullable()->after('graded_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->dropColumn(['status', 'grading_details', 'graded_by', 'graded_at']);
        });
    }
};
