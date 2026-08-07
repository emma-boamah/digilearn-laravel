<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tutor_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->decimal('hourly_rate', 10, 2)->default(0.00);
            $table->timestamps();
            
            $table->unique(['user_id', 'subject_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('tutor_subjects');
    }
};
