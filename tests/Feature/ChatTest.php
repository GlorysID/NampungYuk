<?php

namespace Tests\Feature;

use App\Events\MessageRead;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Notifications\NewMessageNotification;
use Illuminate\Broadcasting\BroadcastException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ChatTest extends TestCase
{
    use RefreshDatabase;

    private function pair(): array
    {
        $me = User::factory()->create();
        $other = User::factory()->create();

        return [$me, $other];
    }

    public function test_inbox_lists_conversations(): void
    {
        [$me, $other] = $this->pair();
        $conv = Conversation::between($me->id, $other->id);
        $conv->messages()->create(['sender_id' => $other->id, 'body' => 'Halo dari other']);

        $response = $this->actingAs($me)->get('/obrolan');

        $response->assertStatus(200);
        $response->assertSee($other->name);
        $response->assertSee('Halo dari other');
    }

    public function test_sending_a_message_persists_and_returns_json(): void
    {
        [$me, $other] = $this->pair();

        $response = $this->actingAs($me)->postJson('/obrolan/'.$other->username.'/kirim', [
            'body' => 'Pesan uji coba',
        ]);

        $response->assertStatus(200)->assertJson(['success' => true]);
        $this->assertDatabaseHas('messages', [
            'sender_id' => $me->id,
            'body' => 'Pesan uji coba',
        ]);
    }

    public function test_cannot_message_self(): void
    {
        $me = User::factory()->create();

        $response = $this->actingAs($me)->postJson('/obrolan/'.$me->username.'/kirim', [
            'body' => 'Halo diri sendiri',
        ]);

        $response->assertStatus(422);
    }

    public function test_opening_conversation_marks_incoming_messages_read(): void
    {
        [$me, $other] = $this->pair();
        $conv = Conversation::between($me->id, $other->id);
        $incoming = $conv->messages()->create(['sender_id' => $other->id, 'body' => 'Belum dibaca']);

        $this->actingAs($me)->get('/obrolan/'.$other->username)->assertStatus(200);

        $this->assertNotNull($incoming->fresh()->read_at);
    }

    public function test_can_delete_own_message(): void
    {
        [$me, $other] = $this->pair();
        $conv = Conversation::between($me->id, $other->id);
        $mine = $conv->messages()->create(['sender_id' => $me->id, 'body' => 'Hapus aku']);

        $response = $this->actingAs($me)->deleteJson('/obrolan/pesan/'.$mine->id);

        $response->assertStatus(200)->assertJson(['success' => true]);
        $this->assertSoftDeleted('messages', ['id' => $mine->id]);
    }

    public function test_cannot_delete_other_users_message(): void
    {
        [$me, $other] = $this->pair();
        $conv = Conversation::between($me->id, $other->id);
        $theirs = $conv->messages()->create(['sender_id' => $other->id, 'body' => 'Pesan other']);

        $response = $this->actingAs($me)->deleteJson('/obrolan/pesan/'.$theirs->id);

        $response->assertStatus(403);
        $this->assertDatabaseHas('messages', ['id' => $theirs->id, 'deleted_at' => null]);
    }

    public function test_can_load_older_messages(): void
    {
        [$me, $other] = $this->pair();
        $conv = Conversation::between($me->id, $other->id);
        for ($i = 1; $i <= 60; $i++) {
            $conv->messages()->create(['sender_id' => $me->id, 'body' => "Pesan {$i}"]);
        }

        $first = $conv->messages()->oldest()->first();

        $response = $this->actingAs($me)->getJson('/obrolan/'.$other->username.'/lama?before='.($first->id + 50));

        $response->assertStatus(200)->assertJson(['success' => true]);
        $this->assertNotEmpty($response->json('messages'));
    }

    public function test_show_loads_the_latest_messages_not_the_oldest(): void
    {
        [$me, $other] = $this->pair();
        $conv = Conversation::between($me->id, $other->id);

        // 50 messages so the page cap (40) actually has to pick the newest.
        for ($i = 1; $i <= 50; $i++) {
            $conv->messages()->create(['sender_id' => $me->id, 'body' => "Pesan nomor {$i}"]);
        }

        $response = $this->actingAs($me)->get('/obrolan/'.$other->username);

        $response->assertStatus(200);
        // The newest message must be present in the seeded payload...
        $response->assertSee('Pesan nomor 50', false);
        // ...and the oldest must NOT (it fell outside the latest-40 window).
        $response->assertDontSee('Pesan nomor 1"', false);
    }

    public function test_typing_endpoint_responds(): void
    {
        [$me, $other] = $this->pair();

        $response = $this->actingAs($me)->postJson('/obrolan/'.$other->username.'/mengetik', [
            'typing' => true,
        ]);

        $response->assertStatus(200)->assertJson(['success' => true]);
    }

    public function test_can_delete_whole_conversation(): void
    {
        [$me, $other] = $this->pair();
        $conv = Conversation::between($me->id, $other->id);
        $conv->messages()->create(['sender_id' => $me->id, 'body' => 'X']);

        $response = $this->actingAs($me)->deleteJson('/obrolan/'.$other->username.'/hapus');

        $response->assertStatus(200)->assertJson(['success' => true]);
        $this->assertEquals(0, Conversation::where('id', $conv->id)->count());
        $this->assertEquals(0, Message::withTrashed()->where('conversation_id', $conv->id)->count());
    }

    public function test_guests_cannot_access_chat(): void
    {
        $other = User::factory()->create();
        $this->get('/obrolan/'.$other->username)
            ->assertRedirect(route('projects.index'))
            ->assertSessionHas('auth_required');
    }

    public function test_sender_can_edit_own_recent_message(): void
    {
        [$me, $other] = $this->pair();
        $conv = Conversation::between($me->id, $other->id);
        $msg = $conv->messages()->create(['sender_id' => $me->id, 'body' => 'asli']);

        $response = $this->actingAs($me)->patchJson('/obrolan/pesan/'.$msg->id, ['body' => 'diubah']);

        $response->assertStatus(200)->assertJson(['success' => true]);
        $msg->refresh();
        $this->assertEquals('diubah', $msg->body);
        $this->assertNotNull($msg->edited_at);
    }

    public function test_cannot_edit_other_users_message(): void
    {
        [$me, $other] = $this->pair();
        $conv = Conversation::between($me->id, $other->id);
        $msg = $conv->messages()->create(['sender_id' => $other->id, 'body' => 'punya other']);

        $this->actingAs($me)->patchJson('/obrolan/pesan/'.$msg->id, ['body' => 'hack'])->assertStatus(403);
        $this->assertEquals('punya other', $msg->fresh()->body);
    }

    public function test_cannot_edit_message_after_window(): void
    {
        [$me, $other] = $this->pair();
        $conv = Conversation::between($me->id, $other->id);
        $msg = $conv->messages()->create(['sender_id' => $me->id, 'body' => 'lama']);
        $msg->forceFill(['created_at' => now()->subHour()])->save();

        $this->actingAs($me)->patchJson('/obrolan/pesan/'.$msg->id, ['body' => 'terlambat'])->assertStatus(403);
    }

    public function test_can_clear_all_messages_but_keep_conversation(): void
    {
        [$me, $other] = $this->pair();
        $conv = Conversation::between($me->id, $other->id);
        $conv->messages()->create(['sender_id' => $me->id, 'body' => 'a']);
        $conv->messages()->create(['sender_id' => $me->id, 'body' => 'b']);

        $response = $this->actingAs($me)->deleteJson('/obrolan/'.$other->username.'/bersihkan');

        $response->assertStatus(200)->assertJson(['success' => true]);
        $this->assertEquals(0, $conv->messages()->count());
        // Conversation row is preserved.
        $this->assertNotNull(Conversation::find($conv->id));
    }

    public function test_sending_a_message_notifies_the_recipient(): void
    {
        [$me, $other] = $this->pair();

        $this->actingAs($me)->postJson('/obrolan/'.$other->username.'/kirim', ['body' => 'Halo']);

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $other->id,
            'type' => NewMessageNotification::class,
        ]);
    }

    public function test_repeated_messages_replace_not_stack_the_notification(): void
    {
        [$me, $other] = $this->pair();

        $this->actingAs($me)->postJson('/obrolan/'.$other->username.'/kirim', ['body' => 'Pesan 1']);
        $this->actingAs($me)->postJson('/obrolan/'.$other->username.'/kirim', ['body' => 'Pesan 2']);
        $this->actingAs($me)->postJson('/obrolan/'.$other->username.'/kirim', ['body' => 'Pesan 3']);

        $count = $other->notifications()
            ->where('type', NewMessageNotification::class)
            ->count();

        $this->assertEquals(1, $count);
    }

    public function test_notification_dropdown_endpoint_returns_message_notification(): void
    {
        [$me, $other] = $this->pair();
        $this->actingAs($me)->postJson('/obrolan/'.$other->username.'/kirim', ['body' => 'Halo dari me']);

        $response = $this->actingAs($other)->getJson('/notifikasi/latest');

        $response->assertStatus(200);
        $this->assertGreaterThanOrEqual(1, $response->json('unread_count'));

        $types = collect($response->json('notifications'))->pluck('data.type');
        $this->assertTrue($types->contains('new_message'));
    }

    public function test_sidebar_shows_unread_chat_badge_and_clears_when_opened(): void
    {
        [$sender, $recipient] = $this->pair();

        $this->actingAs($sender)->postJson('/obrolan/'.$recipient->username.'/kirim', ['body' => 'Pesan 1'])->assertOk();
        $this->actingAs($sender)->postJson('/obrolan/'.$recipient->username.'/kirim', ['body' => 'Pesan 2'])->assertOk();

        // On feed, recipient sees unreadChatCount: 2 in the sidebar state.
        $this->actingAs($recipient)
            ->get('/')
            ->assertOk()
            ->assertSee('unreadChatCount: 2', false);

        // Opening the thread marks messages as read so the next page shows 0.
        $this->actingAs($recipient)
            ->get('/obrolan/'.$sender->username)
            ->assertOk()
            ->assertSee('Bergabung', false);

        $this->actingAs($recipient)
            ->get('/')
            ->assertOk()
            ->assertSee('unreadChatCount: 0', false);
    }

    public function test_sync_endpoint_returns_new_messages_typing_state_and_read_receipts(): void
    {
        [$a, $b] = $this->pair();

        // A starts typing before any conversation exists (should not create a conversation row).
        $this->actingAs($a)
            ->postJson('/obrolan/'.$b->username.'/mengetik', ['typing' => true])
            ->assertOk();

        $this->actingAs($b)
            ->getJson('/obrolan/'.$a->username.'/sync?after=0')
            ->assertOk()
            ->assertJsonPath('typing', true)
            ->assertJsonPath('conversation', null);

        // A sends a message -> clears typing state immediately.
        $sent = $this->actingAs($a)
            ->postJson('/obrolan/'.$b->username.'/kirim', ['body' => 'Halo realtime!'])
            ->assertOk();
        $msgId = $sent->json('message.id');

        // B syncs -> gets the new message, typing is false, and message is marked read.
        $this->actingAs($b)
            ->getJson('/obrolan/'.$a->username.'/sync?after=0')
            ->assertOk()
            ->assertJsonPath('typing', false)
            ->assertJsonCount(1, 'messages')
            ->assertJsonPath('messages.0.id', $msgId)
            ->assertJsonPath('messages.0.body', 'Halo realtime!');

        // A syncs -> sees read_up_to_id matching the message B just read.
        $this->actingAs($a)
            ->getJson('/obrolan/'.$b->username.'/sync?after='.$msgId)
            ->assertOk()
            ->assertJsonPath('read_up_to_id', $msgId);
    }

    public function test_mark_read_endpoint_and_typing_bubble_markup(): void
    {
        [$a, $b] = $this->pair();

        $this->actingAs($a)
            ->postJson('/obrolan/'.$b->username.'/kirim', ['body' => 'Cek read receipt'])
            ->assertOk();

        $this->actingAs($b)
            ->postJson('/obrolan/'.$a->username.'/baca')
            ->assertOk()
            ->assertJsonPath('marked', 1);

        $this->actingAs($b)
            ->get('/obrolan/'.$a->username)
            ->assertOk()
            ->assertSee('ny-typing-dot', false)
            ->assertSee('syncUrl:', false)
            ->assertSee('readUrl:', false);
    }

    public function test_chat_requests_do_not_500_when_broadcaster_throws(): void
    {
        [$a, $b] = $this->pair();

        $this->actingAs($a)
            ->postJson('/obrolan/'.$b->username.'/kirim', ['body' => 'Pesan sebelum reverb mati'])
            ->assertOk();

        // Force BroadcastFactory / events to throw BroadcastException as happens when Reverb is offline.
        Event::listen(
            MessageRead::class,
            static fn () => throw new BroadcastException('Connection timed out')
        );

        $this->actingAs($b)
            ->get('/obrolan/'.$a->username)
            ->assertOk();
    }

    public function test_chat_intro_card_renders_follow_button_with_live_state(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create([
            'github_url' => 'https://github.com/bagus-dev',
            'website_url' => 'javascript:alert(1)',
        ]);

        $this->actingAs($a)
            ->get('/obrolan/'.$b->username)
            ->assertOk()
            ->assertSee('followButton({', false)
            ->assertSee(route('follow.toggle', $b), false)
            ->assertSee('following: false', false)
            ->assertSee('Lihat Profil')
            ->assertSee('https://github.com/bagus-dev', false)
            ->assertDontSee('javascript:alert(1)', false)
            ->assertDontSee('Mengikutimu');

        $b->following()->attach($a->id);

        $this->actingAs($a)
            ->get('/obrolan/'.$b->username)
            ->assertOk()
            ->assertSee('mutual: false', false)
            ->assertSee('Mengikutimu');

        $this->actingAs($a)
            ->postJson(route('follow.toggle', $b))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('following', true)
            ->assertJsonPath('followers_count', 1);

        $this->actingAs($a)
            ->get('/obrolan/'.$b->username)
            ->assertOk()
            ->assertSee('following: true', false)
            ->assertSee('mutual: true', false)
            ->assertSee('Teman');
    }
}
