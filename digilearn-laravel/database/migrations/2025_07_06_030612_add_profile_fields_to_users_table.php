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
        Schema::table('users', function (Blueprint $table) {
            // Profile image
            $table->string('avatar')->nullable()->after('country');
            
            // Contact information
            $table->string('phone', 20)->nullable()->after('avatar');
            $table->date('date_of_birth')->nullable()->after('phone');
            $table->string('city')->nullable()->after('date_of_birth');
            
            // Education information
            $table->enum('education_level', ['primary', 'jhs', 'shs', 'university'])->nullable()->after('city');
            $table->string('grade', 10)->nullable()->after('education_level');
            
            // Preferences
            $table->string('preferred_language', 5)->default('en')->after('grade');
            $table->enum('learning_style', ['visual', 'auditory', 'kinesthetic', 'mixed'])->nullable()->after('preferred_language');
            $table->text('bio')->nullable()->after('learning_style');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'avatar',
                'phone',
                'date_of_birth',
                'city',
                'education_level',
                'grade',
                'preferred_language',
                'learning_style',
                'bio'
            ]);
        });
    }
};
