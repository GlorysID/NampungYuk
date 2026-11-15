<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatBlockPrivacyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A conversation with real history, where $blocker has blocked $blocked.
     *
     * @return array{0: User, 1: User, 2: Conversation, 3: int}
     */
    private function blockedPairWithHistory(): array
    {
        $blocker = User::factory()->create();
        $blocked = User::factory()->create();

        $conversation = Conversation::between($blocker->id, $blocked->id);
        $conversation->messages()->create(['sender_id' => $blocked->id, 'body' => 'KODE-RAHASIA-TERAKHIR']);
        $last = $conversation->messages()->create(['sender_id' => $blocker->id, 'body' => 'KODE-RAHASIA-BALASAN']);

        $blocker->blockedUsers()->attach($blocked->id);

        return [$blocker, $blocked, $conversation, $last->id];
    }

    public function test_blocked_user_cannot_read_conversation_or_older_history(): void
    {
        [$blocker, $blocked, , $lastId] = $this->blockedPairWithHistory();

        $show = $this->actingAs($blocked)->get('/obrolan/'.$blocker->username);
        $show->assertStatus(403);
        $this->assertStringNotContainsString('KODE-RAHASIA-TERAKHIR', $show->getContent());
        $this->assertStringNotContainsString('KODE-RAHASIA-BALASAN', $show->getContent());

        $older = $this->actingAs($blocked)
            ->getJson('/obrolan/'.$blocker->username.'/lama?before='.($lastId + 1));
        $older->assertStatus(403);
        $this->assertStringNotContainsString('KODE-RAHASIA-TERAKHIR', $older->getContent());
    }

    public function test_blocker_also_cannot_read_the_conversation(): void
    {
        [$blocker, $blocked, , $lastId] = $this->blockedPairWithHistory();

        $this->actingAs($blocker)->get('/obrolan/'.$blocked->username)->assertStatus(403);

        $older = $this->actingAs($blocker)
            ->getJson('/obrolan/'.$blocked->username.'/lama?before='.($lastId + 1));
        $older->assertStatus(403);
        $this->assertStringNotContainsString('KODE-RAHASIA-BALASAN', $older->getContent());
    }

    public function test_inbox_hides_blocked_conversation_preview(): void
    {
        [$blocker] = $this->blockedPairWithHistory();

        $this->actingAs($blocker)
            ->get('/obrolan')
            ->assertStatus(200)
            ->assertDontSee('KODE-RAHASIA-TERAKHIR');
    }

    /**
     * `following_only` refuses the thread outright (no pending request), so the
     * locked composer must not promise a request that will never exist.
     */
    public function test_following_only_policy_refuses_a_new_thread_and_says_why(): void
    {
        $recipient = User::factory()->create();
        $sender = User::factory()->create();

        $this->actingAs($recipient)
            ->putJson(route('settings.privacy.update'), ['dm_policy' => 'following_only'])
            ->assertOk();

        $this->actingAs($sender)
            ->get('/obrolan/'.$recipient->username)
            ->assertStatus(200)
            ->assertSee('blockedByPolicy: true', false)
            ->assertSee('hanya menerima obrolan dari akun yang mereka ikuti', false);

        $this->actingAs($sender)
            ->get(route('profile.show', $recipient->username))
            ->assertStatus(200)
            ->assertSee('hanya menerima pesan dari akun yang mereka ikuti', false);

        $this->actingAs($sender)
            ->postJson('/obrolan/'.$recipient->username.'/kirim', ['body' => 'halo'])
            ->assertStatus(403);

        $this->assertDatabaseCount('conversations', 0);
    }

    public function test_existing_thread_keeps_working_after_switching_to_following_only(): void
    {
        $recipient = User::factory()->create();
        $sender = User::factory()->create();

        Conversation::between($recipient->id, $sender->id);

        $this->actingAs($recipient)
            ->putJson(route('settings.privacy.update'), ['dm_policy' => 'following_only'])
            ->assertOk();

        $this->actingAs($sender)
            ->postJson('/obrolan/'.$recipient->username.'/kirim', ['body' => 'masih bisa kirim'])
            ->assertOk()
            ->assertJsonPath('success', true);
    }
}
