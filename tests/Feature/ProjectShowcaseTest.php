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

    public function test_user_can_view_project_feed(): void
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

    public function test_user_can_view_project_detail(): void
    {
        $category = Category::create(['name' => 'Open Source', 'slug' => 'open-source']);
        $project = Project::create([
            'category_id' => $category->id,
            'title' => 'Library Mantap',
            'slug' => 'library-mantap',
            'tagline' => 'Library PHP super kencang',
            'description' => 'Ini deskripsi lengkap library mantap.',
            'tech_stacks' => ['PHP', 'Composer'],
        ]);

        $response = $this->get('/project/'.$project->slug);
        $response->assertStatus(200);
        $response->assertSee('Library Mantap');
        $response->assertSee('Ini deskripsi lengkap library mantap.');
    }

    public function test_user_can_vote_on_project(): void
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

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'score' => 1,
            'user_vote' => 'up',
        ]);

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'score' => 1,
        ]);
    }

    public function test_user_can_submit_project(): void
    {
        $category = Category::create(['name' => 'CLI Tools', 'slug' => 'cli-tools']);

        $response = $this->post('/unggah', [
            'title' => 'Nusantara CLI Tool',
            'tagline' => 'CLI cepat untuk database seeding',
            'category_id' => $category->id,
            'tech_stacks' => 'Golang, Cobra, SQLite',
            'description' => 'Tool cli generasi terbaru',
            'guest_name' => 'Fajar Programmer',
        ]);

        $response->assertRedirect('/project/nusantara-cli-tool');

        $this->assertDatabaseHas('projects', [
            'title' => 'Nusantara CLI Tool',
            'slug' => 'nusantara-cli-tool',
        ]);
    }

    public function test_user_can_comment_on_project(): void
    {
        $category = Category::create(['name' => 'Game Dev', 'slug' => 'game-dev']);
        $project = Project::create([
            'category_id' => $category->id,
            'title' => 'Game 2D',
            'slug' => 'game-2d',
            'tagline' => 'Game asik',
        ]);

        $response = $this->post('/project/'.$project->id.'/comment', [
            'content' => 'Game-nya seru banget! Grafis pixelnya rapi.',
            'guest_name' => 'GamerDev',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('project_comments', [
            'project_id' => $project->id,
            'content' => 'Game-nya seru banget! Grafis pixelnya rapi.',
            'guest_name' => 'GamerDev',
        ]);
    }

    public function test_user_can_submit_project_with_prototype_url(): void
    {
        $category = Category::create(['name' => 'UI Design', 'slug' => 'ui-design']);

        $response = $this->post('/unggah', [
            'title' => 'Design System UI Kit',
            'tagline' => 'Figma design system & tokens',
            'category_id' => $category->id,
            'tech_stacks' => 'Figma, TailwindCSS',
            'prototype_url' => 'https://www.figma.com/proto/test-prototype',
            'guest_name' => 'UI Designer',
        ]);

        $response->assertRedirect('/project/design-system-ui-kit');

        $this->assertDatabaseHas('projects', [
            'title' => 'Design System UI Kit',
            'prototype_url' => 'https://www.figma.com/proto/test-prototype',
        ]);

        $feedResponse = $this->get('/');
        $feedResponse->assertStatus(200);
        $feedResponse->assertSee('Interactive Prototype');
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
}
