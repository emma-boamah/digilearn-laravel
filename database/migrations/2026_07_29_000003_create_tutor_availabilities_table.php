<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tutor_availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tutor_id')->constrained('users')->onDelete('cascade');
            $table->tinyInteger('day_of_week')->nullable(); // 0 (Sunday) to 6 (Saturday)
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_recurring')->default(true);
            $table->date('specific_date')->nullable();
            $table->boolean('is_blocked')->default(false);
            $table->integer('slot_duration_minutes')->default(60);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tutor_availabilities');
    }
};
