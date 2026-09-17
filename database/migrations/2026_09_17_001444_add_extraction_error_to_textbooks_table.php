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
        Schema::table('textbooks', function (Blueprint $table) {
            $table->text('extraction_error')->nullable()->after('toc_extraction_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('textbooks', function (Blueprint $table) {
            $table->dropColumn('extraction_error');
        });
    }
};
