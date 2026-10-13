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

    public function test_message_is_accepted_when_recipient_follows_sender(): void
    {
        $a = User::factory()->create(); // sender
        $b = User::factory()->create(); // recipient

        // Recipient follows sender → direct delivery (IG rule).
        $b->following()->attach($a->id);

        $this->actingAs($a)->postJson('/obrolan/'.$b->username.'/kirim', ['body' => 'Halo']);

        $this->assertEquals('accepted', Conversation::first()->status);
    }

    public function test_message_is_pending_when_only_sender_follows_recipient(): void
    {
        $a = User::factory()->create(); // sender
        $b = User::factory()->create(); // recipient

        // Sender follows recipient, but recipient does NOT follow back.
        $a->following()->attach($b->id);

        $this->actingAs($a)->postJson('/obrolan/'.$b->username.'/kirim', ['body' => 'Halo']);

        // Must still be a request — a one-way follow does not grant direct DM.
        $this->assertEquals('pending', Conversation::first()->status);
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

    public function test_recipient_composer_is_locked_while_request_pending(): void
    {
        $a = User::factory()->create(); // initiator
        $b = User::factory()->create(); // recipient

        $this->actingAs($a)->postJson('/obrolan/'.$b->username.'/kirim', ['body' => 'Halo']);

        // Recipient sees accept/decline AND no composer.
        $recipient = $this->actingAs($b)->get('/obrolan/'.$a->username);
        $recipient->assertStatus(200);
        $recipient->assertSee('Terima');
        $recipient->assertDontSee('Tulis pesan');

        // Initiator (who already sent the opener) also has no composer.
        $initiator = $this->actingAs($a)->get('/obrolan/'.$b->username);
        $initiator->assertStatus(200);
        $initiator->assertDontSee('Tulis pesan');
    }

    public function test_initiator_composer_is_open_before_sending_opener(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();

        // No conversation yet — first message is allowed, so composer is present.
        $response = $this->actingAs($a)->get('/obrolan/'.$b->username);
        $response->assertStatus(200);
        $response->assertSee('Tulis pesan');
    }

    public function test_composer_reopens_for_both_after_accept(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();

        $this->actingAs($a)->postJson('/obrolan/'.$b->username.'/kirim', ['body' => 'Halo']);
        $this->actingAs($b)->postJson('/obrolan/'.$a->username.'/terima');

        $this->actingAs($a)->get('/obrolan/'.$b->username)->assertSee('Tulis pesan');
        $this->actingAs($b)->get('/obrolan/'.$a->username)->assertSee('Tulis pesan');
    }

    public function test_stale_accepted_conversation_is_demoted_when_rule_no_longer_met(): void
    {
        $a = User::factory()->create(); // sender
        $b = User::factory()->create(); // recipient

        // Simulate a legacy conversation wrongly marked accepted without acceptance.
        $conv = Conversation::create([
            'user_one_id' => min($a->id, $b->id),
            'user_two_id' => max($a->id, $b->id),
            'status' => 'accepted',
            'initiator_id' => $a->id,
        ]);

        // No follow relationship → next send must re-evaluate to pending.
        $this->actingAs($a)->postJson('/obrolan/'.$b->username.'/kirim', ['body' => 'Halo']);

        $this->assertEquals('pending', $conv->fresh()->status);
    }

    public function test_explicit_acceptance_is_sticky_across_resends(): void
    {
        $a = User::factory()->create();
        $b = User::factory()->create();

        // Start pending, then recipient accepts.
        $this->actingAs($a)->postJson('/obrolan/'.$b->username.'/kirim', ['body' => 'Halo']);
        $this->actingAs($b)->postJson('/obrolan/'.$a->username.'/terima');
        $conv = Conversation::first();
        $this->assertNotNull($conv->accepted_at);

        // Sender sends again (still no follows) — must remain accepted (sticky).
        $this->actingAs($a)->postJson('/obrolan/'.$b->username.'/kirim', ['body' => 'Lagi'])->assertStatus(200);
        $this->assertEquals('accepted', $conv->fresh()->status);
    }
}
