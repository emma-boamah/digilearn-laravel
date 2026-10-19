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
        Schema::table('guided_learning_progress', function (Blueprint $table) {
            $table->unsignedBigInteger('curriculum_indicator_id')->nullable()->change();
            $table->index(['user_id', 'textbook_section_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guided_learning_progress', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'textbook_section_id']);
            $table->unsignedBigInteger('curriculum_indicator_id')->nullable(false)->change();
        });
    }
};
