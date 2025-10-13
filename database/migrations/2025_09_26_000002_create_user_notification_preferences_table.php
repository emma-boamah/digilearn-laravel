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
        Schema::create('user_notification_preferences', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade');

            $table->foreignId('notification_type_id')
                ->constrained('notification_types')
                ->onDelete('cascade');

            $table->json('channels')->nullable(); 
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();

            // Short, custom index name
            $table->unique(['user_id', 'notification_type_id'], 'uniq_user_pref');
            $table->index(['user_id', 'is_enabled'], 'idx_user_pref_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_notification_preferences');
    }
};
