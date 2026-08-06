<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->text('tutor_notes')->nullable()->after('meeting_link');
            $table->unsignedTinyInteger('student_rating')->nullable()->after('tutor_notes');
            $table->text('student_feedback')->nullable()->after('student_rating');
            $table->text('decline_reason')->nullable()->after('student_feedback');
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete()->after('decline_reason');
            $table->text('cancellation_reason')->nullable()->after('cancelled_by');
        });
    }

    public function down()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['cancelled_by']);
            $table->dropColumn([
                'tutor_notes',
                'student_rating',
                'student_feedback',
                'decline_reason',
                'cancelled_by',
                'cancellation_reason',
            ]);
        });
    }
};
