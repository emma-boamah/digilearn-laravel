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
        Schema::create('report_card_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_card_id')->constrained('report_cards')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->decimal('class_score', 8, 2)->default(0);
            $table->decimal('exam_score', 8, 2)->default(0);
            $table->decimal('total_score', 8, 2)->default(0);
            $table->string('grade', 5)->nullable();
            $table->text('remarks')->nullable();
            $table->integer('position_in_subject')->nullable();
            $table->timestamps();
            
            $table->unique(['report_card_id', 'subject_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_card_details');
    }
};
