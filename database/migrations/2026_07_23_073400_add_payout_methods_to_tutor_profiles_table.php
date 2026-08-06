<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('tutor_profiles', function (Blueprint $table) {
            // Drop old email
            $table->dropColumn('payout_email');

            // Add new payout fields
            $table->string('payout_method')->nullable()->after('communication_handle'); // 'momo' or 'bank'
            $table->string('payout_momo_network')->nullable()->after('payout_method');
            $table->string('payout_momo_number')->nullable()->after('payout_momo_network');
            $table->string('payout_bank_name')->nullable()->after('payout_momo_number');
            $table->string('payout_bank_account_name')->nullable()->after('payout_bank_name');
            $table->string('payout_bank_account_number')->nullable()->after('payout_bank_account_name');
            $table->string('payout_bank_branch')->nullable()->after('payout_bank_account_number');
        });
    }

    public function down()
    {
        Schema::table('tutor_profiles', function (Blueprint $table) {
            $table->string('payout_email')->nullable()->after('communication_handle');

            $table->dropColumn([
                'payout_method',
                'payout_momo_network',
                'payout_momo_number',
                'payout_bank_name',
                'payout_bank_account_name',
                'payout_bank_account_number',
                'payout_bank_branch'
            ]);
        });
    }
};
