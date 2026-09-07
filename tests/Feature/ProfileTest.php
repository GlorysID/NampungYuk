<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Project;
use App\Models\ProjectComment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_developer_profile(): void
    {
        $user = User::factory()->create([
            'name' => 'Budi Santoso',
            'username' => 'budi_santoso',
            'bio' => 'Software Engineer pengembang web',
            'reputation_points' => 150,
            'github_url' => 'https://github.com/budisantoso',
        ]);

        $response = $this->get('/u/'.$user->username);

        $response->assertStatus(200);
        $response->assertSee('Budi Santoso');
        $response->assertSee('@budi_santoso');
        $response->assertSee('Software Engineer pengembang web');
        $response->assertSee('150 Poin Reputasi');
    }

    public function test_profile_displays_projects_and_activity(): void
    {
        $user = User::factory()->create([
            'name' => 'Dimas Aditya',
            'username' => 'dimas_aditya',
        ]);

        $category = Category::create([
            'name' => 'Web App',
            'slug' => 'web-app',
        ]);

        $project = Project::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'title' => 'Project Dimas Keren',
            'slug' => 'project-dimas-keren',
            'tagline' => 'Tagline project Dimas',
            'description' => 'Deskripsi project Dimas',
            'tech_stacks' => ['Vue 3', 'TailwindCSS'],
            'score' => 45,
        ]);

        ProjectComment::create([
            'project_id' => $project->id,
            'user_id' => $user->id,
            'content' => 'Komentar pengujian dari Dimas',
        ]);

        $response = $this->get('/u/'.$user->username);

        $response->assertStatus(200);
        $response->assertSee('Project Dimas Keren');
        $response->assertSee('Komentar pengujian dari Dimas');
    }

    public function test_non_existent_profile_returns_404(): void
    {
        $response = $this->get('/u/user_tidak_ada_xyz_123');

        $response->assertStatus(404);
    }
}
