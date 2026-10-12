<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminModerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_report_project(): void
    {
        $author = User::factory()->create();
        $reporter = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $author->id]);

        $response = $this->actingAs($reporter)->postJson(route('reports.store'), [
            'reportable_type' => 'project',
            'reportable_id' => $project->id,
            'reason' => 'spam',
            'details' => 'Proyek ini berisi spam link.',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('reports', [
            'reporter_id' => $reporter->id,
            'reportable_type' => Project::class,
            'reportable_id' => $project->id,
            'reason' => 'spam',
            'status' => 'pending',
        ]);
    }

    public function test_user_cannot_report_own_project(): void
    {
        $author = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $author->id]);

        $response = $this->actingAs($author)->postJson(route('reports.store'), [
            'reportable_type' => 'project',
            'reportable_id' => $project->id,
            'reason' => 'spam',
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseMissing('reports', [
            'reportable_id' => $project->id,
        ]);
    }

    public function test_non_admin_cannot_access_admin_moderation_dashboard(): void
    {
        $regularUser = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($regularUser)->get(route('admin.reports.index'));

        $response->assertStatus(403);
    }

    public function test_admin_can_access_moderation_dashboard(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get(route('admin.reports.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.reports');
    }

    public function test_admin_can_warn_project(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $project = Project::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.projects.warn', $project), [
            'warning_notes' => 'Harap ganti link demo yang rusak.',
        ]);

        $response->assertRedirect();
        $project->refresh();
        $this->assertEquals('Harap ganti link demo yang rusak.', $project->warning_notes);
        $this->assertTrue($project->is_flagged);
    }

    public function test_admin_can_ban_and_unban_user(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $targetUser = User::factory()->create(['is_banned' => false]);

        $banResponse = $this->actingAs($admin)->post(route('admin.users.ban', $targetUser), [
            'reason' => 'Spam massal.',
        ]);

        $banResponse->assertRedirect();
        $targetUser->refresh();
        $this->assertTrue($targetUser->is_banned);
        $this->assertEquals('Spam massal.', $targetUser->ban_reason);

        // Unban
        $unbanResponse = $this->actingAs($admin)->post(route('admin.users.unban', $targetUser));
        $unbanResponse->assertRedirect();
        $targetUser->refresh();
        $this->assertFalse($targetUser->is_banned);
        $this->assertNull($targetUser->ban_reason);
    }

    public function test_banned_user_is_logged_out_by_middleware(): void
    {
        $bannedUser = User::factory()->create([
            'is_banned' => true,
            'ban_reason' => 'Akun melanggar aturan.',
        ]);

        $response = $this->actingAs($bannedUser)->get(route('projects.index'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_admin_can_warn_and_unwarn_user_without_report(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $targetUser = User::factory()->create(['warning_notes' => null]);

        // Admin warns user directly
        $response = $this->actingAs($admin)->post(route('admin.users.warn', $targetUser), [
            'warning_notes' => 'Peringatan pertama untuk konten repositori Anda.',
        ]);

        $response->assertRedirect();
        $targetUser->refresh();
        $this->assertEquals('Peringatan pertama untuk konten repositori Anda.', $targetUser->warning_notes);
        $this->assertNotNull($targetUser->warned_at);
        $this->assertTrue($targetUser->hasWarning());

        // Admin removes warning
        $unwarnResponse = $this->actingAs($admin)->post(route('admin.users.warn', $targetUser), [
            'action' => 'remove',
        ]);

        $unwarnResponse->assertRedirect();
        $targetUser->refresh();
        $this->assertNull($targetUser->warning_notes);
        $this->assertNull($targetUser->warned_at);
        $this->assertFalse($targetUser->hasWarning());
    }

    public function test_admin_can_delete_user(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $targetUser = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $targetUser->id]);

        $response = $this->actingAs($admin)->delete(route('admin.users.destroy', $targetUser));

        $response->assertRedirect(route('projects.index'));
        $this->assertDatabaseMissing('users', ['id' => $targetUser->id]);
        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }

    public function test_warned_project_is_only_visible_to_owner_and_admin(): void
    {
        $author = User::factory()->create();
        $otherUser = User::factory()->create();
        $admin = User::factory()->create(['is_admin' => true]);

        $warnedProject = Project::factory()->create([
            'user_id' => $author->id,
            'title' => 'Proyek Kena Peringatan',
            'warning_notes' => 'Melanggar aturan penamaan repositori.',
            'is_flagged' => true,
            'visibility' => 'public',
        ]);

        // 1. Other user accessing detail gets 404
        $otherResponse = $this->actingAs($otherUser)->get('/project/'.$warnedProject->slug);
        $otherResponse->assertStatus(404);

        // 2. Author can view detail
        $authorResponse = $this->actingAs($author)->get('/project/'.$warnedProject->slug);
        $authorResponse->assertStatus(200);
        $authorResponse->assertSee('Proyek Kena Peringatan');

        // 3. Admin can view detail
        $adminResponse = $this->actingAs($admin)->get('/project/'.$warnedProject->slug);
        $adminResponse->assertStatus(200);
        $adminResponse->assertSee('Proyek Kena Peringatan');

        // 4. Feed query does not show warned project to other user
        $feedResponse = $this->actingAs($otherUser)->get('/');
        $feedResponse->assertDontSee('Proyek Kena Peringatan');

        // 5. Feed query DOES show warned project to author
        $authorFeedResponse = $this->actingAs($author)->get('/');
        $authorFeedResponse->assertSee('Proyek Kena Peringatan');
    }

    public function test_admin_can_view_all_users_tab_and_search(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user1 = User::factory()->create(['name' => 'Charlie Delta', 'username' => 'charlie_delta']);
        $user2 = User::factory()->create(['name' => 'Echo Foxtrot', 'username' => 'echo_foxtrot']);

        $response = $this->actingAs($admin)->get(route('admin.reports.index', ['tab' => 'all_users']));
        $response->assertStatus(200);
        $response->assertSee('Charlie Delta');
        $response->assertSee('Echo Foxtrot');

        // Search filter
        $searchResponse = $this->actingAs($admin)->get(route('admin.reports.index', ['tab' => 'all_users', 'q' => 'Charlie']));
        $searchResponse->assertStatus(200);
        $searchResponse->assertSee('Charlie Delta');
        $searchResponse->assertDontSee('Echo Foxtrot');
    }
}
