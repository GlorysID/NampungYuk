<?php

namespace Tests\Feature;

use App\Events\ProjectPublished;
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
}
