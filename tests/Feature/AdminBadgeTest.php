<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminBadgeTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_profile_does_not_show_public_admin_badge(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'name' => 'Admin User']);

        $response = $this->get('/u/'.$admin->username);
        $response->assertStatus(200);
        $response->assertDontSee('Administrator NampungYuk', false);
    }

    public function test_regular_profile_has_no_admin_badge(): void
    {
        $user = User::factory()->create(['is_admin' => false, 'name' => 'Regular User']);

        $response = $this->get('/u/'.$user->username);
        $response->assertStatus(200);
        $response->assertDontSee('Administrator NampungYuk', false);
    }

    public function test_admin_badge_does_not_show_on_project_card_author(): void
    {
        $category = Category::firstOrCreate(['slug' => 'web-app'], ['name' => 'Web App']);
        $admin = User::factory()->create(['is_admin' => true]);
        Project::create([
            'user_id' => $admin->id,
            'category_id' => $category->id,
            'title' => 'Admin Project',
            'slug' => 'admin-project',
            'tagline' => 'tagline',
            'tech_stacks' => ['Laravel'],
            'score' => 1,
        ]);

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertDontSee('Administrator NampungYuk', false);
    }
}
