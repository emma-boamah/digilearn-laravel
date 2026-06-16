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
        Schema::create('agent_chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_chat_session_id')->constrained()->onDelete('cascade');
            $table->string('role'); // 'user' or 'model'
            $table->text('text');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_chat_messages');
    }
};
