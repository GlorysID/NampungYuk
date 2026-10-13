<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_mutual_message_starts_as_pending(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();

        $this->actingAs($a)->postJson('/obrolan/'.$b->username.'/kirim', ['body' => 'Halo']);

        $conv = Conversation::first();
        $this->assertEquals('pending', $conv->status);
        $this->assertEquals($a->id, $conv->initiator_id);
    }

    public function test_mutual_follow_message_starts_as_accepted(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();
        $a->following()->attach($b->id);
        $b->following()->attach($a->id);

        $this->actingAs($a)->postJson('/obrolan/'.$b->username.'/kirim', ['body' => 'Halo']);

        $this->assertEquals('accepted', Conversation::first()->status);
    }

    public function test_initiator_cannot_send_second_message_while_pending(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();

        $this->actingAs($a)->postJson('/obrolan/'.$b->username.'/kirim', ['body' => 'Pesan pertama'])->assertStatus(200);
        $this->actingAs($a)->postJson('/obrolan/'.$b->username.'/kirim', ['body' => 'Pesan kedua'])->assertStatus(403);
    }

    public function test_recipient_cannot_reply_before_accepting(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();

        $this->actingAs($a)->postJson('/obrolan/'.$b->username.'/kirim', ['body' => 'Halo']);
        $this->actingAs($b)->postJson('/obrolan/'.$a->username.'/kirim', ['body' => 'Balas'])->assertStatus(403);
    }

    public function test_recipient_can_accept_request_then_chat_freely(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();

        $this->actingAs($a)->postJson('/obrolan/'.$b->username.'/kirim', ['body' => 'Halo']);

        $this->actingAs($b)->postJson('/obrolan/'.$a->username.'/terima')->assertStatus(200);
        $this->assertEquals('accepted', Conversation::first()->status);

        // Both can now chat.
        $this->actingAs($b)->postJson('/obrolan/'.$a->username.'/kirim', ['body' => 'Oke'])->assertStatus(200);
        $this->actingAs($a)->postJson('/obrolan/'.$b->username.'/kirim', ['body' => 'Sip'])->assertStatus(200);
    }

    public function test_initiator_cannot_accept_own_request(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();

        $this->actingAs($a)->postJson('/obrolan/'.$b->username.'/kirim', ['body' => 'Halo']);

        $this->actingAs($a)->postJson('/obrolan/'.$b->username.'/terima')->assertStatus(403);
    }

    public function test_recipient_can_decline_request(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();

        $this->actingAs($a)->postJson('/obrolan/'.$b->username.'/kirim', ['body' => 'Halo']);

        $this->actingAs($b)->postJson('/obrolan/'.$a->username.'/tolak')->assertStatus(200);
        $this->assertEquals(0, Conversation::count());
    }

    public function test_inbox_shows_requests_separately_from_conversations(): void
    {
        $recipient = User::factory()->create();
        $stranger = User::factory()->create(['name' => 'Stranger Danger']);

        $this->actingAs($stranger)->postJson('/obrolan/'.$recipient->username.'/kirim', ['body' => 'Hai, kenalan yuk']);

        $response = $this->actingAs($recipient)->get('/obrolan');
        $response->assertStatus(200);
        $response->assertSee('Permintaan Pesan');
        $response->assertSee('Stranger Danger');
    }

    public function test_profile_has_message_button_for_other_users(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create(['username' => 'target_user']);

        $response = $this->actingAs($a)->get('/u/'.$b->username);
        $response->assertStatus(200);
        $response->assertSee('Pesan');
        $response->assertSee('/obrolan/'.$b->username);
    }

    public function test_typing_does_not_create_conversation_or_bypass_the_gate(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();

        // Typing before any message must not create a conversation row.
        $this->actingAs($a)->postJson('/obrolan/'.$b->username.'/mengetik', ['typing' => true])->assertStatus(200);
        $this->assertEquals(0, Conversation::count());

        // First real message starts a pending request.
        $this->actingAs($a)->postJson('/obrolan/'.$b->username.'/kirim', ['body' => 'Halo'])->assertStatus(200);
        $this->assertEquals('pending', Conversation::first()->status);

        // The initiator still cannot send a second message (gate intact).
        $this->actingAs($a)->postJson('/obrolan/'.$b->username.'/kirim', ['body' => 'Lagi'])->assertStatus(403);
    }

    public function test_typing_after_conversation_exists_does_not_resend_gate(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();

        $this->actingAs($a)->postJson('/obrolan/'.$b->username.'/kirim', ['body' => 'Halo']);
        $conv = Conversation::first();

        $this->actingAs($a)->postJson('/obrolan/'.$b->username.'/mengetik', ['typing' => true])->assertStatus(200);

        // Only the one conversation exists — typing did not create another.
        $this->assertEquals(1, Conversation::count());
        $this->assertEquals($conv->id, Conversation::first()->id);
    }

    public function test_opening_a_fresh_chat_does_not_create_a_conversation(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();

        $response = $this->actingAs($a)->get('/obrolan/'.$b->username);
        $response->assertStatus(200);

        $this->assertEquals(0, Conversation::count());
    }

    public function test_older_endpoint_returns_empty_when_no_conversation(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();

        $response = $this->actingAs($a)->getJson('/obrolan/'.$b->username.'/lama?before=999');
        $response->assertStatus(200)->assertJson(['success' => true, 'has_more' => false]);
        $this->assertEquals(0, Conversation::count());
    }
}
