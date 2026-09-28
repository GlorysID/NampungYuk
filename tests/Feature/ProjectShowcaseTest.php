<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectShowcaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_project_feed(): void
    {
        $category = Category::create([
            'name' => 'Web App',
            'slug' => 'web-app',
        ]);

        $project = Project::create([
            'category_id' => $category->id,
            'title' => 'Project Keren',
            'slug' => 'project-keren',
            'tagline' => 'Tagline project keren banget',
            'tech_stacks' => ['Laravel', 'Vue'],
            'score' => 10,
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Project Keren');
        $response->assertSee('Web App');
    }

    public function test_guest_can_view_project_detail(): void
    {
        $category = Category::create(['name' => 'Open Source', 'slug' => 'open-source']);
        $creator = User::factory()->create(['name' => 'Rian Creator']);

        $project = Project::create([
            'user_id' => $creator->id,
            'category_id' => $category->id,
            'title' => 'Library Mantap',
            'slug' => 'library-mantap',
            'tagline' => 'Library PHP super kencang',
            'description' => 'Ini deskripsi lengkap library mantap.',
            'learnings' => 'Banyak belajar tentang benchmarking PHP.',
            'challenges' => 'Optimasi memori dan garbage collector.',
            'setup_instructions' => "composer require library/mantap\nphp artisan vendor:publish",
            'tech_stacks' => ['PHP', 'Composer'],
            'status' => 'production',
        ]);

        $response = $this->get('/project/'.$project->slug);

        $response->assertStatus(200);
        $response->assertSee('Library Mantap');
        $response->assertSee('Ini deskripsi lengkap library mantap.');
        $response->assertSee('Banyak belajar tentang benchmarking PHP.');
        $response->assertSee('Optimasi memori dan garbage collector.');
        $response->assertSee('Cara Menjalankan Project');
        $response->assertSee('composer require library/mantap');
        $response->assertSee('Rilis Publik');
    }

    public function test_guest_cannot_submit_project_and_is_redirected_to_login(): void
    {
        $category = Category::create(['name' => 'CLI Tools', 'slug' => 'cli-tools']);

        $response = $this->post('/unggah', [
            'title' => 'Guest Tool',
            'tagline' => 'Guest tagline',
            'category_id' => $category->id,
            'tech_stacks' => 'Python',
        ]);

        $response->assertRedirect('/login');
        $this->assertDatabaseMissing('projects', ['title' => 'Guest Tool']);
    }

    public function test_authenticated_user_can_upload_project_with_knowledge_fields(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'CLI Tools', 'slug' => 'cli-tools']);

        $response = $this->actingAs($user)->post('/unggah', [
            'title' => 'Nusantara CLI Tool',
            'tagline' => 'CLI cepat untuk database seeding',
            'category_id' => $category->id,
            'project_type' => 'developer_tool',
            'status' => 'beta',
            'tech_stacks' => 'Golang, Cobra, SQLite',
            'description' => 'Tool cli generasi terbaru',
            'challenges' => 'Membangun concurrent worker pool di Go',
            'learnings' => 'Memahami channel dan goroutines secara mendalam',
            'setup_instructions' => 'go install github.com/user/nusantara-cli@latest',
            'demo_url' => 'https://demo.example.com',
            'github_url' => 'https://github.com/user/nusantara-cli',
            'prototype_url' => 'https://figma.com/proto/nusantara-proto',
        ]);

        $response->assertRedirect('/project/nusantara-cli-tool');

        $this->assertDatabaseHas('projects', [
            'user_id' => $user->id,
            'title' => 'Nusantara CLI Tool',
            'slug' => 'nusantara-cli-tool',
            'status' => 'beta',
            'challenges' => 'Membangun concurrent worker pool di Go',
            'learnings' => 'Memahami channel dan goroutines secara mendalam',
            'setup_instructions' => 'go install github.com/user/nusantara-cli@latest',
            'prototype_url' => 'https://figma.com/proto/nusantara-proto',
        ]);
    }

    public function test_project_validation_works(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/unggah', [
            'title' => '',
            'tagline' => '',
            'category_id' => 99999, // Non-existent category
            'tech_stacks' => '',
            'demo_url' => 'not-a-valid-url',
        ]);

        $response->assertSessionHasErrors(['title', 'tagline', 'category_id', 'tech_stacks', 'demo_url']);
    }

    public function test_guest_cannot_vote_unauthorized(): void
    {
        $category = Category::create(['name' => 'AI', 'slug' => 'ai']);
        $project = Project::create([
            'category_id' => $category->id,
            'title' => 'AI Bot',
            'slug' => 'ai-bot',
            'tagline' => 'Bot canggih',
            'score' => 0,
        ]);

        $response = $this->postJson('/project/'.$project->id.'/vote', [
            'type' => 'up',
        ]);

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_upvote_and_creator_reputation_increases(): void
    {
        $creator = User::factory()->create(['reputation_points' => 10]);
        $voter = User::factory()->create();

        $category = Category::create(['name' => 'AI', 'slug' => 'ai']);
        $project = Project::create([
            'user_id' => $creator->id,
            'category_id' => $category->id,
            'title' => 'AI Assistant',
            'slug' => 'ai-assistant',
            'tagline' => 'Smart assistant',
            'score' => 0,
            'upvotes_count' => 0,
        ]);

        $response = $this->actingAs($voter)->postJson('/project/'.$project->id.'/vote', [
            'type' => 'up',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'score' => 1,
            'upvotes' => 1,
            'user_vote' => 'up',
        ]);

        $this->assertDatabaseHas('project_votes', [
            'project_id' => $project->id,
            'user_id' => $voter->id,
            'type' => 'up',
        ]);

        // Creator receives 5 reputation points
        $creator->refresh();
        $this->assertEquals(15, $creator->reputation_points);
    }

    public function test_authenticated_user_can_downvote(): void
    {
        $voter = User::factory()->create();
        $category = Category::create(['name' => 'Tools', 'slug' => 'tools']);
        $project = Project::create([
            'category_id' => $category->id,
            'title' => 'Tool Project',
            'slug' => 'tool-project',
            'tagline' => 'Tool tagline',
            'score' => 0,
            'downvotes_count' => 0,
        ]);

        $response = $this->actingAs($voter)->postJson('/project/'.$project->id.'/vote', [
            'type' => 'down',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'score' => -1,
            'downvotes' => 1,
            'user_vote' => 'down',
        ]);

        $project->refresh();
        $this->assertEquals(-1, $project->score);
        $this->assertEquals(1, $project->downvotes_count);
    }

    public function test_vote_can_be_removed_and_creator_reputation_syncs(): void
    {
        $creator = User::factory()->create(['reputation_points' => 20]);
        $voter = User::factory()->create();

        $category = Category::create(['name' => 'Tools', 'slug' => 'tools']);
        $project = Project::create([
            'user_id' => $creator->id,
            'category_id' => $category->id,
            'title' => 'Toggle Vote Project',
            'slug' => 'toggle-vote-project',
            'tagline' => 'Test toggle',
            'score' => 0,
            'upvotes_count' => 0,
        ]);

        // First vote (up)
        $this->actingAs($voter)->postJson('/project/'.$project->id.'/vote', ['type' => 'up']);
        $creator->refresh();
        $this->assertEquals(25, $creator->reputation_points);

        // Second vote (up again -> remove vote)
        $response = $this->actingAs($voter)->postJson('/project/'.$project->id.'/vote', ['type' => 'up']);
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'score' => 0,
            'upvotes' => 0,
            'user_vote' => null,
        ]);

        $this->assertDatabaseMissing('project_votes', [
            'project_id' => $project->id,
            'user_id' => $voter->id,
        ]);

        // Creator reputation reverted back to 20
        $creator->refresh();
        $this->assertEquals(20, $creator->reputation_points);
    }

    public function test_vote_can_be_switched_and_creator_reputation_syncs(): void
    {
        $creator = User::factory()->create(['reputation_points' => 50]);
        $voter = User::factory()->create();

        $category = Category::create(['name' => 'Tools', 'slug' => 'tools']);
        $project = Project::create([
            'user_id' => $creator->id,
            'category_id' => $category->id,
            'title' => 'Switch Vote Project',
            'slug' => 'switch-vote-project',
            'tagline' => 'Test switch',
            'score' => 0,
        ]);

        // 1. Upvote (+1 score, +5 creator rep)
        $this->actingAs($voter)->postJson('/project/'.$project->id.'/vote', ['type' => 'up']);
        $creator->refresh();
        $this->assertEquals(55, $creator->reputation_points);

        // 2. Switch to Downvote (score drops by 2 to -1, creator rep deducted by 5 to 50)
        $response = $this->actingAs($voter)->postJson('/project/'.$project->id.'/vote', ['type' => 'down']);
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'score' => -1,
            'user_vote' => 'down',
        ]);

        $creator->refresh();
        $this->assertEquals(50, $creator->reputation_points);
    }

    public function test_guest_cannot_comment_and_is_redirected_to_login(): void
    {
        $category = Category::create(['name' => 'Game Dev', 'slug' => 'game-dev']);
        $project = Project::create([
            'category_id' => $category->id,
            'title' => 'Game 2D',
            'slug' => 'game-2d',
            'tagline' => 'Game asik',
        ]);

        $response = $this->post('/project/'.$project->id.'/comment', [
            'content' => 'Komentar dari guest',
        ]);

        $response->assertRedirect('/login');
        $this->assertDatabaseMissing('project_comments', [
            'content' => 'Komentar dari guest',
        ]);
    }

    public function test_authenticated_user_can_comment_on_project(): void
    {
        $user = User::factory()->create(['name' => 'Arya Developer']);
        $category = Category::create(['name' => 'Game Dev', 'slug' => 'game-dev']);
        $project = Project::create([
            'category_id' => $category->id,
            'title' => 'Game 2D',
            'slug' => 'game-2d',
            'tagline' => 'Game asik',
            'comments_count' => 0,
        ]);

        $response = $this->actingAs($user)->post('/project/'.$project->id.'/comment', [
            'content' => 'Game-nya seru banget! Grafis pixelnya rapi.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('project_comments', [
            'project_id' => $project->id,
            'user_id' => $user->id,
            'content' => 'Game-nya seru banget! Grafis pixelnya rapi.',
        ]);

        $project->refresh();
        $this->assertEquals(1, $project->comments_count);
    }

    public function test_search_works_by_title_tech_and_creator(): void
    {
        $user = User::factory()->create(['name' => 'Sandhika Galih', 'username' => 'sandhikagalih']);
        $category = Category::create(['name' => 'Web App', 'slug' => 'web-app']);

        $project1 = Project::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'title' => 'WPU Course Portal',
            'slug' => 'wpu-course-portal',
            'tagline' => 'Portal belajar pemrograman gratis',
            'tech_stacks' => ['Next.js', 'PostgreSQL'],
        ]);

        $project2 = Project::create([
            'category_id' => $category->id,
            'title' => 'Mobile Banking UI',
            'slug' => 'mobile-banking-ui',
            'tagline' => 'Desain flutter fintech',
            'tech_stacks' => ['Flutter', 'Dart'],
        ]);

        // Search by Title
        $resp1 = $this->get('/?search=Portal');
        $resp1->assertStatus(200);
        $resp1->assertSee('WPU Course Portal');
        $resp1->assertDontSee('Mobile Banking UI');

        // Search by Tech Stack
        $resp2 = $this->get('/?search=Flutter');
        $resp2->assertStatus(200);
        $resp2->assertSee('Mobile Banking UI');
        $resp2->assertDontSee('WPU Course Portal');

        // Search by Creator Username
        $resp3 = $this->get('/?search=sandhikagalih');
        $resp3->assertStatus(200);
        $resp3->assertSee('WPU Course Portal');
    }

    public function test_user_can_filter_by_category(): void
    {
        $cat1 = Category::create(['name' => 'Web App', 'slug' => 'web-app']);
        $cat2 = Category::create(['name' => 'Mobile App', 'slug' => 'mobile-app']);

        Project::create([
            'category_id' => $cat1->id,
            'title' => 'Web Laravel',
            'slug' => 'web-laravel',
            'tagline' => 'Web keren',
            'tech_stacks' => ['Laravel'],
        ]);

        Project::create([
            'category_id' => $cat2->id,
            'title' => 'Mobile Flutter',
            'slug' => 'mobile-flutter',
            'tagline' => 'App flutter',
            'tech_stacks' => ['Flutter'],
        ]);

        $response = $this->get('/?kategori=web-app');
        $response->assertStatus(200);
        $response->assertSee('Web Laravel');
        $response->assertDontSee('Mobile Flutter');
    }

    public function test_user_can_filter_feed_by_prototype_tab(): void
    {
        $category = Category::create(['name' => 'Web App', 'slug' => 'web-app']);

        $protoProject = Project::create([
            'category_id' => $category->id,
            'title' => 'Figma Prototype Project',
            'slug' => 'figma-prototype-project',
            'tagline' => 'Prototype ready',
            'prototype_url' => 'https://figma.com/proto/12345',
        ]);

        $nonProtoProject = Project::create([
            'category_id' => $category->id,
            'title' => 'Code Only Project',
            'slug' => 'code-only-project',
            'tagline' => 'No prototype',
            'prototype_url' => null,
        ]);

        $response = $this->get('/?tab=prototype');
        $response->assertStatus(200);
        $this->assertTrue($response->viewData('projects')->pluck('id')->contains($protoProject->id));
        $this->assertFalse($response->viewData('projects')->pluck('id')->contains($nonProtoProject->id));
    }

    public function test_session_based_view_counting_prevents_inflation(): void
    {
        $category = Category::create(['name' => 'Tools', 'slug' => 'tools']);
        $project = Project::create([
            'category_id' => $category->id,
            'title' => 'View Count Project',
            'slug' => 'view-count-project',
            'tagline' => 'Testing views',
            'views_count' => 0,
        ]);

        // First visit increments count to 1
        $this->get('/project/'.$project->slug);
        $project->refresh();
        $this->assertEquals(1, $project->views_count);

        // Second visit with same session does NOT increment views
        $this->get('/project/'.$project->slug);
        $project->refresh();
        $this->assertEquals(1, $project->views_count);
    }

    public function test_navbar_displays_guest_links_when_unauthenticated_and_auth_actions_when_logged_in(): void
    {
        // 1. Unauthenticated (Guest)
        $guestResponse = $this->get('/');
        $guestResponse->assertStatus(200);
        $guestResponse->assertSee('Masuk');
        $guestResponse->assertSee('Daftar');
        $guestResponse->assertDontSee('title="Shortcut Keyboard (?)"', false);
        $guestResponse->assertDontSee('title="Koleksi Project Tersimpan"', false);

        // 2. Authenticated
        $user = User::factory()->create();
        $authResponse = $this->actingAs($user)->get('/');
        $authResponse->assertStatus(200);
        $authResponse->assertSee('Pamer Kodingan');
        $authResponse->assertSee('title="Koleksi Project Tersimpan"', false);
        $authResponse->assertSee('title="Shortcut Keyboard (?)"', false);
    }

    public function test_sidebar_renders_navigation_categories_and_mobile_drawer_toggle(): void
    {
        $category = Category::create(['name' => 'AI Machine Learning', 'slug' => 'ai-ml']);

        $response = $this->get('/');
        $response->assertStatus(200);

        // Sidebar Navigation
        $response->assertSee('Menu Utama');
        $response->assertSee('Semua Karya');
        $response->assertSee('Trending');
        $response->assertSee('Prototipe Interaktif');
        $response->assertSee('Kategori Kodingan');
        $response->assertSee('Tech Stacks');
        $response->assertSee('AI Machine Learning');

        // Mobile Drawer Toggle
        $response->assertSee('aria-label="Buka Menu Sidebar"', false);
    }
}
