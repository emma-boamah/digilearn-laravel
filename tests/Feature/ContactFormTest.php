<?php

namespace Tests\Feature;

use App\Models\User;
use App\Mail\ContactFormMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_users_are_redirected_to_login(): void
    {
        $response = $this->post('/contact/submit', [
            'message' => 'Hello',
        ]);

        $response->assertRedirect('/login');
    }

    public function test_authenticated_users_can_submit_contact_form(): void
    {
        Mail::fake();
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $response = $this->actingAs($user)->post('/contact/submit', [
            'message' => 'Hello',
        ]);

        $response->assertSessionHas('success');
        Mail::assertSent(ContactFormMail::class, function ($mail) use ($user) {
            return $mail->data['firstName'] === 'John' &&
                   $mail->data['lastName'] === 'Doe' &&
                   $mail->data['email'] === 'john@example.com';
        });
    }

    public function test_contact_form_is_rate_limited(): void
    {
        $user = User::factory()->create();
        
        // Use up the 5 attempts
        for ($i = 0; $i < 5; $i++) {
            $this->actingAs($user)->post('/contact/submit', [
                'message' => 'Hello',
            ]);
        }

        // 6th attempt should fail
        $response = $this->actingAs($user)->post('/contact/submit', [
            'message' => 'Hello',
        ]);

        $response->assertStatus(429);
    }
}
