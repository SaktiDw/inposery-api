<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Password;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthenticationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_can_register()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'User',
            'email' => 'user@gmail.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);
        $response->assertStatus(200);
    }
    public function test_users_can_not_register_with_wrong_password_confirmation()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'User',
            'email' => 'user@gmail.com',
            'password' => 'password',
            'password_confirmation' => 'not match password',
        ]);
        $response->assertStatus(422);
    }
    public function test_users_can_not_register_if_email_already_exist()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'User',
            'email' => 'user@gmail.com',
            'password' => 'password',
            'password_confirmation' => 'not match password',
        ]);
        $response->assertStatus(422);
    }

    public function test_users_can_login()
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        $response->assertStatus(200);
    }

    public function test_users_can_not_login_with_invalid_password()
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);
        $response->assertStatus(422);
    }
    public function test_users_can_logout()
    {
        $user = User::factory()->create();
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);
        $response = $this->postJson('/api/logout');

        $response->assertStatus(200);
    }

    public function test_user_can_request_email_verification_link()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);
        Sanctum::actingAs($user);
        $response = $this->postJson('/api/resend-email-verification-link');
        $response->assertStatus(200);
    }
    public function test_user_can_verify_email()
    {
        $notification = new VerifyEmail();
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);
        // New user should not has verified their email yet
        $this->assertFalse($user->hasVerifiedEmail());
        Sanctum::actingAs($user);

        $mail = $notification->toMail($user);
        $uri = $mail->actionUrl;
        $this
            ->get($uri);

        // User should have verified their email
        $this->assertTrue(User::find($user->id)->hasVerifiedEmail());
    }
    public function test_user_can_request_reset_password_link()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $response = $this->postJson('/api/forgot-password', ['email' => $user->email]);
        $response->assertStatus(200);
    }
    public function test_user_can_reset_password()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $token = Password::createToken($user);
        $response = $this->postJson('/api/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertStatus(200);
    }
    public function test_user_can_not_reset_password_with_wrong_password_confirmation()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $token = Password::createToken($user);
        $response = $this->postJson('/api/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'password',
            'password_confirmation' => 'not match password',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertStatus(422);
    }
}
