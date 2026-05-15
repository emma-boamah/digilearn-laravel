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
        Schema::table('videos', function (Blueprint $table) {
            $table->boolean('is_agent_generated')->default(false)->after('unit_name');
            $table->string('agent_query', 500)->nullable()->after('is_agent_generated');
            $table->string('agent_topic', 255)->nullable()->after('agent_query');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->dropColumn(['is_agent_generated', 'agent_query', 'agent_topic']);
        });
    }
};
