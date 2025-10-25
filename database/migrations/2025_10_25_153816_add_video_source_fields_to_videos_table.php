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
            $table->enum('video_source', ['local', 'youtube', 'vimeo', 'mux'])->default('local')->after('title');
            $table->string('external_video_id')->nullable()->after('video_source');
            $table->string('external_video_url')->nullable()->after('external_video_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->dropColumn(['video_source', 'external_video_id', 'external_video_url']);
        });
    }
};
