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
            $table->string('type')->default('lesson')->after('level_group');
            $table->json('roadmap_data')->nullable()->after('gemini_response');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agent_requests', function (Blueprint $table) {
            $table->dropColumn(['type', 'roadmap_data']);
        });
    }
};
