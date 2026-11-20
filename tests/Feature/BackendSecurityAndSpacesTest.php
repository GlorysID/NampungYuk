<?php

namespace Tests\Feature;

use App\Events\ProjectPublished;
use App\Events\SpaceSignal;
use App\Models\Category;
use App\Models\Community;
use App\Models\CommunityMember;
use App\Models\CommunityPost;
use App\Models\Project;
use App\Models\ProjectBookmark;
use App\Models\ProjectComment;
use App\Models\Space;
use App\Models\SpaceParticipant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class BackendSecurityAndSpacesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 1. Private community feed fragment returns 403 for non-members and 200 for members.
     */
    public function test_private_community_feed_fragment_requires_membership(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $nonMember = User::factory()->create();

        $community = Community::create([
            'owner_id' => $owner->id,
            'name' => 'Klub Rahasia',
            'topic' => 'Koding',
            'slug' => 'klub-rahasia',
            'visibility' => 'private',
        ]);

        CommunityMember::create([
            'community_id' => $community->id,
            'user_id' => $member->id,
            'role' => 'member',
        ]);

        // Guest access
        $this->get(route('feeds.community', $community->slug))
            ->assertStatus(403);

        // Non-member access
        $this->actingAs($nonMember)
            ->get(route('feeds.community', $community->slug))
            ->assertStatus(403);

        // Member access
        $this->actingAs($member)
            ->get(route('feeds.community', $community->slug))
            ->assertStatus(200);

        // Owner access
        $this->actingAs($owner)
            ->get(route('feeds.community', $community->slug))
            ->assertStatus(200);
    }

    /**
     * 2. Private project creation does NOT dispatch ProjectPublished event/follower notifications,
     * and non-owners get 403 when voting/commenting/bookmarking a private project.
     */
    public function test_private_project_creation_and_interaction_guards(): void
    {
        Event::fake([ProjectPublished::class]);
        Notification::fake();

        $category = Category::create(['name' => 'Web App', 'slug' => 'web-app']);
        $author = User::factory()->create();
        $follower = User::factory()->create();
        $stranger = User::factory()->create();

        // Follower follows author
        $follower->following()->attach($author->id);

        $response = $this->actingAs($author)->post(route('projects.store'), [
            'title' => 'Project Rahasia Saya',
            'tagline' => 'Hanya untuk saya',
            'category_id' => $category->id,
            'tech_stacks' => 'Laravel, Vue',
            'visibility' => 'private',
        ]);

        $response->assertRedirect();

        $project = Project::where('title', 'Project Rahasia Saya')->firstOrFail();
        $this->assertEquals('private', $project->visibility);

        // Events & notifications fake assertions
        Event::assertNotDispatched(ProjectPublished::class);
        Notification::assertNothingSent();

        // Non-owner interactions must be blocked with 403
        $this->actingAs($stranger)
            ->postJson(route('projects.vote', $project))
            ->assertStatus(403);

        $this->actingAs($stranger)
            ->postJson(route('projects.comment', $project), ['content' => 'Komentar intip'])
            ->assertStatus(403);

        $this->actingAs($stranger)
            ->postJson(route('projects.bookmark', $project))
            ->assertStatus(403);

        $this->actingAs($stranger)
            ->postJson(route('projects.repost', $project))
            ->assertStatus(403);
    }

    /**
     * 3. Deleting a parent comment with replies decrements comments_count by parent + replies count.
     */
    public function test_deleting_parent_comment_decrements_comments_count_by_total(): void
    {
        $category = Category::create(['name' => 'Web App', 'slug' => 'web-app']);
        $owner = User::factory()->create();
        $commenter = User::factory()->create();

        $project = Project::create([
            'user_id' => $owner->id,
            'category_id' => $category->id,
            'title' => 'Showcase Diskusi',
            'slug' => 'showcase-diskusi',
            'tagline' => 'tagline',
            'tech_stacks' => ['PHP'],
            'visibility' => 'public',
            'comments_count' => 3,
            'score' => 1,
        ]);

        $parent = ProjectComment::create([
            'project_id' => $project->id,
            'user_id' => $commenter->id,
            'content' => 'Parent comment',
        ]);

        ProjectComment::create([
            'project_id' => $project->id,
            'user_id' => $commenter->id,
            'parent_id' => $parent->id,
            'content' => 'Reply 1',
        ]);

        ProjectComment::create([
            'project_id' => $project->id,
            'user_id' => $commenter->id,
            'parent_id' => $parent->id,
            'content' => 'Reply 2',
        ]);

        $this->actingAs($commenter)
            ->deleteJson(route('projects.comment.destroy', $parent))
            ->assertStatus(200)
            ->assertJson(['success' => true, 'comments_count' => 0]);

        $project->refresh();
        $this->assertSame(0, $project->comments_count);
        $this->assertDatabaseMissing('project_comments', ['id' => $parent->id]);
        $this->assertSame(0, ProjectComment::where('project_id', $project->id)->count());
    }

    /**
     * 4. Community post author can delete their own post and posts_count decrements.
     */
    public function test_community_post_author_can_delete_own_post_and_posts_count_decrements(): void
    {
        $owner = User::factory()->create();
        $author = User::factory()->create();

        $community = Community::create([
            'owner_id' => $owner->id,
            'name' => 'Laravel Indo',
            'topic' => 'PHP',
            'slug' => 'laravel-indo',
            'visibility' => 'public',
            'posts_count' => 1,
        ]);

        CommunityMember::create([
            'community_id' => $community->id,
            'user_id' => $author->id,
            'role' => 'member',
        ]);

        $post = CommunityPost::create([
            'community_id' => $community->id,
            'user_id' => $author->id,
            'type' => 'discussion',
            'content' => 'Tanya seputar Laravel 13',
        ]);

        $this->actingAs($author)
            ->delete(route('communities.post.destroy', $post))
            ->assertRedirect();

        $this->assertDatabaseMissing('community_posts', ['id' => $post->id]);

        $community->refresh();
        $this->assertSame(0, $community->posts_count);
    }

    /**
     * 5. Space join, signal (participant vs non-participant 403), and ended space join rejection work via both slug and id.
     */
    public function test_space_join_signal_and_ended_rejection_work_by_slug_and_id(): void
    {
        $host = User::factory()->create();
        $listener = User::factory()->create();
        $stranger = User::factory()->create();

        $space = Space::create([
            'host_id' => $host->id,
            'title' => 'Kopi Sore Developer',
            'slug' => 'kopi-sore-developer',
            'type' => 'audio',
            'status' => 'live',
        ]);

        SpaceParticipant::create([
            'space_id' => $space->id,
            'user_id' => $host->id,
            'role' => 'host',
            'is_muted' => false,
        ]);

        // Join via numeric ID
        $this->actingAs($listener)
            ->postJson("/spaces/{$space->id}/join")
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('space_participants', [
            'space_id' => $space->id,
            'user_id' => $listener->id,
        ]);

        // Signal relay between participants via ID
        $this->actingAs($listener)
            ->postJson("/spaces/{$space->id}/signal", [
                'to' => $host->id,
                'kind' => 'offer',
                'payload' => ['sdp' => 'mock-sdp-data'],
            ])
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        // Non-participant gets 403 when trying to signal
        $this->actingAs($stranger)
            ->postJson("/spaces/{$space->slug}/signal", [
                'to' => $host->id,
                'kind' => 'offer',
                'payload' => ['sdp' => 'mock-sdp-data'],
            ])
            ->assertStatus(403);

        // End the space
        $space->update(['status' => 'ended']);

        // Joining ended space via slug fails with 422
        $this->actingAs($stranger)
            ->postJson("/spaces/{$space->slug}/join")
            ->assertStatus(422)
            ->assertJson(['message' => 'Space telah berakhir.']);

        // Joining ended space via numeric ID also fails with 422
        $this->actingAs($stranger)
            ->postJson("/spaces/{$space->id}/join")
            ->assertStatus(422)
            ->assertJson(['message' => 'Space telah berakhir.']);
    }

    /**
     * 6. Private community post vote and comment block non-members with 403.
     */
    public function test_private_community_post_vote_and_comment_block_non_members(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();

        $community = Community::create([
            'owner_id' => $owner->id,
            'name' => 'Secret Circle',
            'topic' => 'Security',
            'slug' => 'secret-circle',
            'visibility' => 'private',
        ]);

        CommunityMember::create([
            'community_id' => $community->id,
            'user_id' => $owner->id,
            'role' => 'admin',
        ]);

        $post = CommunityPost::create([
            'community_id' => $community->id,
            'user_id' => $owner->id,
            'type' => 'discussion',
            'content' => 'Internal security discussion',
        ]);

        $this->actingAs($stranger)
            ->postJson(route('communities.post.vote', $post))
            ->assertStatus(403);

        $this->actingAs($stranger)
            ->postJson(route('communities.post.comment', $post), ['content' => 'Intruder comment'])
            ->assertStatus(403);
    }

    /**
     * 7. Guests receive direct public project links on homepage and can open live space room without JS syntax errors.
     */
    public function test_guest_receives_direct_project_links_and_can_open_space_room(): void
    {
        $category = Category::create(['name' => 'CLI Tools', 'slug' => 'cli-tools']);
        $owner = User::factory()->create();

        $project = Project::create([
            'user_id' => $owner->id,
            'category_id' => $category->id,
            'title' => 'Public Rust CLI',
            'slug' => 'public-rust-cli',
            'tagline' => 'Fast CLI tool',
            'tech_stacks' => ['Rust'],
            'visibility' => 'public',
        ]);

        $space = Space::create([
            'host_id' => $owner->id,
            'title' => 'Rust Live Talk',
            'slug' => 'rust-live-talk',
            'type' => 'audio',
            'status' => 'live',
        ]);

        $homeResponse = $this->get('/');
        $homeResponse->assertOk();
        $homeResponse->assertSee(route('projects.show', $project->slug), false);
        $homeResponse->assertDontSee('href="javascript:void(0)"', false);

        $spaceResponse = $this->get(route('spaces.show', $space->slug));
        $spaceResponse->assertOk();
        $spaceResponse->assertSee('me: null', false);
    }

    /**
     * 8. /feed/latest, relatedProjects on show, and /koleksi hide private or warned projects from non-owners.
     */
    public function test_latest_feed_related_projects_and_bookmarks_enforce_visibility(): void
    {
        $category = Category::create(['name' => 'Backend', 'slug' => 'backend']);
        $owner = User::factory()->create();
        $viewer = User::factory()->create();

        $publicProject = Project::create([
            'user_id' => $owner->id,
            'category_id' => $category->id,
            'title' => 'Visible Public Service',
            'slug' => 'visible-public-service',
            'tagline' => 'Public tagline',
            'tech_stacks' => ['Go'],
            'visibility' => 'public',
        ]);

        $privateProject = Project::create([
            'user_id' => $owner->id,
            'category_id' => $category->id,
            'title' => 'Hidden Private Service',
            'slug' => 'hidden-private-service',
            'tagline' => 'Private tagline',
            'tech_stacks' => ['Go'],
            'visibility' => 'private',
            'score' => 99,
        ]);

        $warnedProject = Project::create([
            'user_id' => $owner->id,
            'category_id' => $category->id,
            'title' => 'Hidden Warned Service',
            'slug' => 'hidden-warned-service',
            'tagline' => 'Warned tagline',
            'tech_stacks' => ['Go'],
            'visibility' => 'public',
            'is_flagged' => true,
            'warning_notes' => 'Pelanggaran',
            'score' => 98,
        ]);

        // S1: /feed/latest must only expose visible projects
        $this->getJson(route('projects.latest'))
            ->assertOk()
            ->assertSee('Visible Public Service')
            ->assertDontSee('Hidden Private Service')
            ->assertDontSee('Hidden Warned Service');

        // S2: relatedProjects on detail page must not leak private or warned projects
        $this->actingAs($viewer)
            ->get(route('projects.show', $publicProject->slug))
            ->assertOk()
            ->assertDontSee('Hidden Private Service')
            ->assertDontSee('Hidden Warned Service');

        // S4: /koleksi hides previously bookmarked projects that turned private or warned
        ProjectBookmark::create(['project_id' => $publicProject->id, 'user_id' => $viewer->id]);
        ProjectBookmark::create(['project_id' => $privateProject->id, 'user_id' => $viewer->id]);
        ProjectBookmark::create(['project_id' => $warnedProject->id, 'user_id' => $viewer->id]);

        $this->actingAs($viewer)
            ->get(route('projects.bookmarks'))
            ->assertOk()
            ->assertSee('Visible Public Service')
            ->assertDontSee('Hidden Private Service')
            ->assertDontSee('Hidden Warned Service');
    }

    /**
     * 9. Creating a community rejects javascript: scheme on icon URL.
     */
    public function test_community_creation_rejects_javascript_icon_url(): void
    {
        $user = User::factory()->create(['is_admin' => true]);

        $this->actingAs($user)
            ->post(route('communities.store'), [
                'name' => 'Komunitas Uji XSS',
                'topic' => 'Keamanan',
                'icon' => 'javascript:alert(1)',
            ])
            ->assertSessionHasErrors('icon');

        $this->assertDatabaseMissing('communities', ['name' => 'Komunitas Uji XSS']);
    }

    /**
     * 10. Space chat signal succeeds for active participants and forbids non-participants.
     */
    public function test_space_chat_signal_succeeds_for_participant_and_forbids_non_participant(): void
    {
        Event::fake([SpaceSignal::class]);

        $host = User::factory()->create();
        $participant = User::factory()->create();
        $outsider = User::factory()->create();

        $space = Space::create([
            'host_id' => $host->id,
            'title' => 'Ruang Diskusi Live',
            'type' => 'audio',
            'status' => 'live',
        ]);

        SpaceParticipant::create([
            'space_id' => $space->id,
            'user_id' => $participant->id,
            'role' => 'listener',
            'is_muted' => true,
        ]);

        $this->actingAs($outsider)
            ->postJson(route('spaces.signal', $space), [
                'kind' => 'chat',
                'payload' => ['text' => 'Menyusup ke ruang'],
            ])
            ->assertStatus(403);

        $this->actingAs($participant)
            ->postJson(route('spaces.signal', $space), [
                'kind' => 'chat',
                'payload' => ['text' => 'Halo semuanya'],
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        Event::assertDispatched(SpaceSignal::class, function (SpaceSignal $event) use ($space, $participant): bool {
            return $event->spaceId === $space->id
                && $event->from === $participant->id
                && $event->kind === 'chat'
                && ($event->payload['text'] ?? null) === 'Halo semuanya';
        });
    }

    /**
     * 11. Space sync returns persisted chat/signaling events and participants for offline fallback,
     * and join/leave/end never crash HTTP 500 when Reverb is unreachable.
     */
    public function test_spaces_offline_sync_and_safe_broadcast_resilience(): void
    {
        config([
            'broadcasting.default' => 'reverb',
            'broadcasting.connections.reverb.options.host' => '127.0.0.1',
            'broadcasting.connections.reverb.options.port' => 65531,
        ]);

        $host = User::factory()->create(['name' => 'Host Lokal', 'is_admin' => true]);
        $guest = User::factory()->create(['name' => 'Peserta Lokal']);

        $this->actingAs($host)
            ->post(route('spaces.store'), [
                'title' => 'Diskusi Lokal Offline',
                'type' => 'audio',
            ])
            ->assertRedirect();

        $space = Space::where('title', 'Diskusi Lokal Offline')->firstOrFail();

        // Guest joins while Reverb is down — should succeed without HTTP 500.
        $this->actingAs($guest)
            ->postJson(route('spaces.join', $space->slug))
            ->assertOk()
            ->assertJson(['success' => true]);

        // Guest sends Space Chat message & direct WebRTC offer to host.
        $this->actingAs($guest)
            ->postJson(route('spaces.signal', $space->slug), [
                'kind' => 'chat',
                'payload' => ['text' => 'Tes offline sync'],
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->actingAs($guest)
            ->postJson(route('spaces.signal', $space->slug), [
                'to' => $host->id,
                'kind' => 'offer',
                'payload' => ['type' => 'offer', 'sdp' => 'dummy-sdp'],
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        // Host polls /sync and receives join + chat + offer signals + participant list.
        $syncRes = $this->actingAs($host)
            ->getJson(route('spaces.sync', $space->slug).'?after_id=0')
            ->assertOk()
            ->assertJsonPath('status', 'live')
            ->json();

        $kinds = array_column($syncRes['signals'] ?? [], 'kind');
        $this->assertContains('join', $kinds);
        $this->assertContains('chat', $kinds);
        $this->assertContains('offer', $kinds);
        $this->assertCount(2, $syncRes['participants'] ?? []);

        // Host renders /spaces/{slug} with initial messages and Alpine `type` prop.
        $this->actingAs($host)
            ->get(route('spaces.show', $space->slug))
            ->assertOk()
            ->assertSee('Tes offline sync')
            ->assertSee("type: 'audio'", false);

        // Leave & end while Reverb is still down succeed cleanly.
        $this->actingAs($guest)
            ->postJson(route('spaces.leave', $space->slug))
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->actingAs($host)
            ->post(route('spaces.end', $space->slug))
            ->assertRedirect(route('spaces.index'));

        $this->assertSame('ended', $space->fresh()->status);
    }

    /**
     * 12. Creating a Space requires recognized & credible creator criteria (or admin).
     */
    public function test_space_creation_requires_recognized_creator_or_admin(): void
    {
        $category = Category::create(['name' => 'Backend', 'slug' => 'backend']);

        $newbie = User::factory()->create([
            'reputation_points' => 10,
            'github_url' => null,
        ]);

        $this->actingAs($newbie)
            ->get(route('spaces.index'))
            ->assertOk()
            ->assertSee('Syarat Host Space');

        $this->actingAs($newbie)
            ->post(route('spaces.store'), [
                'title' => 'Space Ilegal',
                'type' => 'audio',
            ])
            ->assertStatus(403);

        $proCreator = User::factory()->create([
            'reputation_points' => Space::MIN_REPUTATION_TO_CREATE,
            'github_url' => 'https://github.com/pro-dev',
        ]);

        for ($i = 1; $i <= Space::MIN_PROJECTS_TO_CREATE; $i++) {
            Project::create([
                'user_id' => $proCreator->id,
                'category_id' => $category->id,
                'title' => "Pro Project {$i}",
                'slug' => "pro-project-{$i}",
                'tagline' => 'Public portfolio project',
                'tech_stacks' => ['Laravel'],
                'visibility' => 'public',
            ]);
        }

        $followers = User::factory()->count(Space::MIN_FOLLOWERS_TO_CREATE)->create();
        $proCreator->followers()->attach($followers->pluck('id')->all());

        $this->actingAs($proCreator)
            ->post(route('spaces.store'), [
                'title' => 'Bedah Arsitektur Laravel',
                'type' => 'audio',
                'description' => 'Diskusi terstruktur untuk developer.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('spaces', [
            'host_id' => $proCreator->id,
            'title' => 'Bedah Arsitektur Laravel',
            'status' => 'live',
        ]);
    }

    /**
     * 13. Structured X-Spaces flow: schedule/start, raise hand, approve/demote, co-host, force-mute, pin, and kick.
     */
    public function test_structured_space_stage_roles_moderation_and_schedule_flow(): void
    {
        $category = Category::create(['name' => 'Tools', 'slug' => 'tools']);
        $host = User::factory()->create(['is_admin' => true, 'name' => 'Host Utama']);
        $listenerA = User::factory()->create(['name' => 'Pendengar A']);
        $listenerB = User::factory()->create(['name' => 'Pendengar B']);

        $project = Project::create([
            'user_id' => $host->id,
            'category_id' => $category->id,
            'title' => 'Karya Bahasan Utama',
            'slug' => 'karya-bahasan-utama',
            'tagline' => 'Topik diskusi space',
            'tech_stacks' => ['PHP'],
            'visibility' => 'public',
        ]);

        // Host schedules a Space
        $this->actingAs($host)
            ->post(route('spaces.store'), [
                'title' => 'Space Terjadwal',
                'type' => 'audio',
                'start_mode' => 'scheduled',
                'scheduled_at' => now()->addHours(2)->format('Y-m-d H:i:s'),
                'pinned_project_id' => $project->id,
            ])
            ->assertRedirect();

        $space = Space::where('title', 'Space Terjadwal')->firstOrFail();
        $this->assertSame('scheduled', $space->status);
        $this->assertSame($project->id, $space->pinned_project_id);

        // Listener A joins lobby while scheduled
        $this->actingAs($listenerA)
            ->postJson(route('spaces.join', $space->slug))
            ->assertOk();

        // Non-host cannot start scheduled space
        $this->actingAs($listenerA)
            ->postJson(route('spaces.start', $space->slug))
            ->assertStatus(403);

        // Host starts the Space
        $this->actingAs($host)
            ->postJson(route('spaces.start', $space->slug))
            ->assertOk()
            ->assertJson(['status' => 'live']);

        // Listener B joins live space
        $this->actingAs($listenerB)
            ->postJson(route('spaces.join', $space->slug))
            ->assertOk();

        // Listener cannot unmute mic before stage approval
        $this->actingAs($listenerA)
            ->postJson(route('spaces.mic', $space->slug), ['muted' => false])
            ->assertStatus(403);

        // Listener A raises hand
        $this->actingAs($listenerA)
            ->postJson(route('spaces.request_speak', $space->slug))
            ->assertOk()
            ->assertJson(['hand_raised' => true]);

        // Non-moderator Listener B cannot approve Listener A
        $this->actingAs($listenerB)
            ->postJson(route('spaces.participant.role', ['space' => $space->slug, 'user' => $listenerA->id]), [
                'action' => 'approve_speaker',
            ])
            ->assertStatus(403);

        // Host approves Listener A to Speaker
        $this->actingAs($host)
            ->postJson(route('spaces.participant.role', ['space' => $space->slug, 'user' => $listenerA->id]), [
                'action' => 'approve_speaker',
            ])
            ->assertOk();

        $this->assertDatabaseHas('space_participants', [
            'space_id' => $space->id,
            'user_id' => $listenerA->id,
            'role' => 'speaker',
            'hand_raised_at' => null,
        ]);

        // Speaker A can now unmute own mic
        $this->actingAs($listenerA)
            ->postJson(route('spaces.mic', $space->slug), ['muted' => false])
            ->assertOk()
            ->assertJson(['is_muted' => false]);

        // Host force-mutes Speaker A -> Speaker A cannot unmute
        $this->actingAs($host)
            ->postJson(route('spaces.participant.role', ['space' => $space->slug, 'user' => $listenerA->id]), [
                'action' => 'mute',
            ])
            ->assertOk();

        $this->actingAs($listenerA)
            ->postJson(route('spaces.mic', $space->slug), ['muted' => false])
            ->assertStatus(403);

        // Host unmutes restriction & promotes Listener A to Co-host
        $this->actingAs($host)
            ->postJson(route('spaces.participant.role', ['space' => $space->slug, 'user' => $listenerA->id]), [
                'action' => 'promote_cohost',
            ])
            ->assertOk();

        // Co-host can promote Listener B to Speaker, but cannot promote another Co-host
        $this->actingAs($listenerA)
            ->postJson(route('spaces.participant.role', ['space' => $space->slug, 'user' => $listenerB->id]), [
                'action' => 'promote_speaker',
            ])
            ->assertOk();

        $this->actingAs($listenerA)
            ->postJson(route('spaces.participant.role', ['space' => $space->slug, 'user' => $listenerB->id]), [
                'action' => 'promote_cohost',
            ])
            ->assertStatus(403);

        // Speaker B steps down to listener voluntarily
        $this->actingAs($listenerB)
            ->postJson(route('spaces.participant.role', ['space' => $space->slug, 'user' => $listenerB->id]), [
                'action' => 'step_down',
            ])
            ->assertOk();

        $this->assertDatabaseHas('space_participants', [
            'space_id' => $space->id,
            'user_id' => $listenerB->id,
            'role' => 'listener',
        ]);

        // Co-host kicks Listener B and unpins project
        $this->actingAs($listenerA)
            ->postJson(route('spaces.participant.role', ['space' => $space->slug, 'user' => $listenerB->id]), [
                'action' => 'kick',
            ])
            ->assertOk();

        $this->assertDatabaseMissing('space_participants', [
            'space_id' => $space->id,
            'user_id' => $listenerB->id,
        ]);

        // Sync exposes pinned_project and structured participant roles
        $this->actingAs($host)
            ->getJson(route('spaces.sync', $space->slug).'?after_id=0')
            ->assertOk()
            ->assertJsonPath('status', 'live')
            ->assertJsonPath('pinned_project.slug', $project->slug);
    }
}
