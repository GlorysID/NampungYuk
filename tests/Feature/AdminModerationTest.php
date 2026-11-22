<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\CommunityPost;
use App\Models\Project;
use App\Models\Report;
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
        $response->assertSee('@charlie_delta');
        $response->assertDontSee('{{ $u->username }}', false);
        $response->assertSee('Echo Foxtrot');

        // Search filter
        $searchResponse = $this->actingAs($admin)->get(route('admin.reports.index', ['tab' => 'all_users', 'q' => 'Charlie']));
        $searchResponse->assertStatus(200);
        $searchResponse->assertSee('Charlie Delta');
        $searchResponse->assertDontSee('Echo Foxtrot');
    }

    public function test_admin_moderation_projects_tab_displays_community_post_reports(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $author = User::factory()->create();
        $reporter = User::factory()->create();

        $community = Community::create([
            'owner_id' => $author->id,
            'name' => 'Komunitas Audit',
            'topic' => 'Laravel',
            'slug' => 'komunitas-audit',
            'visibility' => 'public',
        ]);

        $post = CommunityPost::create([
            'community_id' => $community->id,
            'user_id' => $author->id,
            'type' => 'discussion',
            'content' => 'Postingan komunitas yang dilaporkan untuk audit',
        ]);

        Report::create([
            'reporter_id' => $reporter->id,
            'reportable_type' => CommunityPost::class,
            'reportable_id' => $post->id,
            'reason' => 'spam',
            'notes' => 'Mengandung tautan spam',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.reports.index', ['tab' => 'projects']));

        $response->assertOk();
        $response->assertSee('Postingan komunitas yang dilaporkan untuk audit');
    }

    public function test_warning_user_sends_notification_and_shows_global_banner_only_to_warned_user(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $targetUser = User::factory()->create(['warning_notes' => null]);
        $otherUser = User::factory()->create(['warning_notes' => null]);
        $notes = 'Harap perbaiki deskripsi proyek yang menyesatkan.';

        $this->actingAs($admin)->post(route('admin.users.warn', $targetUser), [
            'warning_notes' => $notes,
        ])->assertRedirect();

        $targetUser->refresh();

        $this->assertSame(1, $targetUser->notifications()->count());
        $notification = $targetUser->notifications()->first();
        $this->assertSame('moderation_warning', $notification->data['type'] ?? null);
        $this->assertSame('account', $notification->data['scope'] ?? null);
        $this->assertSame($notes, $notification->data['warning_excerpt'] ?? null);

        $this->actingAs($targetUser)->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('Akun Anda mendapatkan peringatan resmi dari Tim Moderasi NampungYuk.')
            ->assertSee($notes);

        $this->actingAs($targetUser)->get(route('projects.index'))
            ->assertOk()
            ->assertSee('Akun dalam peringatan moderasi')
            ->assertSee($notes);

        $this->actingAs($otherUser)->get(route('projects.index'))
            ->assertOk()
            ->assertDontSee('Akun dalam peringatan moderasi')
            ->assertDontSee($notes);
    }

    public function test_warning_project_sends_notification_and_displays_in_flagged_section(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $author = User::factory()->create();
        $project = Project::factory()->create([
            'user_id' => $author->id,
            'title' => 'Proyek Melanggar Aturan',
            'visibility' => 'public',
        ]);
        $warningNote = 'Tautan demo mengarah ke situs mencurigakan.';

        $this->actingAs($admin)->post(route('admin.projects.warn', $project), [
            'warning_notes' => $warningNote,
        ])->assertRedirect();

        $this->assertSame(1, $author->notifications()->count());
        $notification = $author->notifications()->first();
        $this->assertSame('moderation_warning', $notification->data['type'] ?? null);
        $this->assertSame('project', $notification->data['scope'] ?? null);

        $this->actingAs($admin)->get(route('admin.reports.index', ['tab' => 'flagged']))
            ->assertOk()
            ->assertSee('Proyek Melanggar Aturan')
            ->assertSee($warningNote);

        $this->actingAs($admin)->post(route('admin.projects.warn', $project), [
            'action' => 'remove',
        ])->assertRedirect();

        $this->actingAs($admin)->get(route('admin.reports.index', ['tab' => 'flagged']))
            ->assertOk()
            ->assertSee('Tidak ada konten berperingatan saat ini.')
            ->assertDontSee($warningNote)
            ->assertViewHas('flaggedProjects', fn ($projects): bool => $projects->isEmpty());
    }

    public function test_admin_moderation_filters_users_and_reports_accurately(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $activeUser = User::factory()->create(['name' => 'Aktif User', 'username' => 'aktif_user']);
        $warnedUser = User::factory()->create([
            'name' => 'Warned User',
            'username' => 'warned_user',
            'warning_notes' => 'Dalam pantauan admin.',
            'warned_at' => now(),
        ]);
        $bannedUser = User::factory()->create([
            'name' => 'Banned User',
            'username' => 'banned_user',
            'is_banned' => true,
            'ban_reason' => 'Spam berulang.',
            'banned_at' => now(),
        ]);

        $this->actingAs($admin)->get(route('admin.reports.index', ['tab' => 'all_users', 'filter' => 'warned']))
            ->assertOk()
            ->assertSee('Warned User')
            ->assertDontSee('Aktif User');

        $this->actingAs($admin)->get(route('admin.reports.index', ['tab' => 'all_users', 'filter' => 'banned']))
            ->assertOk()
            ->assertSee('Banned User')
            ->assertDontSee('Warned User');

        $this->actingAs($admin)->get(route('admin.reports.index', ['tab' => 'banned']))
            ->assertOk()
            ->assertSee('Banned User')
            ->assertDontSee('Aktif User');

        $reporter = User::factory()->create();
        $pendingProject = Project::factory()->create(['title' => 'Karya Laporan Pending']);
        $resolvedProject = Project::factory()->create(['title' => 'Karya Laporan Selesai']);

        Report::create([
            'reporter_id' => $reporter->id,
            'reportable_type' => Project::class,
            'reportable_id' => $pendingProject->id,
            'reason' => 'spam',
            'notes' => 'Laporan pending konten',
            'status' => 'pending',
        ]);

        Report::create([
            'reporter_id' => $reporter->id,
            'reportable_type' => User::class,
            'reportable_id' => $activeUser->id,
            'reason' => 'harassment',
            'notes' => 'Laporan akun user',
            'status' => 'pending',
        ]);

        Report::create([
            'reporter_id' => $reporter->id,
            'reportable_type' => Project::class,
            'reportable_id' => $resolvedProject->id,
            'reason' => 'broken_demo',
            'notes' => 'Sudah ditangani admin',
            'status' => 'resolved',
            'resolved_by' => $admin->id,
            'resolved_at' => now(),
        ]);

        $this->actingAs($admin)->get(route('admin.reports.index', ['tab' => 'reports', 'status' => 'pending']))
            ->assertOk()
            ->assertSee('Karya Laporan Pending')
            ->assertSee('Aktif User')
            ->assertDontSee('Karya Laporan Selesai');

        $this->actingAs($admin)->get(route('admin.reports.index', ['tab' => 'reports', 'status' => 'history']))
            ->assertOk()
            ->assertSee('Karya Laporan Selesai')
            ->assertDontSee('Karya Laporan Pending');

        $this->actingAs($admin)->get(route('admin.reports.index', ['tab' => 'reports', 'type' => 'user', 'status' => 'pending']))
            ->assertOk()
            ->assertSee('Aktif User')
            ->assertDontSee('Karya Laporan Pending');

        $this->actingAs($admin)->get(route('admin.reports.index', ['tab' => 'users']))
            ->assertOk()
            ->assertSee('Aktif User')
            ->assertDontSee('Karya Laporan Pending');
    }

    public function test_admin_deleting_user_from_moderation_panel_stays_on_panel_and_resolves_reports(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $targetUser = User::factory()->create(['username' => 'akun_terhapus']);
        $reporter = User::factory()->create();

        $report = Report::create([
            'reporter_id' => $reporter->id,
            'reportable_type' => User::class,
            'reportable_id' => $targetUser->id,
            'reason' => 'spam',
            'notes' => 'Akun spam berulang.',
            'status' => 'pending',
        ]);

        $redirectUrl = route('admin.reports.index', ['tab' => 'all_users', 'filter' => 'warned']);

        $response = $this->actingAs($admin)
            ->from($redirectUrl)
            ->delete(route('admin.users.destroy', $targetUser));

        $response->assertRedirect($redirectUrl);
        $this->assertDatabaseMissing('users', ['id' => $targetUser->id]);
        $this->assertDatabaseHas('reports', [
            'id' => $report->id,
            'status' => 'resolved',
            'resolved_by' => $admin->id,
        ]);
    }
}
