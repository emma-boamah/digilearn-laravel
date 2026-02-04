<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use App\Mail\OtpVerificationMail;
use App\Services\EmailVerificationService;
use Tests\TestCase;
use Mockery;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\RateLimiter;

class OtpFallbackTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('signup:ip:127.0.0.1');
    }

    public function test_registration_falls_back_to_otp_on_service_error()
    {
        Mail::fake();

        // Mock EmailVerificationService to return service_error
        $mockService = Mockery::mock(EmailVerificationService::class);
        $mockService->shouldReceive('verify')->andReturn([
            'valid' => false,
            'message' => 'Service unavailable',
            'service_error' => true
        ]);
        
        // Bind the mock
        $this->app->instance(EmailVerificationService::class, $mockService);

        $password = Str::random(20) . 'Aa1!';
        $email = strtolower('test.otp.' . Str::random(5) . '@gmail.com');

        $userData = [
            'name' => 'Otp User',
            'email' => $email,
            'country' => 'Ghana',
            'password' => $password,
            'password_confirmation' => $password,
            'terms' => true // assuming there might be terms
        ];

        // 1. Attempt Registration
        $this->withoutExceptionHandling();
        $response = $this->post('/signup', $userData);

        // 2. Assert Redirect to Verify OTP
        $response->assertRedirect(route('verify-otp'));
        $response->assertSessionHas('otp_email', $email);
        
        // 3. Assert OTP Email Sent
        Mail::assertSent(OtpVerificationMail::class, function ($mail) use ($userData) {
            return $mail->hasTo($userData['email']);
        });

        // 4. Get OTP from session
        $checkSession = session('registration_otp');
        $this->assertNotNull($checkSession);
        $otp = $checkSession['code'];

        // 5. Verify OTP
        $verifyResponse = $this->post('/verify-otp', ['otp' => $otp]);

        // 6. Assert User Created and Redirected
        $this->assertDatabaseHas('users', ['email' => $email]);
        $verifyResponse->assertRedirect(route('dashboard.level-selection'));
    }
    
    public function test_registration_fails_normally_on_invalid_email_no_error()
    {
        // Mock Service to return just invalid (no service error)
        $mockService = Mockery::mock(EmailVerificationService::class);
        $mockService->shouldReceive('verify')->andReturn([
            'valid' => false,
            'message' => 'Invalid email',
            // service_error is missing or false
        ]);
        
        $this->app->instance(EmailVerificationService::class, $mockService);

        $password = Str::random(20) . 'Aa1!';
        $email = strtolower('test.invalid.' . Str::random(5) . '@gmail.com');

         $userData = [
            'name' => 'Invalid User',
            'email' => $email,
            'country' => 'Ghana',
            'password' => $password,
            'password_confirmation' => $password,
        ];

        $response = $this->post('/signup', $userData);
        
        // Should redirect back with errors, NOT to OTP
        $response->assertSessionHasErrors('auth_error');
        $response->assertRedirect();
        $this->assertFalse(session()->has('registration_otp'));
    }
}
