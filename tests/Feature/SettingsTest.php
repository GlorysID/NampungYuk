<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Project;
use App\Models\User;
use App\Notifications\NewFollowerNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_workspace_renders_all_five_social_sections(): void
    {
        $user = User::factory()->create(['username' => 'sosmed_dev']);

        $this->actingAs($user)
            ->get(route('settings.index'))
            ->assertOk()
            ->assertSee('Akun Anda')
            ->assertSee('Keamanan & Sesi')
            ->assertSee('Privasi & Blokir')
            ->assertSee('Notifikasi')
            ->assertSee('Preferensi & Pintasan')
            ->assertSee('Unduh Arsip Data Anda');
    }

    public function test_user_can_export_personal_archive_json(): void
    {
        $user = User::factory()->create(['username' => 'exporter']);

        $response = $this->actingAs($user)->get(route('settings.export'));

        $response->assertOk()
            ->assertHeader('content-type', 'application/json; charset=UTF-8');

        $json = json_decode($response->streamedContent(), true);
        $this->assertSame('exporter', $json['account']['username']);
        $this->assertArrayHasKey('projects', $json);
        $this->assertArrayHasKey('preferences', $json);
    }

    public function test_user_can_view_and_revoke_other_active_sessions(): void
    {
        $user = User::factory()->create();

        DB::table('sessions')->insert([
            'id' => 'other-device-session-1',
            'user_id' => $user->id,
            'ip_address' => '10.0.0.42',
            'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) Safari/604.1',
            'payload' => base64_encode(serialize([])),
            'last_activity' => now()->subMinutes(10)->timestamp,
        ]);

        $this->actingAs($user)
            ->get(route('settings.index', ['tab' => 'keamanan']))
            ->assertOk()
            ->assertSee('10.0.0.42')
            ->assertSee('iOS');

        $this->actingAs($user)
            ->delete(route('settings.sessions.destroy', 'other-device-session-1'))
            ->assertRedirect(route('settings.index', ['tab' => 'keamanan']));

        $this->assertDatabaseMissing('sessions', ['id' => 'other-device-session-1']);
    }

    public function test_dm_privacy_following_only_blocks_unfollowed_sender_on_new_chat(): void
    {
        $recipient = User::factory()->create([
            'username' => 'penerima_ketat',
            'preferences' => ['privacy' => ['dm_policy' => 'following_only']],
        ]);
        $stranger = User::factory()->create(['username' => 'orang_asing']);

        $this->actingAs($stranger)
            ->postJson(route('chat.send', $recipient->username), ['body' => 'Halo bro'])
            ->assertForbidden()
            ->assertJsonPath('success', false);

        // Recipient follows stranger back -> DM is now allowed.
        $recipient->following()->attach($stranger->id);

        $this->actingAs($stranger)
            ->postJson(route('chat.send', $recipient->username), ['body' => 'Sekarang boleh'])
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_saved_dm_policy_survives_reload_of_settings_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('settings.privacy.update'), [
                'dm_policy' => 'following_only',
                'allow_reposts' => '1',
                // discoverable and show_read_receipts intentionally unchecked
            ])
            ->assertRedirect(route('settings.index', ['tab' => 'privasi']));

        $fresh = $user->fresh();
        $this->assertSame('following_only', $fresh->preference('privacy.dm_policy'));
        $this->assertTrue((bool) $fresh->preference('privacy.allow_reposts'));
        $this->assertFalse((bool) $fresh->preference('privacy.discoverable'));

        $this->actingAs($fresh)
            ->get(route('settings.index', ['tab' => 'privasi']))
            ->assertOk()
            ->assertViewHas('preferences', fn (array $prefs) => $prefs['privacy']['dm_policy'] === 'following_only');
    }

    public function test_blocking_user_detaches_follow_blocks_chat_and_can_be_unblocked(): void
    {
        $blocker = User::factory()->create(['username' => 'pemblokir']);
        $target = User::factory()->create(['username' => 'terblokir']);

        $blocker->following()->attach($target->id);
        $target->following()->attach($blocker->id);

        $this->actingAs($blocker)
            ->post(route('settings.blocks.store'), ['username' => '@terblokir'])
            ->assertRedirect(route('settings.index', ['tab' => 'privasi']));

        $this->assertTrue($blocker->hasBlocked($target));
        $this->assertFalse($blocker->isFollowing($target));
        $this->assertFalse($target->isFollowing($blocker));

        $this->actingAs($target)
            ->postJson(route('chat.send', $blocker->username), ['body' => 'Pesan diblok'])
            ->assertForbidden();

        $this->actingAs($target)
            ->postJson(route('follow.toggle', $blocker->username))
            ->assertForbidden();

        $this->actingAs($blocker)
            ->delete(route('settings.blocks.destroy', $target))
            ->assertRedirect(route('settings.index', ['tab' => 'privasi']));

        $this->assertFalse($blocker->hasBlocked($target));
    }

    public function test_repost_setting_prevents_others_from_reposting_when_disabled(): void
    {
        $author = User::factory()->create([
            'preferences' => ['privacy' => ['allow_reposts' => false]],
        ]);
        $other = User::factory()->create();
        $category = Category::create(['name' => 'Tools', 'slug' => 'tools']);

        $project = Project::create([
            'user_id' => $author->id,
            'category_id' => $category->id,
            'title' => 'Proyek Non-Repost',
            'slug' => 'proyek-non-repost',
            'tagline' => 'Tidak boleh di-repost',
            'description' => 'Tes privasi repost',
            'tech_stacks' => ['PHP'],
        ]);

        $this->actingAs($other)
            ->postJson(route('projects.repost', $project))
            ->assertForbidden();
    }

    public function test_notification_preferences_suppress_disabled_notifications(): void
    {
        Notification::fake();

        $recipient = User::factory()->create();
        $follower = User::factory()->create();

        $this->actingAs($recipient)
            ->put(route('settings.notifications.update'), [
                // new_follower intentionally omitted (unchecked)
                'project_comment' => '1',
                'project_repost' => '1',
                'new_message' => '1',
                'project_uploaded' => '1',
                'project_trending' => '1',
            ])
            ->assertRedirect(route('settings.index', ['tab' => 'notifikasi']));

        $this->assertFalse($recipient->fresh()->wantsNotification('new_follower'));
        $this->assertTrue($recipient->fresh()->wantsNotification('project_comment'));

        $this->actingAs($follower)
            ->post(route('follow.toggle', $recipient->username))
            ->assertRedirect();

        Notification::assertNotSentTo($recipient, NewFollowerNotification::class);
    }

    public function test_user_can_save_media_and_shortcut_preferences(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('settings.preferences.update'), [
                'autoplay_media' => '1',
                // keyboard_shortcuts unchecked
            ])
            ->assertRedirect(route('settings.index', ['tab' => 'tampilan']));

        $fresh = $user->fresh();
        $this->assertTrue((bool) $fresh->preference('display.autoplay_media'));
        $this->assertFalse((bool) $fresh->preference('display.keyboard_shortcuts'));
    }

    public function test_toggle_endpoints_answer_json_without_redirecting(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->putJson(route('settings.privacy.update'), ['dm_policy' => 'following_only'])
            ->assertOk()
            ->assertJsonStructure(['message']);

        $this->actingAs($user)
            ->putJson(route('settings.notifications.update'), ['new_follower' => '1'])
            ->assertOk()
            ->assertJsonStructure(['message']);

        $this->actingAs($user)
            ->putJson(route('settings.preferences.update'), ['keyboard_shortcuts' => '1'])
            ->assertOk()
            ->assertJsonStructure(['message']);

        $fresh = $user->fresh();
        $this->assertSame('following_only', $fresh->preference('privacy.dm_policy'));
        $this->assertTrue($fresh->wantsNotification('new_follower'));
        $this->assertTrue((bool) $fresh->preference('display.keyboard_shortcuts'));
    }

    public function test_block_and_unblock_endpoints_answer_json_for_in_place_actions(): void
    {
        $user = User::factory()->create();
        $target = User::factory()->create(['username' => 'sasaran_blokir']);

        $this->actingAs($user)
            ->postJson(route('settings.blocks.store'), ['username' => '@sasaran_blokir'])
            ->assertOk()
            ->assertJsonPath('user.username', 'sasaran_blokir')
            ->assertJsonStructure(['message', 'user' => ['id', 'username']]);

        $this->assertTrue($user->fresh()->hasBlocked($target));

        $this->actingAs($user)
            ->deleteJson(route('settings.blocks.destroy', $target))
            ->assertOk()
            ->assertJsonStructure(['message']);

        $this->assertFalse($user->fresh()->hasBlocked($target));

        $this->actingAs($user)
            ->postJson(route('settings.blocks.store'), ['username' => 'tidak_ada_orangnya'])
            ->assertStatus(422)
            ->assertJsonStructure(['message']);

        $this->actingAs($user)
            ->postJson(route('settings.blocks.store'), ['username' => $user->username])
            ->assertStatus(422);
    }

    public function test_user_can_delete_account_permanently_with_confirmation(): void
    {
        $user = User::factory()->create([
            'password' => 'konfirmasi-hapus',
        ]);

        $this->actingAs($user)
            ->delete(route('settings.account.destroy'), [
                'delete_password' => 'salah',
            ])
            ->assertSessionHasErrors('delete_password');

        $this->assertDatabaseHas('users', ['id' => $user->id]);

        $this->actingAs($user)
            ->delete(route('settings.account.destroy'), [
                'delete_password' => 'konfirmasi-hapus',
            ])
            ->assertRedirect(route('projects.index'));

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_non_discoverable_user_is_hidden_from_creator_directory_and_search(): void
    {
        $visible = User::factory()->create(['name' => 'Visible Dev', 'username' => 'visible_dev']);
        $hidden = User::factory()->create([
            'name' => 'Hidden Ghost',
            'username' => 'hidden_ghost',
            'preferences' => ['privacy' => ['discoverable' => false]],
        ]);

        $this->actingAs($visible)
            ->get(route('projects.index', ['view' => 'people']))
            ->assertOk()
            ->assertSee('Visible Dev')
            ->assertDontSee('Hidden Ghost');

        $this->actingAs($visible)
            ->get(route('search.index', ['q' => 'hidden_ghost', 'tab' => 'orang']))
            ->assertOk()
            ->assertDontSee('Hidden Ghost');
    }

    public function test_disabled_project_comment_notification_suppresses_notification(): void
    {
        $owner = User::factory()->create([
            'preferences' => ['notifications' => ['project_comment' => false]],
        ]);
        $commenter = User::factory()->create();
        $category = Category::create(['name' => 'Web App', 'slug' => 'web-app']);
        $project = Project::create([
            'user_id' => $owner->id,
            'category_id' => $category->id,
            'title' => 'Quiet Project',
            'slug' => 'quiet-project',
            'tagline' => 'Testing notification opt-out',
            'tech_stacks' => ['Laravel'],
            'visibility' => 'public',
        ]);

        $this->actingAs($commenter)
            ->postJson(route('projects.comment', $project), [
                'content' => 'Komentar baru yang tidak memicu notif',
            ])
            ->assertOk();

        $this->assertSame(0, $owner->notifications()->count());
    }

    public function test_disabled_read_receipts_hides_read_at_in_chat_seed(): void
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create([
            'preferences' => ['privacy' => ['show_read_receipts' => false]],
        ]);

        $conversation = Conversation::create([
            'user_one_id' => min($sender->id, $receiver->id),
            'user_two_id' => max($sender->id, $receiver->id),
            'initiator_id' => $sender->id,
            'status' => 'accepted',
            'last_message_at' => now(),
        ]);

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $sender->id,
            'body' => 'Halo bro',
            'read_at' => now(),
        ]);

        $response = $this->actingAs($sender)
            ->get(route('chat.show', $receiver->username))
            ->assertOk();

        $this->assertStringContainsString('"read_at":null', $response->getContent());
    }
}
