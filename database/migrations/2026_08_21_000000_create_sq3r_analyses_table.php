<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sq3r_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->nullable()->constrained('documents')->onDelete('cascade');
            $table->string('file_path')->nullable();
            $table->string('status')->default('pending'); // pending, processing, completed, failed
            $table->longText('structural_map')->nullable();   // Survey Output
            $table->longText('question_list')->nullable();    // Question Output
            $table->longText('content_notes')->nullable();    // Read Output
            $table->longText('simple_summary')->nullable();   // Recite Output
            $table->longText('final_guide')->nullable();      // Review Output
            $table->json('structured_payload')->nullable();   // Direct JSON payload for Acquisition & Application UI
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sq3r_analyses');
    }
};
