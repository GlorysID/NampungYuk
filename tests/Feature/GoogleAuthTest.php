<?php

namespace Tests\Feature;

use App\Models\User;
use Firebase\JWT\JWT;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
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

        $response = $this->get('/auth/google/callback?code=mock-oauth-code');

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

        $response = $this->get('/auth/google/callback?code=mock-oauth-code');

        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($existingUser);

        // Verify no duplicate user was created
        $this->assertEquals(1, User::where('email', 'manual.same@example.com')->count());

        $existingUser->refresh();
        $this->assertEquals('google-id-linked-555', $existingUser->google_id);
        $this->assertNotNull($existingUser->email_verified_at);
    }

    public function test_google_callback_falls_back_to_stateless_when_session_state_is_lost(): void
    {
        Config::set('services.google.client_id', 'mock-google-client-id');
        Config::set('services.google.client_secret', 'mock-google-client-secret');

        $mockGoogleUser = Mockery::mock(SocialiteUser::class);
        $mockGoogleUser->shouldReceive('getId')->andReturn('google-stateless-123');
        $mockGoogleUser->shouldReceive('getEmail')->andReturn('stateless@example.com');
        $mockGoogleUser->shouldReceive('getName')->andReturn('Stateless User');
        $mockGoogleUser->shouldReceive('getAvatar')->andReturn(null);

        $provider = Mockery::mock();
        // State verification fails (InvalidStateException)
        $provider->shouldReceive('user')
            ->once()
            ->andThrow(new InvalidStateException);
        // Fallback to stateless succeeds
        $provider->shouldReceive('stateless')->once()->andReturnSelf();
        $provider->shouldReceive('user')->once()->andReturn($mockGoogleUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->get('/auth/google/callback?code=mock-oauth-code');

        $response->assertRedirect('/');
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'stateless@example.com',
            'google_id' => 'google-stateless-123',
        ]);
    }

    public function test_google_callback_handles_access_denied_error_without_calling_token_endpoint(): void
    {
        Config::set('services.google.client_id', 'mock-google-client-id');
        Config::set('services.google.client_secret', 'mock-google-client-secret');

        Socialite::shouldReceive('driver')->never();

        $response = $this->get('/auth/google/callback?error=access_denied');

        $response->assertRedirect('/login');
        $response->assertSessionHas('status');
        $this->assertGuest();
    }

    public function test_google_callback_handles_missing_code_without_calling_token_endpoint(): void
    {
        Config::set('services.google.client_id', 'mock-google-client-id');
        Config::set('services.google.client_secret', 'mock-google-client-secret');

        Socialite::shouldReceive('driver')->never();

        $response = $this->get('/auth/google/callback');

        $response->assertRedirect('/login');
        $response->assertSessionHas('status');
        $this->assertGuest();
    }

    public function test_firebase_google_login_verifies_id_token_and_creates_user(): void
    {
        Cache::flush();
        Config::set('services.firebase.project_id', 'nampungyuk-test');

        [$privateKey, $jwks] = $this->createMockFirebaseKeySet('kid-test-1');
        Http::fake([
            'https://www.googleapis.com/service_accounts/v1/jwk/*' => Http::response($jwks, 200, [
                'Cache-Control' => 'public, max-age=3600',
            ]),
        ]);

        $token = $this->signMockFirebaseToken($privateKey, 'kid-test-1', [
            'iss' => 'https://securetoken.google.com/nampungyuk-test',
            'aud' => 'nampungyuk-test',
            'sub' => 'firebase-uid-123',
            'email' => 'firebase.dev@example.com',
            'email_verified' => true,
            'name' => 'Firebase Dev',
            'picture' => 'https://lh3.googleusercontent.com/fb.jpg',
            'firebase' => [
                'identities' => [
                    'google.com' => ['google-sub-999'],
                ],
                'sign_in_provider' => 'google.com',
            ],
        ]);

        $response = $this->postJson(route('auth.firebase.google'), [
            'id_token' => $token,
        ]);

        $response->assertOk()
            ->assertJsonStructure(['redirect', 'message'])
            ->assertSessionHas('status');

        $this->assertAuthenticated();
        $user = User::where('email', 'firebase.dev@example.com')->first();
        $this->assertNotNull($user);
        $this->assertSame('google-sub-999', $user->google_id);
        $this->assertSame('Firebase Dev', $user->name);
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_firebase_google_login_links_existing_user_by_email(): void
    {
        Cache::flush();
        Config::set('services.firebase.project_id', 'nampungyuk-test');

        $existingUser = User::factory()->create([
            'email' => 'existing.firebase@example.com',
            'email_verified_at' => null,
            'google_id' => null,
        ]);

        [$privateKey, $jwks] = $this->createMockFirebaseKeySet('kid-test-2');
        Http::fake([
            'https://www.googleapis.com/service_accounts/v1/jwk/*' => Http::response($jwks, 200),
        ]);

        $token = $this->signMockFirebaseToken($privateKey, 'kid-test-2', [
            'iss' => 'https://securetoken.google.com/nampungyuk-test',
            'aud' => 'nampungyuk-test',
            'sub' => 'firebase-uid-existing',
            'email' => 'existing.firebase@example.com',
            'email_verified' => true,
            'name' => 'Existing User',
        ]);

        $response = $this->postJson(route('auth.firebase.google'), [
            'id_token' => $token,
        ]);

        $response->assertOk();
        $this->assertAuthenticatedAs($existingUser);
        $this->assertSame(1, User::where('email', 'existing.firebase@example.com')->count());

        $existingUser->refresh();
        $this->assertSame('firebase-uid-existing', $existingUser->google_id);
        $this->assertNotNull($existingUser->email_verified_at);
    }

    public function test_firebase_google_login_rejects_wrong_audience_and_unverified_email(): void
    {
        Cache::flush();
        Config::set('services.firebase.project_id', 'nampungyuk-test');

        [$privateKey, $jwks] = $this->createMockFirebaseKeySet('kid-test-3');
        Http::fake([
            'https://www.googleapis.com/service_accounts/v1/jwk/*' => Http::response($jwks, 200),
        ]);

        $wrongAudienceToken = $this->signMockFirebaseToken($privateKey, 'kid-test-3', [
            'iss' => 'https://securetoken.google.com/nampungyuk-test',
            'aud' => 'wrong-project',
            'sub' => 'firebase-uid-bad-aud',
            'email' => 'badaud@example.com',
            'email_verified' => true,
        ]);

        $this->postJson(route('auth.firebase.google'), ['id_token' => $wrongAudienceToken])
            ->assertStatus(401);
        $this->assertGuest();

        $unverifiedToken = $this->signMockFirebaseToken($privateKey, 'kid-test-3', [
            'iss' => 'https://securetoken.google.com/nampungyuk-test',
            'aud' => 'nampungyuk-test',
            'sub' => 'firebase-uid-unverified',
            'email' => 'unverified@example.com',
            'email_verified' => false,
        ]);

        $this->postJson(route('auth.firebase.google'), ['id_token' => $unverifiedToken])
            ->assertStatus(401);
        $this->assertGuest();
    }

    public function test_login_page_renders_firebase_google_button_when_configured(): void
    {
        Config::set('services.firebase', [
            'api_key' => 'mock-api-key',
            'auth_domain' => 'nampungyuk-test.firebaseapp.com',
            'project_id' => 'nampungyuk-test',
        ]);

        $this->get('/login')
            ->assertOk()
            ->assertSee('firebaseGoogleAuth', false)
            ->assertSee('nampungyuk-test.firebaseapp.com', false)
            ->assertSee('name="csrf-token"', false);
    }

    /**
     * Provide a deterministic test-only RSA-2048 keypair and JWK set for testing RS256 ID tokens.
     *
     * @return array{0: string, 1: array<string, mixed>}
     */
    private function createMockFirebaseKeySet(string $kid): array
    {
        $privateKeyPem = <<<'PEM'
-----BEGIN RSA PRIVATE KEY-----
MIIEogIBAAKCAQEAun642Roc4gm0O0Osq6ki4CGYusFd2HOHuhWxmp+nWRBt/UWB
KO2Ch7+hTfVI1I/BA7CunuEiNyJX23ZUSVKHPQ1b7ib0pX4DqrXzmeW6xfY8ccHI
VN7oG23K6t/KU7XBt7mf2g1WNN8pwthX3zWbQmqUenOhaFNpPWJW+YD0ZVWfXuA/
HGulIY/6TWcvMfR7EwV/ge5B7rO32DwrsMpK+XQDGUn8uA60tnBu7c5zvC/FVuj6
QyphyGDitN/imDkgY62RzqnfnOotoXIIP4u+JBuHBoNM+mS2NY9p8swKk0sAEe+l
gCsvndQr4UxDozb7k1kpDV2M4sO3YwL27RzQ+wIDAQABAoIBAAOOmzP43YgqKzfe
KJL1nOatfV5Chem0+mfANiz+0pBOUeq8wqPnM9AnBYqZHRSA0WuimRIKtT5b91UG
QsFLQ60UbiZys8PcTXGaGtVanl9cPPbmfU2xGrg7Nd2Y+yDxnIpGjhf4nSZRA1jm
C3cEbLutkLttLSzyOlWjZIlJvl+YZBIqFoptUgxR8DaHwEMfngx8guCWUrJZLo4U
BXZGPWwEo+6zpX+4eQdg2ZN6SubvVurNIIISb+ju0gCEv0j6g0Kpl49A7EYJdS9+
UDBYqeO00D0fx+/lqeKo1UUg1kF1zxEiKoP5uE7CYQKk+uK+Wzug0CquOYqtsvjP
AgpL+JECgYEA+mH0jiUc+K6YZ7ykIT6jxMW6QnxQIb+IMPj4EWcRaSnjiE1upr96
lFuXy93WNFh0GPGrSnvkq7SPB4bWfZhLwp3O1ko8qK0ffg/CKFZF7BFmsLMZ8e5G
rlxgGuiCD5quVVkbXBQkaE9lhm0CaRDIl2XE4nTMs6bYDpwqovWLx40CgYEAvq3V
1osziu98/jG9+hxMyR9N04ef6SkNCAw0OPKJ1xu8ugJreVOD/54LpDldwCmFOhaW
thYhq46M2hYgX1JMWDUbTszTtiAt99+dGR7BeLRavIinzp7V/FA7M5rbGtvtQAhy
37dYOyABvWk7NFS8oTYr3qlHfBHjIRhcC5w5NKcCgYBcd46EbEpdwMUCEpWevW5n
vgoCgbxqKjzWsSuT6bdxWgnwjYAezrorTgQqQvvXiw8dIyr6L/UMQHtN8UsHSRY5
gmQfmXz7YiwuNw2iLfJWszp0aUBcq32o6O6JIXLe5mYZmPXUtI+ZgjDhSyLHwG4E
E455f18Vvbku9Bd+5SXBBQKBgAfs6cp1FrtboPJf1umia36gkneMV5U1Y5lGCJWl
Xx1Vxc5lc1ojooTpaMp2p/LoxuTXM4R1/whGcIxQByBvJ0UzT+NCmDoltENtQ/Zb
VOdTd8IGXBM2fuZx30QR17GSGpZiw4OQ91XdyrS4vwE51G1O9xvWJWZ+x9YEUS8a
WzZZAoGAQnSHNm87ZXY9tCgiDRolhJKN8a5nPWY6IKqQtFhQTTZu3VOCx48PT+LZ
+SaskUMgy8tvBg0D7zX6ATIENWy8n0jDA8umrjTgwbd4x59x7YL/7bI7+x5950MR
lwwgru1nRqlJfdnq4ikHGlqQ8pWGwR0liBqZfo6txxZOxxhWVbs=
-----END RSA PRIVATE KEY-----
PEM;

        $jwks = [
            'keys' => [
                [
                    'kty' => 'RSA',
                    'alg' => 'RS256',
                    'use' => 'sig',
                    'kid' => $kid,
                    'n' => 'un642Roc4gm0O0Osq6ki4CGYusFd2HOHuhWxmp-nWRBt_UWBKO2Ch7-hTfVI1I_BA7CunuEiNyJX23ZUSVKHPQ1b7ib0pX4DqrXzmeW6xfY8ccHIVN7oG23K6t_KU7XBt7mf2g1WNN8pwthX3zWbQmqUenOhaFNpPWJW-YD0ZVWfXuA_HGulIY_6TWcvMfR7EwV_ge5B7rO32DwrsMpK-XQDGUn8uA60tnBu7c5zvC_FVuj6QyphyGDitN_imDkgY62RzqnfnOotoXIIP4u-JBuHBoNM-mS2NY9p8swKk0sAEe-lgCsvndQr4UxDozb7k1kpDV2M4sO3YwL27RzQ-w',
                    'e' => 'AQAB',
                ],
            ],
        ];

        return [$privateKeyPem, $jwks];
    }

    /**
     * Sign a mock Firebase ID token with custom claims.
     *
     * @param  array<string, mixed>  $overrides
     */
    private function signMockFirebaseToken(string $privateKeyPem, string $kid, array $overrides): string
    {
        $now = now()->timestamp;
        $payload = array_merge([
            'iat' => $now - 10,
            'auth_time' => $now - 10,
            'exp' => $now + 3600,
        ], $overrides);

        return JWT::encode($payload, $privateKeyPem, 'RS256', $kid);
    }
}
