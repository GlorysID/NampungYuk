<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Community;
use App\Models\CommunityPost;
use App\Models\CommunityPostComment;
use App\Models\Project;
use App\Models\ProjectComment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentThreadIntegrityTest extends TestCase
{
    use RefreshDatabase;

    private function project(string $title, string $slug): Project
    {
        $category = Category::firstOrCreate(['slug' => 'web-app'], ['name' => 'Web App']);

        return Project::create([
            'category_id' => $category->id,
            'title' => $title,
            'slug' => $slug,
            'tagline' => 'Tagline '.$title,
            'tech_stacks' => ['PHP'],
            'visibility' => 'public',
            'comments_count' => 0,
        ]);
    }

    public function test_project_reply_must_target_a_comment_in_the_same_thread(): void
    {
        $user = User::factory()->create();
        $projectX = $this->project('Proyek X', 'proyek-x');
        $projectY = $this->project('Proyek Y', 'proyek-y');

        $foreignParent = ProjectComment::create([
            'project_id' => $projectY->id,
            'user_id' => $user->id,
            'content' => 'Komentar di proyek Y',
        ]);

        $this->actingAs($user)
            ->post('/project/'.$projectX->id.'/comment', [
                'content' => 'Balasan nyasar',
                'parent_id' => $foreignParent->id,
            ])
            ->assertSessionHasErrors('parent_id');

        $this->assertDatabaseMissing('project_comments', ['content' => 'Balasan nyasar']);

        // Same-thread reply still works.
        $localParent = ProjectComment::create([
            'project_id' => $projectX->id,
            'user_id' => $user->id,
            'content' => 'Komentar di proyek X',
        ]);

        $this->actingAs($user)
            ->post('/project/'.$projectX->id.'/comment', [
                'content' => 'Balasan valid',
                'parent_id' => $localParent->id,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('project_comments', [
            'project_id' => $projectX->id,
            'parent_id' => $localParent->id,
            'content' => 'Balasan valid',
        ]);
    }

    public function test_community_reply_must_target_a_comment_in_the_same_post(): void
    {
        $user = User::factory()->create();

        $community = Community::create([
            'owner_id' => $user->id,
            'name' => 'Laravel Indo',
            'topic' => 'PHP',
            'slug' => 'laravel-indo',
            'visibility' => 'public',
            'posts_count' => 2,
        ]);

        $postA = CommunityPost::create([
            'community_id' => $community->id,
            'user_id' => $user->id,
            'type' => 'discussion',
            'content' => 'Post A',
        ]);

        $postB = CommunityPost::create([
            'community_id' => $community->id,
            'user_id' => $user->id,
            'type' => 'discussion',
            'content' => 'Post B',
        ]);

        $foreignParent = CommunityPostComment::create([
            'community_post_id' => $postB->id,
            'user_id' => $user->id,
            'content' => 'Komentar di post B',
        ]);

        $this->actingAs($user)
            ->post('/community-post/'.$postA->id.'/comment', [
                'content' => 'Balasan nyasar',
                'parent_id' => $foreignParent->id,
            ])
            ->assertSessionHasErrors('parent_id');

        $this->assertDatabaseMissing('community_post_comments', ['content' => 'Balasan nyasar']);

        $localParent = CommunityPostComment::create([
            'community_post_id' => $postA->id,
            'user_id' => $user->id,
            'content' => 'Komentar di post A',
        ]);

        $this->actingAs($user)
            ->post('/community-post/'.$postA->id.'/comment', [
                'content' => 'Balasan valid',
                'parent_id' => $localParent->id,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('community_post_comments', [
            'community_post_id' => $postA->id,
            'parent_id' => $localParent->id,
            'content' => 'Balasan valid',
        ]);
    }

    public function test_deleting_project_comment_deletes_multi_level_descendants_and_updates_count(): void
    {
        $owner = User::factory()->create();
        $commenter = User::factory()->create();
        $category = Category::firstOrCreate(['slug' => 'web-app'], ['name' => 'Web App']);

        $project = Project::create([
            'user_id' => $owner->id,
            'category_id' => $category->id,
            'title' => 'Proyek Nested Comment',
            'slug' => 'proyek-nested-comment',
            'tagline' => 'Testing nested comment deletion',
            'tech_stacks' => ['Laravel'],
            'visibility' => 'public',
            'comments_count' => 3,
        ]);

        $root = ProjectComment::create([
            'project_id' => $project->id,
            'user_id' => $commenter->id,
            'content' => 'Komentar level 1',
        ]);

        $child = ProjectComment::create([
            'project_id' => $project->id,
            'user_id' => $owner->id,
            'parent_id' => $root->id,
            'content' => 'Balasan level 2',
        ]);

        $grandchild = ProjectComment::create([
            'project_id' => $project->id,
            'user_id' => $commenter->id,
            'parent_id' => $child->id,
            'content' => 'Balasan level 3',
        ]);

        $this->actingAs($commenter)
            ->deleteJson(route('projects.comment.destroy', $root))
            ->assertOk()
            ->assertJson([
                'success' => true,
                'comments_count' => 0,
            ]);

        $this->assertDatabaseMissing('project_comments', ['id' => $root->id]);
        $this->assertDatabaseMissing('project_comments', ['id' => $child->id]);
        $this->assertDatabaseMissing('project_comments', ['id' => $grandchild->id]);
        $this->assertSame(0, (int) $project->fresh()->comments_count);
    }
}
