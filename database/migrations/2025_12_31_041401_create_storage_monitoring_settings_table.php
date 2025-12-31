<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('storage_monitoring_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->json('monitored_paths'); // Array of storage paths to monitor
            $table->decimal('warning_threshold', 5, 2)->default(85.00); // Warning at 85%
            $table->decimal('critical_threshold', 5, 2)->default(95.00); // Critical at 95%
            $table->decimal('emergency_threshold', 5, 2)->default(98.00); // Emergency at 98%
            $table->decimal('recovery_threshold', 5, 2)->default(80.00); // Recovery when below 80%
            $table->integer('alert_throttle_minutes')->default(1440); // Min time between alerts (24 hours)
            $table->boolean('enable_predictive_alerts')->default(false);
            $table->integer('monitoring_interval_minutes')->default(60); // Check every hour
            $table->boolean('auto_cleanup_enabled')->default(false);
            $table->decimal('cleanup_threshold', 5, 2)->default(90.00); // Start cleanup at 90%
            $table->json('cleanup_rules')->nullable(); // Rules for automated cleanup
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Insert default settings
        DB::table('storage_monitoring_settings')->insert([
            'name' => 'default',
            'description' => 'Default storage monitoring configuration',
            'monitored_paths' => json_encode([
                storage_path(),
                storage_path('app'),
                storage_path('app/public'),
                storage_path('logs')
            ]),
            'warning_threshold' => 85.00,
            'critical_threshold' => 95.00,
            'emergency_threshold' => 98.00,
            'recovery_threshold' => 80.00,
            'alert_throttle_minutes' => 1440, // 24 hours
            'enable_predictive_alerts' => false,
            'monitoring_interval_minutes' => 60, // Every hour
            'auto_cleanup_enabled' => false,
            'cleanup_threshold' => 90.00,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('storage_monitoring_settings');
    }
};
