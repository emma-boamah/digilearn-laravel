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
            //
            if (!Schema::hasColumn('videos', 'document_path')) {
                $table->string('document_path')->nullable()->after('description');
            }
            if (!Schema::hasColumn('videos', 'quiz_id')) {
                $table->unsignedBigInteger('quiz_id')->nullable()->after('document_path');
                $table->foreign('quiz_id')->references('id')->on('quizzes')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            //
            if (Schema::hasColumn('videos', 'quiz_id')) {
                $table->dropForeign(['quiz_id']);
                $table->dropColumn('quiz_id');
            }
            if (Schema::hasColumn('videos', 'document_path')){
                $table->dropColumn('document_path');
            }
        });
    }
};
