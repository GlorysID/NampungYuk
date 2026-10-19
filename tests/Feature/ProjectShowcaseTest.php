<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Project;
use App\Models\ProjectComment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
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

        // Guest can view project detail without login wall
        $guestResponse = $this->get('/project/'.$project->slug);
        $guestResponse->assertStatus(200);
        $guestResponse->assertSee('Library Mantap');

        // Authenticated user can view detail
        $authResponse = $this->actingAs($creator)->get('/project/'.$project->slug);
        $authResponse->assertStatus(200);
        $authResponse->assertSee('Library Mantap');
        $authResponse->assertSee('Ini deskripsi lengkap library mantap.');
        $authResponse->assertSee('Banyak belajar tentang benchmarking PHP.');
        $authResponse->assertSee('Optimasi memori dan garbage collector.');
        $authResponse->assertSee('Cara Menjalankan Project');
        $authResponse->assertSee('composer require library/mantap');
        $authResponse->assertSee('Rilis Publik');
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
        $this->assertTrue($resp1->viewData('projects')->pluck('title')->contains('WPU Course Portal'));
        $this->assertFalse($resp1->viewData('projects')->pluck('title')->contains('Mobile Banking UI'));

        // Search by Tech Stack
        $resp2 = $this->get('/?search=Flutter');
        $resp2->assertStatus(200);
        $this->assertTrue($resp2->viewData('projects')->pluck('title')->contains('Mobile Banking UI'));
        $this->assertFalse($resp2->viewData('projects')->pluck('title')->contains('WPU Course Portal'));

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

        // Guest must be redirected to login
        $guestResponse = $this->get('/?kategori=web-app');
        $guestResponse->assertRedirect(route('login'));

        $guestTechResponse = $this->get('/?tech=Flutter');
        $guestTechResponse->assertRedirect(route('login'));

        // Authenticated user can filter
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('/?kategori=web-app');
        $response->assertStatus(200);
        $this->assertTrue($response->viewData('projects')->pluck('title')->contains('Web Laravel'));
        $this->assertFalse($response->viewData('projects')->pluck('title')->contains('Mobile Flutter'));
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
        $user = User::factory()->create();
        $category = Category::create(['name' => 'Tools', 'slug' => 'tools']);
        $project = Project::create([
            'category_id' => $category->id,
            'title' => 'View Count Project',
            'slug' => 'view-count-project',
            'tagline' => 'Testing views',
            'views_count' => 0,
        ]);

        // First visit increments count to 1
        $response = $this->actingAs($user)->get('/project/'.$project->slug);
        $response->assertOk();
        $project->refresh();
        $this->assertEquals(1, $project->views_count);

        // Second visit with same session does NOT increment views
        $this->actingAs($user)->get('/project/'.$project->slug);
        $project->refresh();
        $this->assertEquals(1, $project->views_count);
    }

    public function test_navbar_displays_guest_links_when_unauthenticated_and_auth_actions_when_logged_in(): void
    {
        // 1. Unauthenticated (Guest)
        $guestResponse = $this->get('/');
        $guestResponse->assertStatus(200);
        $guestResponse->assertSee('Masuk');
        $guestResponse->assertDontSee('Pamer Kodingan');

        // 2. Authenticated
        $user = User::factory()->create();
        $authResponse = $this->actingAs($user)->get('/');
        $authResponse->assertStatus(200);
        $authResponse->assertSee('Pamer Kodingan');
        $authResponse->assertSee('Koleksi Tersimpan');
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

    public function test_people_search_view_lists_matching_accounts(): void
    {
        $viewer = User::factory()->create();
        User::factory()->create(['name' => 'Budi Santoso', 'username' => 'budi_laravel']);
        User::factory()->create(['name' => 'Siti Rahma', 'username' => 'siti_ui']);

        $response = $this->actingAs($viewer)->get('/?view=people&q=budi');

        $response->assertStatus(200);
        $response->assertSee('Akun');
        $response->assertSee('budi_laravel');
        $response->assertDontSee('siti_ui');
    }

    public function test_people_search_matches_by_username_fragment(): void
    {
        $viewer = User::factory()->create();
        User::factory()->create(['name' => 'Arif Wibowo', 'username' => 'arya_gamedev']);

        $response = $this->actingAs($viewer)->get('/?view=people&q=gamedev');

        $response->assertStatus(200);
        $response->assertSee('arya_gamedev');
    }

    public function test_people_view_without_query_browses_all_accounts(): void
    {
        $viewer = User::factory()->create(['name' => 'Viewer One', 'username' => 'viewer_one']);
        User::factory()->create(['name' => 'Someone Else', 'username' => 'someone_else']);

        $response = $this->actingAs($viewer)->get('/?view=people');

        $response->assertStatus(200);
        $response->assertSee('someone_else');
    }

    public function test_people_search_with_no_match_shows_empty_state(): void
    {
        $viewer = User::factory()->create();

        $response = $this->actingAs($viewer)->get('/?view=people&q=zzz_no_such_account_zzz');

        $response->assertStatus(200);
        $response->assertSee('Akun tidak ditemukan');
    }

    public function test_author_can_view_edit_project_page(): void
    {
        $creator = User::factory()->create(['name' => 'Bambang Dev']);
        $category = Category::create(['name' => 'Web App', 'slug' => 'web-app']);

        $project = Project::create([
            'user_id' => $creator->id,
            'category_id' => $category->id,
            'title' => 'Sistem Inventaris',
            'slug' => 'sistem-inventaris',
            'tagline' => 'Aplikasi pencatatan stok gudang',
            'tech_stacks' => ['Laravel', 'Alpine.js'],
        ]);

        $response = $this->actingAs($creator)->get(route('projects.edit', $project->slug));

        $response->assertStatus(200);
        $response->assertSee('Edit Project:');
        $response->assertSee('Sistem Inventaris');
        $response->assertSee('Aplikasi pencatatan stok gudang');
    }

    public function test_non_author_cannot_edit_another_users_project(): void
    {
        $creator = User::factory()->create();
        $intruder = User::factory()->create();
        $category = Category::create(['name' => 'Mobile', 'slug' => 'mobile']);

        $project = Project::create([
            'user_id' => $creator->id,
            'category_id' => $category->id,
            'title' => 'Project Rahasia',
            'slug' => 'project-rahasia',
            'tagline' => 'Rahasia creator',
            'tech_stacks' => ['Flutter'],
        ]);

        $response = $this->actingAs($intruder)->get(route('projects.edit', $project->slug));
        $response->assertStatus(403);

        $updateResponse = $this->actingAs($intruder)->put(route('projects.update', $project->slug), [
            'title' => 'Hacked Project',
            'tagline' => 'Tagline baru',
            'category_id' => $category->id,
            'tech_stacks' => 'Hacking',
        ]);
        $updateResponse->assertStatus(403);
    }

    public function test_author_can_update_project(): void
    {
        $creator = User::factory()->create();
        $category1 = Category::create(['name' => 'Frontend', 'slug' => 'frontend']);
        $category2 = Category::create(['name' => 'Backend', 'slug' => 'backend']);

        $project = Project::create([
            'user_id' => $creator->id,
            'category_id' => $category1->id,
            'title' => 'Judul Lama',
            'slug' => 'judul-lama',
            'tagline' => 'Tagline lama',
            'tech_stacks' => ['HTML', 'CSS'],
        ]);

        $response = $this->actingAs($creator)->put(route('projects.update', $project->slug), [
            'title' => 'Judul Baru Keren',
            'tagline' => 'Tagline yang sudah disempurnakan',
            'category_id' => $category2->id,
            'tech_stacks' => 'Laravel, PostgreSQL, Docker',
            'description' => 'Deskripsi terbaru tentang arsitektur backend.',
            'status' => 'production',
        ]);

        $response->assertRedirect(route('projects.show', $project->slug));
        $response->assertSessionHas('success');

        $project->refresh();
        $this->assertSame('Judul Baru Keren', $project->title);
        $this->assertSame('Tagline yang sudah disempurnakan', $project->tagline);
        $this->assertSame($category2->id, $project->category_id);
        $this->assertSame(['Laravel', 'PostgreSQL', 'Docker'], $project->tech_stacks);
    }

    public function test_author_can_delete_project(): void
    {
        $creator = User::factory()->create(['username' => 'creator_budi']);
        $category = Category::create(['name' => 'AI/ML', 'slug' => 'ai-ml']);

        $project = Project::create([
            'user_id' => $creator->id,
            'category_id' => $category->id,
            'title' => 'Model NLP',
            'slug' => 'model-nlp',
            'tagline' => 'Klasifikasi teks bahasa Indonesia',
            'tech_stacks' => ['Python', 'PyTorch'],
        ]);

        $response = $this->actingAs($creator)->delete(route('projects.destroy', $project->slug));

        $response->assertRedirect(route('profile.show', 'creator_budi'));
        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }

    public function test_comment_author_and_project_owner_can_delete_comment(): void
    {
        $projectOwner = User::factory()->create();
        $commentAuthor = User::factory()->create();
        $stranger = User::factory()->create();
        $category = Category::create(['name' => 'DevOps', 'slug' => 'devops']);

        $project = Project::create([
            'user_id' => $projectOwner->id,
            'category_id' => $category->id,
            'title' => 'CI/CD Pipeline',
            'slug' => 'cicd-pipeline',
            'tagline' => 'Automated deploy pipeline',
            'tech_stacks' => ['Docker', 'GitHub Actions'],
            'comments_count' => 1,
        ]);

        $comment = ProjectComment::create([
            'project_id' => $project->id,
            'user_id' => $commentAuthor->id,
            'content' => 'Komentar pertama yang keren!',
        ]);

        // Stranger cannot delete comment
        $strangerResponse = $this->actingAs($stranger)->delete(route('projects.comment.destroy', $comment->id));
        $strangerResponse->assertStatus(403);
        $this->assertDatabaseHas('project_comments', ['id' => $comment->id]);

        // Comment author can delete comment
        $authorResponse = $this->actingAs($commentAuthor)->delete(route('projects.comment.destroy', $comment->id));
        $authorResponse->assertStatus(302);
        $this->assertDatabaseMissing('project_comments', ['id' => $comment->id]);

        // Test project owner can also delete comments on their project
        $comment2 = ProjectComment::create([
            'project_id' => $project->id,
            'user_id' => $commentAuthor->id,
            'content' => 'Komentar kedua untuk ditest!',
        ]);

        $ownerResponse = $this->actingAs($projectOwner)->delete(route('projects.comment.destroy', $comment2->id));
        $ownerResponse->assertStatus(302);
        $this->assertDatabaseMissing('project_comments', ['id' => $comment2->id]);
    }

    public function test_user_can_update_their_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password-123'),
        ]);

        $response = $this->actingAs($user)->put(route('profile.password.update'), [
            'current_password' => 'old-password-123',
            'password' => 'new-secret-password-456',
            'password_confirmation' => 'new-secret-password-456',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertTrue(Hash::check('new-secret-password-456', $user->password));
    }

    public function test_user_cannot_update_password_with_incorrect_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password-123'),
        ]);

        $response = $this->actingAs($user)->put(route('profile.password.update'), [
            'current_password' => 'wrong-current-password',
            'password' => 'new-secret-password-456',
            'password_confirmation' => 'new-secret-password-456',
        ]);

        $response->assertSessionHasErrors(['current_password']);

        $user->refresh();
        $this->assertTrue(Hash::check('old-password-123', $user->password));
    }
}
