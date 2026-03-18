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
            $table->string('page_url')->nullable()->after('consent_hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cookie_consents', function (Blueprint $table) {
            $table->dropColumn('page_url');
        });
    }
};
