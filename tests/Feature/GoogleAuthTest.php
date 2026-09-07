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
}
