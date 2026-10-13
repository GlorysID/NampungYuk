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
        // The header search is contextual: on a profile it is present and scoped to this user.
        $response->assertSee('id="nav-search"', false);
        $response->assertSee('Cari karya @budi_santoso', false);
        $response->assertSee('name="u" value="budi_santoso"', false);
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

    public function test_user_can_update_profile_and_change_username_first_time(): void
    {
        $user = User::factory()->create([
            'username' => 'original_name',
            'username_changed_at' => null,
        ]);

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => 'Original User',
            'username' => 'new_username',
            'bio' => 'New bio text',
        ]);

        $response->assertRedirect(route('profile.show', 'new_username'));
        $user->refresh();
        $this->assertEquals('new_username', $user->username);
        $this->assertNotNull($user->username_changed_at);
    }

    public function test_user_cannot_change_username_again_within_7_days(): void
    {
        $user = User::factory()->create([
            'username' => 'current_handle',
            'username_changed_at' => now()->subDays(2),
        ]);

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => 'Current User',
            'username' => 'attempted_handle',
            'bio' => 'Trying to change username too soon',
        ]);

        $response->assertSessionHasErrors('username');
        $user->refresh();
        $this->assertEquals('current_handle', $user->username);
    }

    public function test_user_can_update_other_profile_fields_without_changing_username_within_7_days(): void
    {
        $changedAt = now()->subDays(2);
        $user = User::factory()->create([
            'name' => 'Old Name',
            'username' => 'locked_handle',
            'username_changed_at' => $changedAt,
        ]);

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => 'Updated Name',
            'username' => 'locked_handle',
            'bio' => 'Updated bio successfully',
        ]);

        $response->assertSessionHasNoErrors();
        $user->refresh();
        $this->assertEquals('Updated Name', $user->name);
        $this->assertEquals('Updated bio successfully', $user->bio);
        $this->assertEquals('locked_handle', $user->username);
    }

    public function test_user_can_change_username_after_7_days(): void
    {
        $user = User::factory()->create([
            'username' => 'old_handle',
            'username_changed_at' => now()->subDays(8),
        ]);

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => 'User Name',
            'username' => 'fresh_handle',
        ]);

        $response->assertSessionHasNoErrors();
        $user->refresh();
        $this->assertEquals('fresh_handle', $user->username);
    }

    public function test_user_cannot_take_existing_username_in_profile_update_case_insensitively(): void
    {
        User::factory()->create(['username' => 'existing_master']);

        $user = User::factory()->create([
            'username' => 'my_profile',
            'username_changed_at' => null,
        ]);

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => 'My Profile',
            'username' => 'EXISTING_MASTER',
        ]);

        $response->assertSessionHasErrors('username');
        $user->refresh();
        $this->assertEquals('my_profile', $user->username);
    }
}
