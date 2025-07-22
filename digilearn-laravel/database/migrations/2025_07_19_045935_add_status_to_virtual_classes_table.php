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
        Schema::table('virtual_classes', function (Blueprint $table) {
            // Add the 'status' column after 'end_time'
            $table->string('status')->default('scheduled')->after('end_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('virtual_classes', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
