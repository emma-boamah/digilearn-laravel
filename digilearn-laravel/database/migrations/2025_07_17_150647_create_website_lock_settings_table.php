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
        Schema::create('website_lock_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_locked')->default(false);
            $table->timestamps();
        });
        
        // Initial setting
        DB::table('website_lock_settings')->insert(['is_locked' => false]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('website_lock_settings');
    }
};
