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
        // Storage analytics table for tracking usage over time
        Schema::createIfNotExists('storage_analytics', function (Blueprint $table) {
            $table->id();
            $table->string('path'); // Storage path being monitored
            $table->unsignedBigInteger('total_space_bytes');
            $table->unsignedBigInteger('used_space_bytes');
            $table->unsignedBigInteger('free_space_bytes');
            $table->decimal('usage_percentage', 5, 2);
            $table->decimal('growth_rate_percentage', 5, 2)->nullable(); // Growth rate compared to previous measurement
            $table->json('file_counts')->nullable(); // Count of different file types
            $table->timestamp('measured_at');
            $table->timestamps();

            $table->index(['path', 'measured_at']);
        });

        // Storage alerts table for alert history and throttling
        Schema::create('storage_alerts', function (Blueprint $table) {
            $table->id();
            $table->string('alert_type'); // warning, critical, emergency, recovery
            $table->string('path'); // Storage path that triggered the alert
            $table->decimal('usage_percentage', 5, 2);
            $table->unsignedBigInteger('used_space_bytes');
            $table->unsignedBigInteger('total_space_bytes');
            $table->json('admin_users_notified')->nullable(); // IDs of admins notified
            $table->timestamp('alert_sent_at');
            $table->timestamps();

            $table->index(['alert_type', 'path', 'alert_sent_at']);
        });

        // Storage quotas table for user/organization limits
        Schema::create('storage_quotas', function (Blueprint $table) {
            $table->id();
            $table->morphs('quotable'); // Can be applied to users, organizations, etc.
            $table->unsignedBigInteger('quota_bytes'); // Storage limit in bytes
            $table->unsignedBigInteger('used_bytes')->default(0);
            $table->decimal('warning_threshold_percentage', 5, 2)->default(80.00);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['quotable_type', 'quotable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('storage_quotas');
        Schema::dropIfExists('storage_alerts');
        Schema::dropIfExists('storage_analytics');
    }
};
