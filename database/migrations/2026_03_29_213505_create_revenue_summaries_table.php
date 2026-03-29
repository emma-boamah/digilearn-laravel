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
        Schema::create('revenue_summaries', function (Blueprint $table) {
            $table->id();
            $table->string('period_type'); // daily, weekly, monthly, annual
            $table->date('period_date'); // The start date of the period
            $table->decimal('revenue', 15, 2)->default(0);
            $table->integer('payments_count')->default(0);
            $table->integer('subscriptions_count')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['period_type', 'period_date']);
            $table->index('period_type');
            $table->index('period_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('revenue_summaries');
    }
};
