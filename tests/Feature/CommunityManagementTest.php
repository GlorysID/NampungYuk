<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\CommunityBan;
use App\Models\CommunityJoinRequest;
use App\Models\CommunityMember;
use App\Models\CommunityPost;
use App\Models\User;
use App\Notifications\CommunityMembershipNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CommunityManagementTest extends TestCase
{
    use RefreshDatabase;

    private function makeCommunity(User $owner, array $overrides = []): Community
    {
        $community = Community::create(array_merge([
            'owner_id' => $owner->id,
            'name' => 'Laravel Builders',
            'topic' => 'Laravel',
            'slug' => 'laravel-builders',
            'description' => 'Komunitas developer Laravel Indonesia',
            'visibility' => 'public',
            'join_policy' => 'open',
            'post_policy' => 'all_members',
            'members_count' => 1,
        ], $overrides));

        CommunityMember::create([
            'community_id' => $community->id,
            'user_id' => $owner->id,
            'role' => 'owner',
        ]);

        return $community;
    }

    public function test_role_authorization_boundaries_for_manage_routes(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create();
        $mod = User::factory()->create();
        $member = User::factory()->create();
        $outsider = User::factory()->create();

        $community = $this->makeCommunity($owner);

        CommunityMember::create(['community_id' => $community->id, 'user_id' => $admin->id, 'role' => 'admin']);
        CommunityMember::create(['community_id' => $community->id, 'user_id' => $mod->id, 'role' => 'mod']);
        CommunityMember::create(['community_id' => $community->id, 'user_id' => $member->id, 'role' => 'member']);
        $community->update(['members_count' => 4]);

        // Outsider & plain member cannot open management dashboard.
        $this->actingAs($outsider)->get(route('communities.manage.index', $community->slug))->assertStatus(403);
        $this->actingAs($member)->get(route('communities.manage.index', $community->slug))->assertStatus(403);

        // Moderator can open queue tab, but cannot open settings tab or ban/kick/update settings.
        $this->actingAs($mod)->get(route('communities.manage.index', $community->slug))->assertOk();
        $this->actingAs($mod)->get(route('communities.manage.index', ['community' => $community->slug, 'tab' => 'pengaturan']))->assertStatus(403);
        $this->actingAs($mod)->put(route('communities.manage.settings.update', $community->slug), [
            'name' => 'Hacked Name',
            'visibility' => 'public',
            'join_policy' => 'open',
            'post_policy' => 'all_members',
        ])->assertStatus(403);
        $this->actingAs($mod)->post(route('communities.manage.members.ban', [$community, $member]))->assertStatus(403);

        // Admin can open settings, manage mod/member roles, kick/ban members,
        // but cannot promote someone to admin, transfer ownership, or delete community.
        $this->actingAs($admin)->get(route('communities.manage.index', ['community' => $community->slug, 'tab' => 'pengaturan']))->assertOk();
        $this->actingAs($admin)->post(route('communities.manage.members.role', [$community, $member]), ['role' => 'admin'])->assertStatus(403);
        $this->actingAs($admin)->post(route('communities.manage.transfer', $community), ['new_owner_id' => $admin->id])->assertStatus(403);
        $this->actingAs($admin)->delete(route('communities.manage.destroy', $community), ['confirm_name' => $community->name])->assertStatus(403);

        // Admin CAN promote member -> mod.
        $this->actingAs($admin)
            ->post(route('communities.manage.members.role', [$community, $member]), ['role' => 'mod'])
            ->assertRedirect();
        $this->assertDatabaseHas('community_members', [
            'community_id' => $community->id,
            'user_id' => $member->id,
            'role' => 'mod',
        ]);
    }

    public function test_join_approval_workflow_with_questions_approve_reject_and_cancel(): void
    {
        Notification::fake();

        $owner = User::factory()->create();
        $applicantA = User::factory()->create();
        $applicantB = User::factory()->create();
        $applicantC = User::factory()->create();

        $community = $this->makeCommunity($owner, [
            'join_policy' => 'approval',
            'join_questions' => [
                'Apa stack utama kamu?',
                'Apa tujuan bergabung di grup ini?',
            ],
        ]);

        // Applicant A submits answers -> creates pending request, not yet a member.
        $this->actingAs($applicantA)
            ->post(route('communities.join', $community), [
                'answers' => ['Laravel + PostgreSQL', 'Belajar arsitektur sistem'],
                'note' => 'Salam kenal!',
            ])
            ->assertRedirect();

        $reqA = CommunityJoinRequest::where('community_id', $community->id)
            ->where('user_id', $applicantA->id)
            ->firstOrFail();

        $this->assertSame('pending', $reqA->status);
        $this->assertCount(2, $reqA->answers);
        $this->assertFalse($community->hasMember($applicantA->id));

        // Owner sees Applicant A in queue and approves.
        $this->actingAs($owner)
            ->get(route('communities.manage.index', $community->slug))
            ->assertOk()
            ->assertSee($applicantA->name)
            ->assertSee('Laravel + PostgreSQL');

        $this->actingAs($owner)
            ->post(route('communities.manage.requests.approve', [$community, $reqA]))
            ->assertRedirect();

        $this->assertTrue($community->hasMember($applicantA->id));
        $this->assertSame(2, $community->fresh()->members_count);
        $this->assertSame('approved', $reqA->fresh()->status);
        Notification::assertSentTo($applicantA, CommunityMembershipNotification::class);
        $this->assertDatabaseHas('community_audit_logs', [
            'community_id' => $community->id,
            'action' => 'join.approved',
            'target_user_id' => $applicantA->id,
        ]);

        // Applicant B submits and gets rejected.
        $this->actingAs($applicantB)->post(route('communities.join', $community), [
            'answers' => ['React', 'Cari proyek'],
        ]);
        $reqB = CommunityJoinRequest::where('community_id', $community->id)->where('user_id', $applicantB->id)->firstOrFail();

        $this->actingAs($owner)
            ->post(route('communities.manage.requests.reject', [$community, $reqB]), [
                'rejection_reason' => 'Jawaban terlalu singkat',
            ])
            ->assertRedirect();

        $this->assertSame('rejected', $reqB->fresh()->status);
        $this->assertSame('Jawaban terlalu singkat', $reqB->fresh()->rejection_reason);
        $this->assertFalse($community->hasMember($applicantB->id));
        Notification::assertSentTo($applicantB, CommunityMembershipNotification::class);

        // Applicant C submits and cancels own request.
        $this->actingAs($applicantC)->post(route('communities.join', $community), [
            'answers' => ['Vue', 'Diskusi'],
        ]);
        $this->actingAs($applicantC)
            ->delete(route('communities.join.cancel', $community))
            ->assertRedirect();
        $this->assertDatabaseMissing('community_join_requests', [
            'community_id' => $community->id,
            'user_id' => $applicantC->id,
        ]);
    }

    public function test_private_community_shows_preview_gate_without_leaking_posts_or_members(): void
    {
        $owner = User::factory()->create();
        $outsider = User::factory()->create();

        $community = $this->makeCommunity($owner, [
            'visibility' => 'private',
            'join_policy' => 'approval',
        ]);

        CommunityPost::create([
            'community_id' => $community->id,
            'user_id' => $owner->id,
            'type' => 'discussion',
            'status' => 'approved',
            'content' => 'Rahasia internal hanya untuk anggota privat',
        ]);

        $this->actingAs($outsider)
            ->get(route('communities.show', $community->slug))
            ->assertOk()
            ->assertSee('Komunitas Private')
            ->assertDontSee('Rahasia internal hanya untuk anggota privat');
    }

    public function test_post_approval_and_admins_only_policies(): void
    {
        Notification::fake();

        $owner = User::factory()->create();
        $mod = User::factory()->create();
        $member = User::factory()->create();

        $community = $this->makeCommunity($owner, [
            'post_policy' => 'approval_required',
        ]);
        CommunityMember::create(['community_id' => $community->id, 'user_id' => $mod->id, 'role' => 'mod']);
        CommunityMember::create(['community_id' => $community->id, 'user_id' => $member->id, 'role' => 'member']);

        // Member post goes to pending queue.
        $this->actingAs($member)
            ->post(route('communities.post', $community), [
                'content' => 'Postingan menunggu persetujuan',
                'type' => 'discussion',
            ])
            ->assertRedirect();

        $pendingPost = CommunityPost::where('community_id', $community->id)->where('user_id', $member->id)->firstOrFail();
        $this->assertSame('pending', $pendingPost->status);
        $this->assertSame(0, $community->fresh()->posts_count);

        // Pending post is hidden from public community feed and fragment.
        $this->get(route('feeds.community', $community->slug))
            ->assertOk()
            ->assertDontSee('Postingan menunggu persetujuan');

        // Moderator post skips approval and publishes immediately.
        $this->actingAs($mod)
            ->post(route('communities.post', $community), [
                'content' => 'Pengumuman resmi moderator',
                'type' => 'announcement',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('community_posts', [
            'community_id' => $community->id,
            'user_id' => $mod->id,
            'status' => 'approved',
        ]);
        $this->assertSame(1, $community->fresh()->posts_count);

        // Moderator approves pending post -> status approved, visible, posts_count increments.
        $this->actingAs($mod)
            ->post(route('communities.manage.posts.approve', [$community, $pendingPost]))
            ->assertRedirect();

        $this->assertSame('approved', $pendingPost->fresh()->status);
        $this->assertSame(2, $community->fresh()->posts_count);
        $this->get(route('feeds.community', $community->slug))
            ->assertOk()
            ->assertSee('Postingan menunggu persetujuan');

        // Switch policy to admins_only -> member cannot create new posts.
        $community->update(['post_policy' => 'admins_only']);
        $this->actingAs($member)
            ->post(route('communities.post', $community), [
                'content' => 'Percobaan posting saat admins_only',
            ])
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('community_posts', [
            'community_id' => $community->id,
            'content' => 'Percobaan posting saat admins_only',
        ]);
    }

    public function test_comment_policies_and_post_lock_enforcement(): void
    {
        $owner = User::factory()->create();
        $mod = User::factory()->create();
        $member = User::factory()->create();

        $community = $this->makeCommunity($owner);
        CommunityMember::create(['community_id' => $community->id, 'user_id' => $mod->id, 'role' => 'mod']);
        CommunityMember::create(['community_id' => $community->id, 'user_id' => $member->id, 'role' => 'member']);

        $post = CommunityPost::create([
            'community_id' => $community->id,
            'user_id' => $owner->id,
            'type' => 'discussion',
            'status' => 'approved',
            'content' => 'Diskusi arsitektur',
        ]);

        // Moderator locks post -> member receives 403, mod can still comment.
        $this->actingAs($mod)->post(route('communities.post.lock', $post))->assertRedirect();
        $this->assertTrue($post->fresh()->is_locked);

        $this->actingAs($member)
            ->postJson(route('communities.post.comment', $post), ['content' => 'Komentar saat terkunci'])
            ->assertStatus(403);

        $this->actingAs($mod)
            ->postJson(route('communities.post.comment', $post), ['content' => 'Komentar moderator'])
            ->assertOk();

        // Unlock post, then disable allow_member_comments globally -> member blocked again.
        $this->actingAs($mod)->post(route('communities.post.lock', $post))->assertRedirect();
        $community->update(['allow_member_comments' => false]);

        $this->actingAs($member)
            ->postJson(route('communities.post.comment', $post), ['content' => 'Komentar saat komentar global mati'])
            ->assertStatus(403);
    }

    public function test_mute_kick_ban_and_unban_member_lifecycle(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();

        $community = $this->makeCommunity($owner, ['members_count' => 2]);
        CommunityMember::create(['community_id' => $community->id, 'user_id' => $member->id, 'role' => 'member']);

        $post = CommunityPost::create([
            'community_id' => $community->id,
            'user_id' => $owner->id,
            'type' => 'discussion',
            'status' => 'approved',
            'content' => 'Diskusi terbuka',
        ]);

        // Mute member for 24h -> cannot post or comment.
        $this->actingAs($owner)
            ->post(route('communities.manage.members.mute', [$community, $member]), [
                'duration' => '24h',
                'reason' => 'Spam link berulang',
            ])
            ->assertRedirect();

        $this->assertTrue($community->isMuted($member->id));

        $this->actingAs($member)
            ->post(route('communities.post', $community), ['content' => 'Coba posting saat mute'])
            ->assertSessionHas('error');

        $this->actingAs($member)
            ->postJson(route('communities.post.comment', $post), ['content' => 'Coba komen saat mute'])
            ->assertStatus(403);

        // Unmute -> can comment again.
        $this->actingAs($owner)
            ->post(route('communities.manage.members.mute', [$community, $member]), ['duration' => 'unmute'])
            ->assertRedirect();
        $this->assertFalse($community->isMuted($member->id));

        $this->actingAs($member)
            ->postJson(route('communities.post.comment', $post), ['content' => 'Komentar setelah unmute'])
            ->assertOk();

        // Kick -> removes membership and decrements members_count.
        $this->actingAs($owner)
            ->delete(route('communities.manage.members.kick', [$community, $member]))
            ->assertRedirect();
        $this->assertFalse($community->hasMember($member->id));
        $this->assertSame(1, $community->fresh()->members_count);

        // Member rejoins, then gets banned -> blocks join, vote, and comment.
        $this->actingAs($member)->postJson(route('communities.join', $community))->assertOk();
        $this->assertSame(2, $community->fresh()->members_count);

        $this->actingAs($owner)
            ->post(route('communities.manage.members.ban', [$community, $member]), [
                'reason' => 'Melanggar aturan grup',
            ])
            ->assertRedirect();

        $this->assertFalse($community->hasMember($member->id));
        $this->assertTrue($community->isBanned($member->id));
        $this->assertSame(1, $community->fresh()->members_count);
        $this->assertDatabaseHas('community_bans', [
            'community_id' => $community->id,
            'user_id' => $member->id,
            'reason' => 'Melanggar aturan grup',
        ]);

        $this->actingAs($member)->postJson(route('communities.join', $community))->assertStatus(403);
        $this->actingAs($member)->postJson(route('communities.post.vote', $post))->assertStatus(403);

        // Unban -> user can join again.
        $this->actingAs($owner)
            ->delete(route('communities.manage.bans.remove', [$community, $member]))
            ->assertRedirect();

        $this->assertFalse(CommunityBan::where('community_id', $community->id)->where('user_id', $member->id)->exists());
        $this->actingAs($member)->postJson(route('communities.join', $community))->assertOk();
        $this->assertTrue($community->hasMember($member->id));
    }

    public function test_admin_can_update_settings_upload_media_transfer_ownership_and_owner_can_destroy(): void
    {
        Storage::fake('public');

        $owner = User::factory()->create();
        $admin = User::factory()->create();

        $community = $this->makeCommunity($owner, ['members_count' => 2]);
        CommunityMember::create(['community_id' => $community->id, 'user_id' => $admin->id, 'role' => 'admin']);

        $this->actingAs($admin)
            ->put(route('communities.manage.settings.update', $community->slug), [
                'name' => 'Laravel Pro Indonesia',
                'topic' => 'Backend & Architecture',
                'location' => 'Bandung, Indonesia',
                'website_url' => 'https://laravel.id',
                'description' => 'Wadah diskusi arsitektur Laravel.',
                'welcome_message' => 'Selamat datang di Laravel Pro!',
                'visibility' => 'private',
                'join_policy' => 'open', // should be forced to approval because private
                'post_policy' => 'approval_required',
                'allow_member_comments' => '1',
                'allow_member_images' => '0',
                'icon_file' => UploadedFile::fake()->image('icon.png', 200, 200),
                'cover_file' => UploadedFile::fake()->image('cover.jpg', 1200, 400),
                'join_questions' => ['Pengalaman Laravel berapa lama?'],
                'rules_items' => [
                    ['title' => 'Sopansantun', 'description' => 'Hormati sesama anggota.'],
                    ['title' => 'No Spam', 'description' => ''],
                ],
            ])
            ->assertRedirect();

        $community->refresh();
        $this->assertSame('Laravel Pro Indonesia', $community->name);
        $this->assertSame('private', $community->visibility);
        $this->assertSame('approval', $community->join_policy);
        $this->assertSame('approval_required', $community->post_policy);
        $this->assertTrue($community->allow_member_comments);
        $this->assertFalse($community->allow_member_images);
        $this->assertNotNull($community->icon);
        $this->assertNotNull($community->cover);
        $this->assertCount(2, $community->structuredRules());

        // Transfer ownership to admin -> old owner becomes admin, new owner becomes owner.
        $this->actingAs($owner)
            ->post(route('communities.manage.transfer', $community), [
                'new_owner_id' => $admin->id,
            ])
            ->assertRedirect();

        $community->refresh();
        $this->assertSame($admin->id, $community->owner_id);
        $this->assertSame('owner', $community->roleOf($admin->id));
        $this->assertSame('admin', $community->roleOf($owner->id));

        // Destroy fails if confirm_name mismatches, succeeds when matched.
        $this->actingAs($admin)
            ->delete(route('communities.manage.destroy', $community), [
                'confirm_name' => 'Salah Nama',
            ])
            ->assertSessionHasErrors('confirm_name');

        $this->assertDatabaseHas('communities', ['id' => $community->id]);

        $this->actingAs($admin)
            ->delete(route('communities.manage.destroy', $community), [
                'confirm_name' => 'Laravel Pro Indonesia',
            ])
            ->assertRedirect(route('communities.index'));

        $this->assertDatabaseMissing('communities', ['id' => $community->id]);
    }

    public function test_welcome_message_renders_valid_alpine_handler_for_members(): void
    {
        $owner = User::factory()->create();
        $community = $this->makeCommunity($owner, [
            'welcome_message' => 'Selamat datang developer di komunitas ini!',
        ]);

        $this->actingAs($owner)
            ->get(route('communities.show', $community->slug))
            ->assertOk()
            ->assertSee('Pesan Sambutan')
            ->assertSee('Selamat datang developer di komunitas ini!')
            ->assertSee('dismiss()', false)
            ->assertDontSee('x-init="try', false);
    }
}
