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
        Schema::create('lesson_difficulties', function (Blueprint $table) {
            $table->id();
            $table->string('grade_level')->unique();
            $table->integer('difficulty_score');
            $table->string('stage'); // foundation, intermediate, advanced, higher_education
            $table->integer('sequence_in_stage');
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['difficulty_score', 'stage']);
            $table->index('stage');
        });
        
        // Seed with complete progression data
        \Illuminate\Support\Facades\DB::table('lesson_difficulties')->insert([
            // Primary School (Grades 1-6) - Foundation Level
            ['grade_level' => 'Primary 1', 'difficulty_score' => 1, 'stage' => 'foundation', 'sequence_in_stage' => 1],
            ['grade_level' => 'Primary 2', 'difficulty_score' => 2, 'stage' => 'foundation', 'sequence_in_stage' => 2],
            ['grade_level' => 'Primary 3', 'difficulty_score' => 3, 'stage' => 'foundation', 'sequence_in_stage' => 3],
            ['grade_level' => 'Primary 4', 'difficulty_score' => 4, 'stage' => 'foundation', 'sequence_in_stage' => 4],
            ['grade_level' => 'Primary 5', 'difficulty_score' => 5, 'stage' => 'foundation', 'sequence_in_stage' => 5],
            ['grade_level' => 'Primary 6', 'difficulty_score' => 6, 'stage' => 'foundation', 'sequence_in_stage' => 6],
            
            // Junior High School (Grades 7-9) - Intermediate Level  
            ['grade_level' => 'JHS 1', 'difficulty_score' => 7, 'stage' => 'intermediate', 'sequence_in_stage' => 1],
            ['grade_level' => 'JHS 2', 'difficulty_score' => 8, 'stage' => 'intermediate', 'sequence_in_stage' => 2],
            ['grade_level' => 'JHS 3', 'difficulty_score' => 9, 'stage' => 'intermediate', 'sequence_in_stage' => 3],
            
            // Senior High School (Grades 10-12) - Advanced Level
            ['grade_level' => 'SHS 1', 'difficulty_score' => 10, 'stage' => 'advanced', 'sequence_in_stage' => 1],
            ['grade_level' => 'SHS 2', 'difficulty_score' => 11, 'stage' => 'advanced', 'sequence_in_stage' => 2],
            ['grade_level' => 'SHS 3', 'difficulty_score' => 12, 'stage' => 'advanced', 'sequence_in_stage' => 3],
            
            // University - Higher Education Level
            ['grade_level' => 'University', 'difficulty_score' => 13, 'stage' => 'higher_education', 'sequence_in_stage' => 1],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lesson_difficulties');
    }
};