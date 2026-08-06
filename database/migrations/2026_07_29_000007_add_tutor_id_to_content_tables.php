<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->foreignId('tutor_id')->nullable()->after('uploaded_by')->constrained('users')->nullOnDelete();
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->foreignId('tutor_id')->nullable()->after('uploaded_by')->constrained('users')->nullOnDelete();
        });

        Schema::table('quizzes', function (Blueprint $table) {
            $table->foreignId('tutor_id')->nullable()->after('uploaded_by')->constrained('users')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->dropForeign(['tutor_id']);
            $table->dropColumn('tutor_id');
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['tutor_id']);
            $table->dropColumn('tutor_id');
        });

        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropForeign(['tutor_id']);
            $table->dropColumn('tutor_id');
        });
    }
};
