<?php

namespace Tests\Feature;

use App\Mail\SecurityCodeMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PasswordChangeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        // Rate limiter counters live in the cache; start every test clean.
        Cache::flush();
    }

    protected function tearDown(): void
    {
        Cache::flush();

        parent::tearDown();
    }

    /**
     * Pull the plain code out of the last SecurityCodeMail that was sent.
     */
    private function captureCode(): string
    {
        $code = null;

        Mail::assertSent(SecurityCodeMail::class, function (SecurityCodeMail $mail) use (&$code): bool {
            $code = $mail->code;

            return true;
        });

        return (string) $code;
    }

    public function test_user_with_password_sees_the_confirmation_stage_first(): void
    {
        $user = User::factory()->create(['password' => 'password-lama-123']);

        $this->actingAs($user)
            ->get(route('settings.password.edit'))
            ->assertOk()
            ->assertSee('Konfirmasi Password Saat Ini')
            ->assertSee(route('settings.password.verify'), false);
    }

    public function test_update_password_is_blocked_until_current_password_is_verified(): void
    {
        $user = User::factory()->create(['password' => 'password-lama-123']);

        $this->actingAs($user)
            ->from(route('settings.password.edit'))
            ->post(route('settings.password.update'), [
                'password' => 'password-baru-456',
                'password_confirmation' => 'password-baru-456',
            ])
            ->assertRedirect(route('settings.password.edit'))
            ->assertSessionHas('error');

        $this->assertTrue(Hash::check('password-lama-123', $user->fresh()->password));
    }

    public function test_verify_current_password_rejects_the_wrong_password(): void
    {
        $user = User::factory()->create(['password' => 'password-lama-123']);

        $this->actingAs($user)
            ->from(route('settings.password.edit'))
            ->post(route('settings.password.verify'), ['current_password' => 'salah-banget'])
            ->assertSessionHasErrors('current_password');
    }

    public function test_verified_user_can_change_password(): void
    {
        $user = User::factory()->create(['password' => 'password-lama-123']);

        $this->actingAs($user)
            ->post(route('settings.password.verify'), ['current_password' => 'password-lama-123'])
            ->assertRedirect(route('settings.password.edit'));

        // Once verified, the page no longer asks for the current password.
        $this->actingAs($user)
            ->get(route('settings.password.edit'))
            ->assertOk()
            ->assertDontSee('name="current_password"', false);

        $this->actingAs($user)
            ->post(route('settings.password.update'), [
                'password' => 'password-baru-456',
                'password_confirmation' => 'password-baru-456',
            ])
            ->assertRedirect(route('settings.index', ['tab' => 'keamanan']))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $this->assertTrue(Hash::check('password-baru-456', $user->fresh()->password));
    }

    public function test_google_user_without_password_can_set_one_directly(): void
    {
        $user = User::factory()->create(['password' => null, 'google_id' => 'google-uid-1']);

        $this->actingAs($user)
            ->get(route('settings.password.edit'))
            ->assertOk()
            ->assertSee('Buat Password Akun')
            ->assertDontSee('name="current_password"', false);

        $this->actingAs($user)
            ->post(route('settings.password.update'), [
                'password' => 'sandi-awal-789',
                'password_confirmation' => 'sandi-awal-789',
            ])
            ->assertRedirect(route('settings.index', ['tab' => 'keamanan']))
            ->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('sandi-awal-789', $user->fresh()->password));
    }

    public function test_forgot_password_in_settings_emails_a_code_and_unlocks_the_form(): void
    {
        $user = User::factory()->create([
            'email' => 'reset@example.com',
            'password' => 'password-lama-123',
        ]);

        $this->actingAs($user)
            ->post(route('settings.password.forgot'))
            ->assertRedirect(route('settings.password.forgot.code'));

        Mail::assertSent(SecurityCodeMail::class, fn (SecurityCodeMail $mail): bool => $mail->hasTo('reset@example.com'));

        $this->actingAs($user)
            ->from(route('settings.password.forgot.code'))
            ->post(route('settings.password.forgot.verify'), ['code' => '000000'])
            ->assertSessionHasErrors('code');

        $this->actingAs($user)
            ->post(route('settings.password.forgot.verify'), ['code' => $this->captureCode()])
            ->assertRedirect(route('settings.password.edit'))
            ->assertSessionHas('status');

        // The form no longer asks for the current password after the code check.
        $this->actingAs($user)
            ->get(route('settings.password.edit'))
            ->assertOk()
            ->assertDontSee('name="current_password"', false);

        $this->actingAs($user)
            ->post(route('settings.password.update'), [
                'password' => 'password-baru-456',
                'password_confirmation' => 'password-baru-456',
            ])
            ->assertRedirect(route('settings.index', ['tab' => 'keamanan']));

        $this->assertTrue(Hash::check('password-baru-456', $user->fresh()->password));
    }

    public function test_login_code_flow_authenticates_the_user(): void
    {
        $user = User::factory()->create([
            'email' => 'login@example.com',
            'password' => 'password-lama-123',
        ]);

        $this->post(route('password.email'), ['email' => 'login@example.com'])
            ->assertRedirect(route('password.code'));

        Mail::assertSent(SecurityCodeMail::class, fn (SecurityCodeMail $mail): bool => $mail->hasTo('login@example.com'));

        $this->get(route('password.code'))->assertOk();

        $this->post(route('password.code.verify'), ['code' => $this->captureCode()])
            ->assertRedirect(route('projects.index'));

        $this->assertAuthenticated();
    }

    public function test_login_code_flow_rejects_a_wrong_code_and_stays_guest(): void
    {
        $user = User::factory()->create(['email' => 'guest@example.com']);

        $this->post(route('password.email'), ['email' => 'guest@example.com'])
            ->assertRedirect(route('password.code'));

        $this->from(route('password.code'))
            ->post(route('password.code.verify'), ['code' => '000000'])
            ->assertSessionHasErrors('code');

        $this->assertGuest();
    }

    public function test_login_code_flow_rejects_an_unknown_email(): void
    {
        $this->from(route('password.request'))
            ->post(route('password.email'), ['email' => 'nobody@example.com'])
            ->assertSessionHasErrors('email');

        Mail::assertNothingSent();
    }

    public function test_unverified_user_can_log_in_with_a_code_and_becomes_verified(): void
    {
        $user = User::factory()->unverified()->create([
            'email' => 'unverified@example.com',
            'password' => null,
        ]);

        $this->post(route('password.email'), ['email' => 'unverified@example.com'])
            ->assertRedirect(route('password.code'));

        $this->post(route('password.code.verify'), ['code' => $this->captureCode()])
            ->assertRedirect(route('projects.index'));

        $this->assertAuthenticated();
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_guests_cannot_open_the_settings_password_page(): void
    {
        $this->get(route('settings.password.edit'))
            ->assertRedirect(route('projects.index'))
            ->assertSessionHas('auth_required', true);
    }
}
