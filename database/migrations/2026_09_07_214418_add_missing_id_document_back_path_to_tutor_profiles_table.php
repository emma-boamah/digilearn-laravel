<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('tutor_profiles', function (Blueprint $table) {
            // Add missing columns
            if (!Schema::hasColumn('tutor_profiles', 'id_type')) {
                $table->string('id_type')->default('national_id')->after('headshot_path');
            }
            if (!Schema::hasColumn('tutor_profiles', 'id_document_back_path')) {
                $table->string('id_document_back_path')->nullable()->after('id_document_path');
            }
        });
    }

    public function down()
    {
        Schema::table('tutor_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('tutor_profiles', 'id_type')) {
                $table->dropColumn('id_type');
            }
            if (Schema::hasColumn('tutor_profiles', 'id_document_back_path')) {
                $table->dropColumn('id_document_back_path');
            }
        });
    }
};
