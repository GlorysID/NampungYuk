<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectGuestFilterAndUrlGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_filter_feed_by_category_and_tech_without_login_wall(): void
    {
        $category = Category::create(['name' => 'Web App', 'slug' => 'web-app']);

        $publicProject = Project::create([
            'category_id' => $category->id,
            'title' => 'Public Laravel',
            'slug' => 'public-laravel',
            'tagline' => 'Publik',
            'tech_stacks' => ['Laravel'],
            'visibility' => 'public',
        ]);

        $privateProject = Project::create([
            'category_id' => $category->id,
            'title' => 'Private Laravel',
            'slug' => 'private-laravel',
            'tagline' => 'Privat',
            'tech_stacks' => ['Laravel'],
            'visibility' => 'private',
        ]);

        $byCategory = $this->get('/?kategori=web-app');
        $byCategory->assertStatus(200);
        $titles = $byCategory->viewData('projects')->pluck('id');
        $this->assertTrue($titles->contains($publicProject->id));
        $this->assertFalse($titles->contains($privateProject->id));

        $this->get('/?tech=Laravel')->assertStatus(200);
    }

    public function test_demo_url_rejects_non_http_schemes_and_accepts_https(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'Web App', 'slug' => 'web-app']);

        $this->actingAs($user)
            ->post('/unggah', [
                'title' => 'Proyek XSS',
                'tagline' => 'Tagline',
                'category_id' => $category->id,
                'tech_stacks' => 'PHP',
                'demo_url' => 'javascript://x%0Aalert(1)',
            ])
            ->assertSessionHasErrors('demo_url');

        $this->assertDatabaseMissing('projects', ['title' => 'Proyek XSS']);

        $this->actingAs($user)
            ->post('/unggah', [
                'title' => 'Proyek Aman',
                'tagline' => 'Tagline',
                'category_id' => $category->id,
                'tech_stacks' => 'PHP',
                'demo_url' => 'https://ok.dev',
            ])
            ->assertRedirect('/project/proyek-aman');

        $this->assertDatabaseHas('projects', [
            'title' => 'Proyek Aman',
            'demo_url' => 'https://ok.dev',
        ]);
    }
}
