<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Notifications\NewMessageNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        $this->get('/obrolan/'.$other->username)->assertRedirect('/login');
    }

    public function test_sender_can_edit_own_recent_message(): void
    {        [$me, $other] = $this->pair();
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
}
