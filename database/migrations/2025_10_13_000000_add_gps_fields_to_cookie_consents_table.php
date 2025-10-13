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
        Schema::table('cookie_consents', function (Blueprint $table) {
            $table->decimal('latitude', 10, 8)->nullable()->after('user_agent');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            $table->string('country')->nullable()->after('longitude');
            $table->string('city')->nullable()->after('country');
            $table->string('region')->nullable()->after('city');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cookie_consents', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude', 'country', 'city', 'region']);
        });
    }
};