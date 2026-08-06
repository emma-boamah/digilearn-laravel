<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->foreignId('tutor_id')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->integer('enrollment_count')->default(0)->after('price');
            $table->decimal('average_rating', 3, 2)->default(0.00)->after('enrollment_count');
            $table->integer('total_ratings')->default(0)->after('average_rating');
        });
    }

    public function down()
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropForeign(['tutor_id']);
            $table->dropColumn(['tutor_id', 'enrollment_count', 'average_rating', 'total_ratings']);
        });
    }
};
