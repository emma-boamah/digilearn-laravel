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
        if(!Schema::hasColumn('users', 'grade')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('grade')->nullable()->after('email'); // Adding 'grade' column after 'email'
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('grade'); // Dropping 'grade' column
        });
    }
};
