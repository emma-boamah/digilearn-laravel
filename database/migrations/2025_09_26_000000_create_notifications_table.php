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
        Schema::createIfNotExists('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type'); // Laravel uses FQCN of notification class
            $table->foreignId('notification_type_id')
                ->nullable()
                ->constrained('notification_types')
                ->onDelete('set null');

            // Manual morph columns to avoid duplicate index
            $table->string('notifiable_type');
            $table->unsignedBigInteger('notifiable_id');
            $table->index(['notifiable_type', 'notifiable_id'], 'notifiable_morph_idx');

            $table->json('data'); // notification payload
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};