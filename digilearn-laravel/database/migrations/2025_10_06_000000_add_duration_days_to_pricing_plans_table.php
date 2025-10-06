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
        Schema::table('pricing_plans', function (Blueprint $table) {
            if (!Schema::hasColumn('pricing_plans', 'duration_days')) {
                $table->integer('duration_days')->nullable()->after('period');
            }
            if(!Schema::hasColumn('pricing_plans', 'is_featured')){
                $table->boolean('is_featured')->default(false)->after('is_active');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pricing_plans', function (Blueprint $table) {
            if(Schema::hasColumn('pricing_plans', 'duration_days') && Schema::hasColumn('pricing_plans', 'is_featured')){
                $table->dropColumn(['duration_days', 'is_featured']);
            }
        });
    }
};