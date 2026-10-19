<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_google_redirect_warns_when_credentials_not_configured(): void
    {
        Config::set('services.google.client_id', null);
        Config::set('services.google.client_secret', null);

        $response = $this->get('/auth/google');

        $response->assertRedirect('/login');
        $response->assertSessionHas('status');
    }

    public function test_google_callback_creates_and_authenticates_user(): void
    {
        Config::set('services.google.client_id', 'mock-google-client-id');
        Config::set('services.google.client_secret', 'mock-google-client-secret');

        $mockGoogleUser = Mockery::mock(SocialiteUser::class);
        $mockGoogleUser->shouldReceive('getId')->andReturn('google-id-998877');
        $mockGoogleUser->shouldReceive('getEmail')->andReturn('tester.google@example.com');
        $mockGoogleUser->shouldReceive('getName')->andReturn('Google Tester');
        $mockGoogleUser->shouldReceive('getAvatar')->andReturn('https://lh3.googleusercontent.com/avatar.jpg');

        $provider = Mockery::mock();
        $provider->shouldReceive('user')->andReturn($mockGoogleUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->get('/auth/google/callback');

        $response->assertRedirect('/');
        $this->assertAuthenticated();

        $user = User::where('email', 'tester.google@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('google-id-998877', $user->google_id);
        $this->assertEquals('Google Tester', $user->name);
    }

    public function test_google_login_links_existing_user_with_same_email_and_marks_email_verified(): void
    {
        Config::set('services.google.client_id', 'mock-google-client-id');
        Config::set('services.google.client_secret', 'mock-google-client-secret');

        // User registered manually earlier, unverified and without google_id
        $existingUser = User::factory()->create([
            'name' => 'Manual User',
            'username' => 'manual_user',
            'email' => 'manual.same@example.com',
            'email_verified_at' => null,
            'google_id' => null,
        ]);

        $mockGoogleUser = Mockery::mock(SocialiteUser::class);
        $mockGoogleUser->shouldReceive('getId')->andReturn('google-id-linked-555');
        $mockGoogleUser->shouldReceive('getEmail')->andReturn('manual.same@example.com');
        $mockGoogleUser->shouldReceive('getName')->andReturn('Google Name');
        $mockGoogleUser->shouldReceive('getAvatar')->andReturn('https://lh3.googleusercontent.com/avatar.jpg');

        $provider = Mockery::mock();
        $provider->shouldReceive('user')->andReturn($mockGoogleUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->get('/auth/google/callback');

        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($existingUser);

        // Verify no duplicate user was created
        $this->assertEquals(1, User::where('email', 'manual.same@example.com')->count());

        $existingUser->refresh();
        $this->assertEquals('google-id-linked-555', $existingUser->google_id);
        $this->assertNotNull($existingUser->email_verified_at);
    }
}
