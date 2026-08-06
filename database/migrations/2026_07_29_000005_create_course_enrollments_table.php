<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('course_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->timestamp('enrolled_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();
            $table->integer('progress_percentage')->default(0);
            $table->tinyInteger('rating')->nullable();
            $table->text('review')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'course_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('course_enrollments');
    }
};
