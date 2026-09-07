<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Valid registration data payload.
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function registrationData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Budi Santoso',
            'username' => 'budi_santoso',
            'email' => 'budi@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ], $overrides);
    }

    public function test_registration_form_can_be_rendered(): void
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
        $response->assertViewIs('auth.register');
        $response->assertSee('Daftar Akun');
    }

    public function test_valid_registration_creates_user_and_redirects(): void
    {
        $response = $this->post(route('register.store'), $this->registrationData());

        $response->assertRedirect(route('register'));
        $response->assertSessionHas('status', 'Registrasi berhasil! Akun Anda telah dibuat.');

        $this->assertDatabaseHas('users', [
            'name' => 'Budi Santoso',
            'username' => 'budi_santoso',
            'email' => 'budi@example.com',
        ]);

        $user = User::where('email', 'budi@example.com')->first();
        $this->assertNotNull($user);
        $this->assertNotEquals('password123', $user->password);
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    public function test_duplicate_email_is_rejected(): void
    {
        User::factory()->create(['email' => 'budi@example.com']);

        $response = $this->from(route('register'))->post(route('register.store'), $this->registrationData());

        $response->assertSessionHasErrors('email');
        $this->assertDatabaseCount('users', 1);
    }

    public function test_duplicate_username_is_rejected(): void
    {
        User::factory()->create(['username' => 'budi_santoso']);

        $response = $this->from(route('register'))->post(route('register.store'), $this->registrationData());

        $response->assertSessionHasErrors('username');
        $this->assertDatabaseCount('users', 1);
    }

    public function test_short_password_is_rejected(): void
    {
        $response = $this->from(route('register'))->post(route('register.store'), $this->registrationData([
            'password' => 'short',
            'password_confirmation' => 'short',
        ]));

        $response->assertSessionHasErrors('password');
        $this->assertDatabaseCount('users', 0);
    }

    public function test_invalid_username_characters_are_rejected(): void
    {
        $response = $this->from(route('register'))->post(route('register.store'), $this->registrationData([
            'username' => 'budi-santoso!',
        ]));

        $response->assertSessionHasErrors('username');
        $this->assertDatabaseCount('users', 0);
    }

    public function test_name_is_required(): void
    {
        $response = $this->from(route('register'))->post(route('register.store'), $this->registrationData([
            'name' => '',
        ]));

        $response->assertSessionHasErrors('name');
        $this->assertDatabaseCount('users', 0);
    }
}
