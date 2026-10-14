<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Project;
use App\Models\ProjectComment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectWarningPrivacyTest extends TestCase
{
    use RefreshDatabase;

    private function makeProject(User $owner, array $attrs = []): Project
    {
        $category = Category::firstOrCreate(['slug' => 'web-app'], ['name' => 'Web App']);

        return Project::create(array_merge([
            'user_id' => $owner->id,
            'category_id' => $category->id,
            'title' => 'Project Rahasia',
            'slug' => 'project-rahasia-'.uniqid(),
            'tagline' => 'tagline',
            'tech_stacks' => ['Laravel'],
            'visibility' => 'public',
            'score' => 1,
        ], $attrs));
    }

    public function test_admin_can_warn_a_specific_project_without_a_report(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $owner = User::factory()->create();
        $project = $this->makeProject($owner);

        $response = $this->actingAs($admin)->post(route('admin.projects.warn', $project), [
            'action' => 'set',
            'warning_notes' => 'Konten melanggar pedoman.',
        ]);

        $response->assertRedirect();
        $project->refresh();
        $this->assertTrue((bool) $project->is_flagged);
        $this->assertEquals('Konten melanggar pedoman.', $project->warning_notes);
    }

    public function test_warned_project_warning_is_hidden_from_public_but_shown_to_owner(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create(['is_admin' => false]);
        $project = $this->makeProject($owner, [
            'is_flagged' => true,
            'warning_notes' => 'Peringatan privat XYZ',
        ]);

        // Owner sees the warning on the detail page.
        $this->actingAs($owner)->get('/project/'.$project->slug)
            ->assertStatus(200)
            ->assertSee('Peringatan privat XYZ');

        // Stranger cannot even open the detail page (hidden from public).
        $this->actingAs($stranger)->get('/project/'.$project->slug)->assertStatus(404);
    }

    public function test_warned_project_does_not_leak_on_public_profile_via_comments(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create(['is_admin' => false]);
        $project = $this->makeProject($owner, [
            'is_flagged' => true,
            'warning_notes' => 'Peringatan privat',
        ]);

        // The owner commented on their own (now warned) project.
        ProjectComment::create([
            'project_id' => $project->id,
            'user_id' => $owner->id,
            'content' => 'komentar saya',
        ]);

        // A stranger viewing the owner's profile must NOT see the project title.
        $response = $this->actingAs($stranger)->get('/u/'.$owner->username);
        $response->assertStatus(200);
        $response->assertDontSee('Project Rahasia');
    }

    public function test_admin_warn_modal_component_is_available_for_admins(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $project = $this->makeProject(User::factory()->create());

        // The feed rendered for an admin should include the warn trigger.
        $response = $this->actingAs($admin)->get('/');
        $response->assertStatus(200);
        $response->assertSee('openWarnProjectModal', false);
    }
}
