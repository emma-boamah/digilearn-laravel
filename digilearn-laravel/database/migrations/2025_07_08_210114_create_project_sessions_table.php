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
        Schema::create('project_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_project_id')->constrained()->onDelete('cascade');
            $table->timestamp('session_start');
            $table->timestamp('session_end')->nullable();
            $table->integer('duration_seconds')->default(0);
            $table->decimal('progress_at_start', 5, 2)->default(0);
            $table->decimal('progress_at_end', 5, 2)->default(0);
            $table->json('session_data')->nullable(); // Store session specific data
            $table->string('device_type')->nullable(); // mobile, desktop, tablet
            $table->string('browser')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->enum('session_status', ['active', 'completed', 'abandoned'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['user_project_id', 'session_start']);
            $table->index(['session_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_sessions');
    }
};
