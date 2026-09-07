<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Credentials payload for the login form.
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function loginData(array $overrides = []): array
    {
        return array_merge([
            'login' => 'budi@example.com',
            'password' => 'password123',
        ], $overrides);
    }

    /**
     * Create a user with well-known credentials.
     */
    private function createUser(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'name' => 'Budi Santoso',
            'username' => 'budi_santoso',
            'email' => 'budi@example.com',
            'password' => 'password123',
        ], $overrides));
    }

    public function test_login_page_can_be_rendered(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
        $response->assertSee('Masuk');
    }

    public function test_user_can_login_via_email(): void
    {
        $user = $this->createUser();

        $response = $this->post(route('login.store'), $this->loginData());

        $response->assertRedirect(url('/'));
        $response->assertSessionHas('status', 'Selamat datang kembali, '.$user->name.'!');

        $this->assertAuthenticatedAs($user);
    }

    public function test_user_can_login_via_username(): void
    {
        $user = $this->createUser();

        $response = $this->post(route('login.store'), $this->loginData([
            'login' => 'budi_santoso',
        ]));

        $response->assertRedirect(url('/'));
        $response->assertSessionHas('status', 'Selamat datang kembali, '.$user->name.'!');

        $this->assertAuthenticatedAs($user);
    }

    public function test_wrong_password_is_rejected(): void
    {
        $this->createUser();

        $response = $this->from(route('login'))->post(route('login.store'), $this->loginData([
            'password' => 'wrong-password',
        ]));

        $response->assertSessionHasErrors('login');
        $response->assertSessionHasInput('login', 'budi@example.com');

        $this->assertGuest();
    }

    public function test_nonexistent_identifier_is_rejected(): void
    {
        $response = $this->from(route('login'))->post(route('login.store'), $this->loginData([
            'login' => 'nobody@example.com',
        ]));

        $response->assertSessionHasErrors('login');

        $this->assertGuest();
    }

    public function test_remember_option_persists_remember_token(): void
    {
        $user = $this->createUser(['remember_token' => null]);

        $response = $this->post(route('login.store'), $this->loginData([
            'remember' => '1',
        ]));

        $response->assertRedirect(url('/'));

        $this->assertAuthenticatedAs($user);

        $this->assertNotNull($user->fresh()->remember_token);
        $this->assertTrue(
            strlen((string) $user->fresh()->remember_token) >= 60,
            'Expected a fresh recaller cookie token (>= 60 chars) to be persisted.'
        );
    }

    public function test_login_is_throttled_after_five_failed_attempts(): void
    {
        $this->createUser();

        foreach (range(1, 5) as $attempt) {
            $this->from(route('login'))->post(route('login.store'), $this->loginData([
                'password' => 'wrong-password',
            ]));
        }

        $response = $this->from(route('login'))->post(route('login.store'), $this->loginData([
            'password' => 'password123',
        ]));

        $response->assertSessionHasErrors('login');

        $this->assertMatchesRegularExpression(
            '/^Terlalu banyak percobaan login\. Coba lagi dalam \d+ detik\.$/',
            session('errors')->first('login')
        );

        $this->assertGuest();
    }

    public function test_logout_destroys_session_and_guest_afterward(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect(url('/'));
        $response->assertSessionHas('status', 'Anda telah keluar.');

        $this->assertGuest();
    }

    public function test_guest_middleware_redirects_authenticated_user_away_from_login_page(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->get(route('login'));

        $response->assertRedirect(url('/'));
    }

    public function test_guest_middleware_redirects_authenticated_user_away_from_register_page(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->get(route('register'));

        $response->assertRedirect(url('/'));
    }

    public function test_auth_middleware_redirects_guest_away_from_logout(): void
    {
        $response = $this->post(route('logout'));

        $response->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
