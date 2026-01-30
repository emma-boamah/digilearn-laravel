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
        Schema::createIfNotExists('notification_types', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('color', 9)->default('#3b82f6'); // support alpha hex (#RRGGBBAA)
            $table->boolean('is_system')->default(false);
            $table->boolean('is_active')->default(true);
            $table->json('default_channels')->nullable(); // handle default in model
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('low');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_system', 'is_active']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_types');
    }
};