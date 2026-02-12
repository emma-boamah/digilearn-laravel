<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Add grace_period_ends_at column
        Schema::table('user_subscriptions', function (Blueprint $table) {
            $table->timestamp('grace_period_ends_at')->nullable()->after('expires_at');
        });

        // Add 'grace_period' to the status enum
        DB::statement("ALTER TABLE user_subscriptions MODIFY COLUMN status ENUM('active','inactive','cancelled','expired','trial','grace_period') DEFAULT 'trial'");
    }

    public function down()
    {
        Schema::table('user_subscriptions', function (Blueprint $table) {
            $table->dropColumn('grace_period_ends_at');
        });

        // Revert enum
        DB::statement("ALTER TABLE user_subscriptions MODIFY COLUMN status ENUM('active','inactive','cancelled','expired','trial') DEFAULT 'trial'");
    }
};
