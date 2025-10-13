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
        Schema::table('quizzes', function (Blueprint $table) {
            $table->integer('time_limit_minutes')->default(30)->after('is_featured'); // Time limit in minutes
            $table->decimal('average_rating', 3, 2)->default(0)->after('time_limit_minutes'); // Average rating (0-5)
            $table->integer('total_ratings')->default(0)->after('average_rating'); // Total number of ratings
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropColumn(['time_limit_minutes', 'average_rating', 'total_ratings']);
        });
    }
};