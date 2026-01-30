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
                // Column additions using macro
                $table->addColumnIfMissing('country', fn($t) => $t->string('country')->nullable()->after('email'));
                $table->addColumnIfMissing('failed_login_attempts', fn($t) => $t->integer('failed_login_attempts')->default(0)->after('remember_token'));
                $table->addColumnIfMissing('locked_until', fn($t) => $t->timestamp('locked_until')->nullable()->after('failed_login_attempts'));
                $table->addColumnIfMissing('last_login_at', fn($t) => $t->timestamp('last_login_at')->nullable()->after('locked_until'));
                $table->addColumnIfMissing('last_login_ip', fn($t) => $t->ipAddress('last_login_ip')->nullable()->after('last_login_at'));
                $table->addColumnIfMissing('registration_ip', fn($t) => $t->ipAddress('registration_ip')->nullable()->after('last_login_ip'));
                $table->addColumnIfMissing('two_factor_secret', fn($t) => $t->text('two_factor_secret')->nullable()->after('registration_ip'));
                $table->addColumnIfMissing('two_factor_recovery_codes', fn($t) => $t->text('two_factor_recovery_codes')->nullable()->after('two_factor_secret'));
                $table->addColumnIfMissing('two_factor_confirmed_at', fn($t) => $t->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_recovery_codes'));
                $table->addColumnIfMissing('deleted_at', fn($t) => $t->softDeletes()->after('updated_at'));
                
                // Index additions using macro
                $table->addIndexIfMissing(['email', 'deleted_at']);
                $table->addIndexIfMissing(['failed_login_attempts', 'locked_until']);
                $table->addIndexIfMissing('last_login_at', 'users_last_login_at_index');
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
