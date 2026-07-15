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
            // Licensing & Tier
            $table->string('plan_tier')->default('basic')->after('status'); // basic, pro, enterprise
            $table->unsignedInteger('max_seats')->default(100)->after('plan_tier');
            $table->decimal('price_per_seat', 8, 2)->default(25.00)->after('max_seats');
            $table->string('billing_cycle')->default('term')->after('price_per_seat'); // term, annual

            // Subscription tracking
            $table->foreignId('pricing_plan_id')->nullable()->constrained('pricing_plans')->nullOnDelete()->after('billing_cycle');
            $table->timestamp('subscription_starts_at')->nullable()->after('pricing_plan_id');
            $table->timestamp('subscription_expires_at')->nullable()->after('subscription_starts_at');
            $table->timestamp('grace_period_ends_at')->nullable()->after('subscription_expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropForeign(['pricing_plan_id']);
            $table->dropColumn([
                'plan_tier', 'max_seats', 'price_per_seat', 'billing_cycle',
                'pricing_plan_id', 'subscription_starts_at', 'subscription_expires_at', 'grace_period_ends_at',
            ]);
        });
    }
};
