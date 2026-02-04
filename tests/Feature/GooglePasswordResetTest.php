<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use App\Mail\GoogleAccountInfoMail;
use App\Mail\ResetPasswordMail;
use Tests\TestCase;

class GooglePasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_google_user_receives_google_account_info_mail_instead_of_rest_link()
    {
        Mail::fake();

        // Create a user that has BOTH google_id and password
        // The bug was that because they had a password, the google check (which also checked for empty password) failed
        // and they got the standard reset link.
        $user = User::factory()->create([
            'name' => 'Test Google User',
            'email' => 'googleuser@example.com',
            'google_id' => '123456789',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/forgot-password', ['email' => $user->email]);

        // Assert it redirects back with status
        $response->assertSessionHas('status');

        // Assert GoogleAccountInfoMail was sent
        Mail::assertSent(GoogleAccountInfoMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });

        // Assert ResetPasswordMail was NOT sent
        Mail::assertNotSent(ResetPasswordMail::class);
    }

    public function test_normal_user_receives_reset_link()
    {
        Mail::fake();

        $user = User::factory()->create([
            'name' => 'Test Normal User',
            'email' => 'normaluser@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/forgot-password', ['email' => $user->email]);

        $response->assertSessionHas('status');

        Mail::assertSent(ResetPasswordMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });

        Mail::assertNotSent(GoogleAccountInfoMail::class);
    }
}
