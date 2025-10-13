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
        Schema::table('users', function (Blueprint $table) {
            // Add current_room_id if it doesn't exist
            if (!Schema::hasColumn('users', 'current_room_id')) {
                $table->string('current_room_id')->nullable();
            }
            // Add is_online if it doesn't exist
            if (!Schema::hasColumn('users', 'is_online')) {
                $table->boolean('is_online')->default(false);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop columns
            $table->dropColumn(['current_room_id', 'is_online']);
        });
    }
};
