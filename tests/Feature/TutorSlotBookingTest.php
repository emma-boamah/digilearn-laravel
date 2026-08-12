<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Subject;
use App\Models\TutorAvailability;
use App\Models\TutorProfile;
use App\Models\TutorSubject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TutorSlotBookingTest extends TestCase
{
    use RefreshDatabase;

    protected User $tutor;
    protected User $student;
    protected Subject $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tutor = User::factory()->create([
            'credit_balance' => 0.00,
            'is_superuser' => true,
        ]);

        TutorProfile::create([
            'user_id' => $this->tutor->id,
            'bio' => 'Sample bio',
            'qualifications' => 'B.Sc. Math',
            'is_approved' => true,
            'scheduling_preference' => 'in_app',
        ]);

        $this->subject = Subject::create([
            'name' => 'Mathematics',
            'slug' => 'mathematics',
        ]);

        TutorSubject::create([
            'user_id' => $this->tutor->id,
            'subject_id' => $this->subject->id,
            'hourly_rate' => 50.00,
        ]);

        // Add recurring availability for Mondays (day_of_week = 1) 09:00 to 17:00
        TutorAvailability::create([
            'tutor_id' => $this->tutor->id,
            'day_of_week' => 1,
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'is_recurring' => true,
            'slot_duration_minutes' => 60,
        ]);

        $this->student = User::factory()->create([
            'credit_balance' => 500.00,
            'is_superuser' => true,
        ]);
    }

    /** @test */
    public function api_slots_endpoint_returns_available_slots_for_tutor()
    {
        // Find next Monday
        $nextMonday = now()->next(\Carbon\Carbon::MONDAY)->toDateString();

        $response = $this->actingAs($this->student)
            ->getJson(route('tutors.schedule.api.slots', [
                'tutorId' => $this->tutor->id,
                'date' => $nextMonday,
                'duration_hours' => 1,
            ]));

        $response->assertStatus(200)
            ->assertJson([
                'date' => $nextMonday,
                'day_name' => 'Monday',
                'is_available' => true,
            ]);

        $slots = $response->json('slots');
        $this->assertNotEmpty($slots);
        $this->assertEquals('09:00', $slots[0]['time']);
    }

    /** @test */
    public function booked_slots_are_marked_unavailable_in_api_slots()
    {
        $nextMonday = now()->next(\Carbon\Carbon::MONDAY);
        $slotStart = $nextMonday->copy()->setTime(10, 0, 0);
        $slotEnd = $slotStart->copy()->addHour();

        // Create an existing booking at 10:00 AM on next Monday
        Booking::create([
            'student_id' => $this->student->id,
            'tutor_id' => $this->tutor->id,
            'subject_id' => $this->subject->id,
            'credits_paid' => 50.00,
            'commission_amount' => 7.50,
            'start_time' => $slotStart,
            'end_time' => $slotEnd,
            'status' => 'scheduled',
            'meeting_link' => 'https://example.com/room',
        ]);

        $response = $this->actingAs($this->student)
            ->getJson(route('tutors.schedule.api.slots', [
                'tutorId' => $this->tutor->id,
                'date' => $nextMonday->toDateString(),
                'duration_hours' => 1,
            ]));

        $response->assertStatus(200);

        $slots = collect($response->json('slots'));
        $slot10Am = $slots->firstWhere('time', '10:00');

        $this->assertNotNull($slot10Am);
        $this->assertFalse($slot10Am['available']);
        $this->assertTrue($slot10Am['is_booked']);
    }

    /** @test */
    public function student_can_checkout_with_date_and_time_slot()
    {
        $nextMonday = now()->next(\Carbon\Carbon::MONDAY)->toDateString();

        $response = $this->actingAs($this->student)
            ->post(route('bookings.checkout'), [
                'tutor_id' => $this->tutor->id,
                'subject_id' => $this->subject->id,
                'duration_hours' => 1,
                'booking_date' => $nextMonday,
                'start_time' => '11:00',
            ]);

        $response->assertRedirect(route('dashboard.main'));

        $this->assertDatabaseHas('bookings', [
            'student_id' => $this->student->id,
            'tutor_id' => $this->tutor->id,
            'subject_id' => $this->subject->id,
            'status' => 'scheduled',
        ]);

        // 50 GHS subtotal - 10% discount for advanced level/superuser = 45 GHS total -> 500 - 45 = 455.00
        $this->assertEquals(455.00, $this->student->fresh()->credit_balance);
    }
}
