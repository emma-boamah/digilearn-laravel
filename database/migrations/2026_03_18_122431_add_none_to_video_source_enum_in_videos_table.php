<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE videos MODIFY COLUMN video_source ENUM('local', 'youtube', 'vimeo', 'mux', 'none') NOT NULL DEFAULT 'local'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE videos MODIFY COLUMN video_source ENUM('local', 'youtube', 'vimeo', 'mux') NOT NULL DEFAULT 'local'");
    }
};
