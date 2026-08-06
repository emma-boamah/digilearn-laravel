<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('tutor_profiles', function (Blueprint $table) {
            $table->string('scheduling_preference')->default('in_app')->after('scheduling_link');
        });
    }

    public function down()
    {
        Schema::table('tutor_profiles', function (Blueprint $table) {
            $table->dropColumn('scheduling_preference');
        });
    }
};
