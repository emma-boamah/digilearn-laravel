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
        Schema::table('schools', function (Blueprint $table) {
            // School Profile
            $table->string('school_type')->nullable()->after('name');
            $table->string('ges_registration_number')->nullable()->after('school_type');
            $table->string('phone')->nullable()->after('ges_registration_number');
            $table->string('school_email')->nullable()->after('phone');

            // Location (GPS-derived)
            $table->string('gps_address')->nullable()->after('school_email');
            $table->string('region')->nullable()->after('gps_address');
            $table->string('district')->nullable()->after('region');
            $table->string('city')->nullable()->after('district');

            // Legal Documents
            $table->string('ges_certificate_path')->nullable()->after('city');
            $table->string('business_certificate_path')->nullable()->after('ges_certificate_path');
            $table->string('tin_number')->nullable()->after('business_certificate_path');

            // Document verification status
            $table->string('verification_status')->default('pending')->after('tin_number');
            $table->timestamp('verified_at')->nullable()->after('verification_status');
            $table->unsignedBigInteger('verified_by')->nullable()->after('verified_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn([
                'school_type', 'ges_registration_number', 'phone', 'school_email',
                'gps_address', 'region', 'district', 'city',
                'ges_certificate_path', 'business_certificate_path', 'tin_number',
                'verification_status', 'verified_at', 'verified_by'
            ]);
        });
    }
};
