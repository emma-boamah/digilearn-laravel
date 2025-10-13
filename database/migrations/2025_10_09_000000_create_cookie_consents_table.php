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
        Schema::create('cookie_consents', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->json('consent_data'); // Store consent preferences as JSON
            $table->string('consent_hash')->nullable(); // For tracking changes
            $table->timestamp('consented_at');
            $table->timestamps();

            // Index for performance
            $table->index(['ip_address', 'consented_at']);
            $table->index('consent_hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cookie_consents');
    }
};