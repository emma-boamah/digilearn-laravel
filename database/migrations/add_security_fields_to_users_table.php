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
        Schema::table('users', function (Blueprint $table) {
            $table->string('country')->nullable()->after('email');
            $table->integer('failed_login_attempts')->default(0)->after('remember_token');
            $table->timestamp('locked_until')->nullable()->after('failed_login_attempts');
            $table->timestamp('last_login_at')->nullable()->after('locked_until');
            $table->ipAddress('last_login_ip')->nullable()->after('last_login_at');
            $table->ipAddress('registration_ip')->nullable()->after('last_login_ip');
            
            // Two-factor authentication fields
            $table->text('two_factor_secret')->nullable()->after('registration_ip');
            $table->text('two_factor_recovery_codes')->nullable()->after('two_factor_secret');
            $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_recovery_codes');
            
            // Soft deletes
            $table->softDeletes()->after('updated_at');
            
            // Indexes for performance
            $table->index(['email', 'deleted_at']);
            $table->index(['failed_login_attempts', 'locked_until']);
            $table->index('last_login_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn([
                'country',
                'failed_login_attempts',
                'locked_until',
                'last_login_at',
                'last_login_ip',
                'registration_ip',
                'two_factor_secret',
                'two_factor_recovery_codes',
                'two_factor_confirmed_at',
            ]);
        });
    }
};