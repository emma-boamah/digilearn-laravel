<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\TutorProfile;
use App\Models\PlatformSetting;
use App\Notifications\TutorApplicationSubmittedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AdminTutorVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected TutorProfile $tutorProfile;

    protected function setUp(): void
    {
        parent::setUp();

        // Create super admin user
        $this->superAdmin = User::factory()->create([
            'email' => 'superadmin@digilearn.test',
            'is_superuser' => true,
            'is_admin' => true,
        ]);

        // Create applicant user with pending tutor profile
        $applicant = User::factory()->create([
            'email' => 'applicant@digilearn.test',
        ]);

        $this->tutorProfile = TutorProfile::create([
            'user_id' => $applicant->id,
            'bio' => 'Physics tutor applicant',
            'hourly_rate' => 60,
            'is_approved' => false,
        ]);
    }

    public function test_super_admin_can_access_tutor_applications_list()
    {
        $response = $this->withoutMiddleware()
            ->actingAs($this->superAdmin)
            ->get(route('admin.tutors.index'));

        $response->assertStatus(200);
        $response->assertSee('Tutor Applications');
    }

    public function test_super_admin_can_inspect_tutor_application()
    {
        $response = $this->withoutMiddleware()
            ->actingAs($this->superAdmin)
            ->get(route('admin.tutors.show', $this->tutorProfile->id));

        $response->assertStatus(200);
        $response->assertSee('Verify Tutor:');
    }

    public function test_super_admin_can_approve_tutor_application()
    {
        $response = $this->withoutMiddleware()
            ->actingAs($this->superAdmin)
            ->post(route('admin.tutors.approve', $this->tutorProfile->id));

        $response->assertRedirect(route('admin.tutors.index'));

        $this->assertDatabaseHas('tutor_profiles', [
            'id' => $this->tutorProfile->id,
            'is_approved' => true,
        ]);
    }

    public function test_super_admin_can_update_platform_settings()
    {
        $response = $this->withoutMiddleware()
            ->actingAs($this->superAdmin)
            ->post(route('admin.platform-settings.update'), [
                'tutor_commission_rate' => 20,
                'min_payout_amount' => 100,
                'payout_processing_days' => 5,
            ]);

        $response->assertRedirect();
        
        $this->assertEquals(0.20, PlatformSetting::getValue('tutor_commission_rate'));
        $this->assertEquals(100.00, PlatformSetting::getValue('min_payout_amount'));
    }

    public function test_tutor_application_dispatches_notification_to_admins()
    {
        Notification::fake();

        $notification = new TutorApplicationSubmittedNotification($this->tutorProfile);

        $this->superAdmin->notify($notification);

        Notification::assertSentTo(
            [$this->superAdmin],
            TutorApplicationSubmittedNotification::class
        );
    }
}
