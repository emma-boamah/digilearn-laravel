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
            $table->string('mux_asset_id')->nullable()->after('vimeo_embed_url');
            $table->string('mux_playback_id')->nullable()->after('mux_asset_id');
            $table->string('mux_upload_id')->nullable()->after('mux_playback_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->dropColumn(['mux_asset_id', 'mux_playback_id', 'mux_upload_id']);
        });
    }
};