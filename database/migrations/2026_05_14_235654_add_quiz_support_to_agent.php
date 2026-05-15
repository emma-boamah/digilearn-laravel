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
        Schema::table('agent_requests', function (Blueprint $table) {
            $table->foreignId('quiz_id')->nullable()->constrained()->onDelete('set null')->after('video_id');
        });

        Schema::table('quizzes', function (Blueprint $table) {
            $table->boolean('is_agent_generated')->default(false)->after('is_featured');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agent_requests', function (Blueprint $table) {
            $table->dropForeign(['quiz_id']);
            $table->dropColumn('quiz_id');
        });

        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropColumn('is_agent_generated');
        });
    }
};
