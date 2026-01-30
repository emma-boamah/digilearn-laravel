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
                // Column additions (Condensed for brevity)
                $cols = [
                    'country' => fn() => $table->string('country')->nullable()->after('email'),
                    'failed_login_attempts' => fn() => $table->integer('failed_login_attempts')->default(0)->after('remember_token'),
                    'locked_until' => fn() => $table->timestamp('locked_until')->nullable()->after('failed_login_attempts'),
                    'last_login_at' => fn() => $table->timestamp('last_login_at')->nullable()->after('locked_until'),
                    'last_login_ip' => fn() => $table->ipAddress('last_login_ip')->nullable()->after('last_login_at'),
                    'registration_ip' => fn() => $table->ipAddress('registration_ip')->nullable()->after('last_login_ip'),
                    'two_factor_secret' => fn() => $table->text('two_factor_secret')->nullable()->after('registration_ip'),
                    'two_factor_recovery_codes' => fn() => $table->text('two_factor_recovery_codes')->nullable()->after('two_factor_secret'),
                    'two_factor_confirmed_at' => fn() => $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_recovery_codes'),
                    'deleted_at' => fn() => $table->softDeletes()->after('updated_at'),
                ];

                foreach ($cols as $col => $definition) {
                    if (!Schema::hasColumn('users', $col)) $definition();
                }
                
                // FIX: Unified Index Checks
                // Check by column array OR specific name to prevent the 1061 error
                if (!Schema::hasIndex('users', ['email', 'deleted_at'])) {
                    $table->index(['email', 'deleted_at']);
                }
                
                if (!Schema::hasIndex('users', ['failed_login_attempts', 'locked_until'])) {
                    $table->index(['failed_login_attempts', 'locked_until']);
                }

                if (!Schema::hasIndex('users', 'users_last_login_at_index')) {
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
                // Drop indexes first if they exist
                if (Schema::hasIndex('users', 'users_last_login_at_index')) {
                    $table->dropIndex('users_last_login_at_index');
                }

                if (Schema::hasColumn('users', 'deleted_at')) {
                    $table->dropSoftDeletes();
                }

                $columns = ['country', 'failed_login_attempts', 'locked_until', 'last_login_at', 'last_login_ip', 'registration_ip', 'two_factor_secret', 'two_factor_recovery_codes', 'two_factor_confirmed_at'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('users', $column)) $table->dropColumn($column);
                }
            });
        }
    }
};
