<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('tutor_profiles', function (Blueprint $table) {
            $table->string('legal_name')->nullable()->after('user_id');
            $table->string('tagline')->nullable()->after('bio');
            
            $table->string('headshot_path')->nullable()->after('tagline');
            $table->string('id_document_path')->nullable()->after('headshot_path');
            $table->string('tax_document_path')->nullable()->after('id_document_path');
            $table->string('certificates_path')->nullable()->after('tax_document_path');
            $table->string('test_video_path')->nullable()->after('certificates_path');
            
            $table->string('communication_handle')->nullable()->after('test_video_path');
            $table->string('payout_email')->nullable()->after('communication_handle');
            
            $table->boolean('is_verified')->default(false)->after('is_approved');
            $table->string('availability_status')->default('active')->after('is_verified');
        });
    }

    public function down()
    {
        Schema::table('tutor_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'legal_name',
                'tagline',
                'headshot_path',
                'id_document_path',
                'tax_document_path',
                'certificates_path',
                'test_video_path',
                'communication_handle',
                'payout_email',
                'is_verified',
                'availability_status'
            ]);
        });
    }
};
