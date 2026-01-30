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
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                // Add columns only if they don't exist
                if (!Schema::hasColumn('users', 'country')) {
                    $table->string('country')->nullable()->after('email');
                }
                if (!Schema::hasColumn('users', 'failed_login_attempts')) {
                    $table->integer('failed_login_attempts')->default(0)->after('remember_token');
                }
                if (!Schema::hasColumn('users', 'locked_until')) {
                    $table->timestamp('locked_until')->nullable()->after('failed_login_attempts');
                }
                if (!Schema::hasColumn('users', 'last_login_at')) {
                    $table->timestamp('last_login_at')->nullable()->after('locked_until');
                }
                if (!Schema::hasColumn('users', 'last_login_ip')) {
                    $table->ipAddress('last_login_ip')->nullable()->after('last_login_at');
                }
                if (!Schema::hasColumn('users', 'registration_ip')) {
                    $table->ipAddress('registration_ip')->nullable()->after('last_login_ip');
                }
                
                // Two-factor authentication fields
                if (!Schema::hasColumn('users', 'two_factor_secret')) {
                    $table->text('two_factor_secret')->nullable()->after('registration_ip');
                }
                if (!Schema::hasColumn('users', 'two_factor_recovery_codes')) {
                    $table->text('two_factor_recovery_codes')->nullable()->after('two_factor_secret');
                }
                if (!Schema::hasColumn('users', 'two_factor_confirmed_at')) {
                    $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_recovery_codes');
                }
                
                // Soft deletes
                if (!Schema::hasColumn('users', 'deleted_at')) {
                    $table->softDeletes()->after('updated_at');
                }
                
                // Indexes for performance (requires Laravel 11.x+ for Schema::hasIndex)
                if (!Schema::hasIndex('users', ['email', 'deleted_at'])) {
                    $table->index(['email', 'deleted_at']);
                }
                if (!Schema::hasIndex('users', ['failed_login_attempts', 'locked_until'])) {
                    $table->index(['failed_login_attempts', 'locked_until']);
                }
                if (!Schema::hasIndex('users', 'last_login_at')) {
                    $table->index('last_login_at');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'deleted_at')) {
                    $table->dropSoftDeletes();
                }

                $columnsToDrop = [
                    'country', 'failed_login_attempts', 'locked_until', 'last_login_at',
                    'last_login_ip', 'registration_ip', 'two_factor_secret',
                    'two_factor_recovery_codes', 'two_factor_confirmed_at'
                ];

                foreach ($columnsToDrop as $column) {
                    if (Schema::hasColumn('users', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
