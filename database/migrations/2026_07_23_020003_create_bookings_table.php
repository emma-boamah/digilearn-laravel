<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('tutor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            
            $table->decimal('credits_paid', 10, 2);
            $table->decimal('commission_amount', 10, 2)->default(0.00);
            
            $table->dateTime('start_time')->nullable();
            $table->dateTime('end_time')->nullable();
            
            $table->enum('status', ['pending_scheduling', 'scheduled', 'confirmed', 'completed', 'cancelled'])->default('pending_scheduling');
            
            $table->string('meeting_link')->nullable();
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bookings');
    }
};
