<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Project;
use App\Models\ProjectBookmark;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookmarkTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_bookmark_and_unbookmark_project(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'Web App', 'slug' => 'web-app']);
        $project = Project::create([
            'category_id' => $category->id,
            'title' => 'Project Bookmark Test',
            'slug' => 'project-bookmark-test',
            'tagline' => 'Tagline test bookmark',
            'score' => 10,
        ]);

        // Toggle on
        $response = $this->actingAs($user)->postJson('/project/'.$project->id.'/bookmark');
        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'bookmarked' => true]);
        $this->assertDatabaseHas('project_bookmarks', [
            'project_id' => $project->id,
            'user_id' => $user->id,
        ]);

        // Toggle off
        $response = $this->actingAs($user)->postJson('/project/'.$project->id.'/bookmark');
        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'bookmarked' => false]);
        $this->assertDatabaseMissing('project_bookmarks', [
            'project_id' => $project->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_guest_can_bookmark_project_via_ip(): void
    {
        $category = Category::create(['name' => 'Mobile', 'slug' => 'mobile']);
        $project = Project::create([
            'category_id' => $category->id,
            'title' => 'Guest Bookmark Test',
            'slug' => 'guest-bookmark-test',
            'tagline' => 'Guest bookmark tagline',
            'score' => 5,
        ]);

        $response = $this->postJson('/project/'.$project->id.'/bookmark');
        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'bookmarked' => true]);

        $this->assertDatabaseHas('project_bookmarks', [
            'project_id' => $project->id,
            'user_id' => null,
        ]);
    }

    public function test_user_can_view_bookmarked_projects_page(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'CLI Tools', 'slug' => 'cli-tools']);
        $project = Project::create([
            'category_id' => $category->id,
            'title' => 'Saved CLI Tool',
            'slug' => 'saved-cli-tool',
            'tagline' => 'Useful CLI tool',
            'score' => 20,
        ]);

        ProjectBookmark::create([
            'project_id' => $project->id,
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->get('/koleksi');
        $response->assertStatus(200);
        $response->assertSee('Saved CLI Tool');
        $response->assertSee('Koleksi Proyek Tersimpan');
    }
}
