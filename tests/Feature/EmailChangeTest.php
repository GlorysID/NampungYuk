<?php

namespace Tests\Feature;

use App\Mail\SecurityCodeMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmailChangeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Rate limiter counters live in the cache; start every test clean.
        Cache::flush();
    }

    protected function tearDown(): void
    {
        Cache::flush();

        parent::tearDown();
    }

    private function user(): User
    {
        return User::factory()->create([
            'email' => 'lama@example.com',
            'email_verified_at' => now(),
        ]);
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

    public function test_code_is_sent_to_the_new_address_without_touching_the_current_email(): void
    {
        Mail::fake();
        $user = $this->user();

        $this->actingAs($user)
            ->post(route('settings.email.send'), ['email' => 'baru@example.com'])
            ->assertRedirect(route('settings.email.code'))
            ->assertSessionHas('status')
            ->assertSessionHas('email_change.pending', 'baru@example.com');

        Mail::assertSent(SecurityCodeMail::class, fn (SecurityCodeMail $mail): bool => $mail->hasTo('baru@example.com'));

        $this->assertSame('lama@example.com', $user->fresh()->email);
    }

    public function test_wrong_code_errors_and_keeps_the_email(): void
    {
        Mail::fake();
        $user = $this->user();

        $this->actingAs($user)->post(route('settings.email.send'), ['email' => 'baru@example.com']);
        $wrong = str_pad((string) (((int) $this->captureCode() + 1) % 1000000), 6, '0', STR_PAD_LEFT);

        $this->actingAs($user)
            ->post(route('settings.email.verify'), ['code' => $wrong])
            ->assertSessionHasErrors('code');

        $this->assertSame('lama@example.com', $user->fresh()->email);
    }

    public function test_correct_code_moves_the_account_to_the_new_email(): void
    {
        Mail::fake();
        $user = $this->user();

        $this->actingAs($user)->post(route('settings.email.send'), ['email' => 'baru@example.com']);
        $code = $this->captureCode();

        $this->actingAs($user)
            ->post(route('settings.email.verify'), ['code' => $code])
            ->assertRedirect(route('settings.index', ['tab' => 'keamanan']))
            ->assertSessionHas('success')
            ->assertSessionMissing('email_change.pending');

        $fresh = $user->fresh();
        $this->assertSame('baru@example.com', $fresh->email);
        $this->assertNotNull($fresh->email_verified_at);
    }

    public function test_email_already_used_by_another_account_is_rejected(): void
    {
        Mail::fake();
        User::factory()->create(['email' => 'dipakai@example.com']);
        $user = $this->user();

        $this->actingAs($user)
            ->post(route('settings.email.send'), ['email' => 'dipakai@example.com'])
            ->assertSessionHasErrors('email');

        Mail::assertNothingSent();
        $this->assertSame('lama@example.com', $user->fresh()->email);
    }

    public function test_email_equal_to_the_current_one_is_rejected(): void
    {
        Mail::fake();
        $user = $this->user();

        $this->actingAs($user)
            ->post(route('settings.email.send'), ['email' => 'LAMA@example.com'])
            ->assertSessionHasErrors('email');

        Mail::assertNothingSent();
    }

    public function test_code_page_without_a_pending_flow_redirects_to_the_form(): void
    {
        $user = $this->user();

        $this->actingAs($user)
            ->get(route('settings.email.code'))
            ->assertRedirect(route('settings.email.edit'));
    }

    public function test_guest_is_redirected_away(): void
    {
        $this->get(route('settings.email.edit'))->assertRedirect(route('projects.index'));
    }
}
