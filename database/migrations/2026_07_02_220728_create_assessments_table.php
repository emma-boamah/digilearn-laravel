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
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->foreignId('class_subject_id')->constrained('class_subjects')->onDelete('cascade');
            $table->foreignId('academic_term_id')->constrained('academic_terms')->onDelete('cascade');
            $table->string('title'); // e.g. "Class Test 1"
            $table->enum('type', ['exercise', 'homework', 'project', 'exam', 'mid_term'])->default('exercise');
            $table->decimal('max_score', 8, 2)->default(100);
            $table->decimal('weight_percentage', 5, 2)->default(100); // How much it contributes to final grade (e.g. 30%)
            $table->date('date_administered')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessments');
    }
};
