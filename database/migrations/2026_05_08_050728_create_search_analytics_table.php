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
        Schema::create('search_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('query');
            $table->string('domain')->index(); // 'lesson', 'quiz', 'note'
            $table->unsignedInteger('hits')->default(1);
            $table->timestamp('last_searched_at')->useCurrent();
            $table->timestamps();

            // Index for faster autocomplete matching
            $table->index(['domain', 'query']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('search_analytics');
    }
};
