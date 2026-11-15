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
}
