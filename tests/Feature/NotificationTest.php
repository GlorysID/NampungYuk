<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\NewFollowerNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_opening_notifications_page_does_not_mark_them_read(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $user->notify(new NewFollowerNotification($other));

        $this->actingAs($user)->get('/notifikasi')
            ->assertStatus(200)
            ->assertSee('mulai mengikuti kamu');

        // Buka lagi (refresh): notifikasi tetap tersimpan dan tetap belum dibaca.
        $this->actingAs($user)->get('/notifikasi')->assertStatus(200);

        $this->assertSame(1, $user->notifications()->count());
        $this->assertSame(1, $user->unreadNotifications()->count());
    }

    public function test_clicking_a_notification_marks_it_read_and_redirects_to_target(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $user->notify(new NewFollowerNotification($other));

        $notification = $user->unreadNotifications()->first();

        $this->actingAs($user)
            ->post("/notifikasi/{$notification->id}/read", ['to' => route('profile.show', $other->username)])
            ->assertRedirect(route('profile.show', $other->username));

        $this->assertSame(0, $user->unreadNotifications()->count());
        $this->assertSame(1, $user->notifications()->count());
    }

    public function test_clicking_a_notification_with_foreign_url_does_not_redirect_out(): void
    {
        $user = User::factory()->create();
        $user->notify(new NewFollowerNotification(User::factory()->create()));

        $notification = $user->unreadNotifications()->first();

        $this->actingAs($user)
            ->post("/notifikasi/{$notification->id}/read", ['to' => 'https://evil.example.com/phish'])
            ->assertRedirect(url('/'));
    }
}
