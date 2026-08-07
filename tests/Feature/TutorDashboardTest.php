<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\TutorProfile;
use App\Models\PlatformSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TutorDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $tutorUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed platform setting default
        PlatformSetting::setValue('tutor_commission_rate', 0.15);

        // Create user with superuser privilege to bypass EnsureSubscribed middleware in test
        $this->tutorUser = User::factory()->create([
            'email' => 'tutor@digilearn.test',
            'credit_balance' => 100,
            'is_superuser' => true,
        ]);

        // Create approved tutor profile
        TutorProfile::create([
            'user_id' => $this->tutorUser->id,
            'bio' => 'Experienced math tutor.',
            'headline' => 'Math Expert',
            'hourly_rate' => 50,
            'is_approved' => true,
            'use_external_scheduling' => false,
            'timezone' => 'Africa/Accra',
        ]);
    }

    public function test_tutor_can_access_dashboard()
    {
        $response = $this->actingAs($this->tutorUser)
            ->get(route('tutors.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Tutor Overview');
    }

    public function test_tutor_can_access_earnings_page()
    {
        $response = $this->actingAs($this->tutorUser)
            ->get(route('tutors.earnings.index'));

        $response->assertStatus(200);
        $response->assertSee('Wallet');
    }

    public function test_tutor_can_access_content_studio()
    {
        $response = $this->actingAs($this->tutorUser)
            ->get(route('tutors.content.index'));

        $response->assertStatus(200);
        $response->assertSee('Content Studio');
    }

    public function test_tutor_can_access_schedule_availability()
    {
        $response = $this->actingAs($this->tutorUser)
            ->get(route('tutors.schedule.availability'));

        $response->assertStatus(200);
        $response->assertSee('Availability');
    }

    public function test_tutor_can_access_analytics()
    {
        $response = $this->actingAs($this->tutorUser)
            ->get(route('tutors.analytics.index'));

        $response->assertStatus(200);
        $response->assertSee('Performance Analytics');
    }

    public function test_tutor_can_access_profile_settings()
    {
        $response = $this->actingAs($this->tutorUser)
            ->get(route('tutors.profile.settings'));

        $response->assertStatus(200);
        $response->assertSee('Profile Settings');
    }
}
