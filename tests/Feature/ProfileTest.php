<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Project;
use App\Models\ProjectComment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
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

    public function test_user_can_persist_github_url_and_website_url_on_profile_update(): void
    {
        $user = User::factory()->create([
            'username' => 'link_owner',
            'github_url' => null,
            'website_url' => null,
        ]);

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => 'Link Owner',
            'username' => 'link_owner',
            'bio' => 'Bio dengan tautan',
            'github_url' => 'https://github.com/linkowner',
            'website_url' => 'https://linkowner.dev',
        ]);

        $response->assertSessionHasNoErrors();
        $user->refresh();
        $this->assertSame('https://github.com/linkowner', $user->github_url);
        $this->assertSame('https://linkowner.dev', $user->website_url);
    }

    public function test_owner_can_search_own_posts_on_profile_including_pinned_and_private_projects(): void
    {
        $user = User::factory()->create(['username' => 'search_owner']);
        $category = Category::create(['name' => 'Backend', 'slug' => 'backend']);

        Project::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'title' => 'Pinned Laravel API',
            'slug' => 'pinned-laravel-api',
            'tagline' => 'REST API cepat',
            'description' => 'Service API dengan Laravel',
            'tech_stacks' => ['Laravel'],
            'is_pinned' => true,
        ]);

        Project::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'title' => 'Private Laravel Tool',
            'slug' => 'private-laravel-tool',
            'tagline' => 'Internal',
            'description' => 'Tool internal Laravel',
            'tech_stacks' => ['Laravel'],
            'is_flagged' => true,
        ]);

        Project::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'title' => 'Aplikasi Flutter Chat',
            'slug' => 'aplikasi-flutter-chat',
            'tagline' => 'Mobile app',
            'description' => 'Chat real-time mobile',
            'tech_stacks' => ['Flutter'],
        ]);

        Cache::forget('rail:trending_projects_v2');

        $response = $this->actingAs($user)
            ->get(route('profile.show', ['username' => $user->username, 'q' => 'Laravel']));

        $response->assertOk()
            ->assertSee('id="profile-search-form"', false)
            ->assertSee('Pinned Laravel API')
            ->assertSee('Private Laravel Tool')
            ->assertViewHas('pinnedProjects', fn ($pinned) => $pinned->isEmpty())
            ->assertViewHas('projects', function ($projects) {
                $titles = $projects->pluck('title')->all();

                return in_array('Pinned Laravel API', $titles, true)
                    && in_array('Private Laravel Tool', $titles, true)
                    && ! in_array('Aplikasi Flutter Chat', $titles, true);
            });
    }

    public function test_profile_search_hides_flagged_projects_from_other_visitors(): void
    {
        Cache::forget('rail:trending_projects_v2');

        $owner = User::factory()->create(['username' => 'flagged_owner']);
        $visitor = User::factory()->create(['username' => 'normal_visitor']);
        $category = Category::create(['name' => 'Tools', 'slug' => 'tools']);

        Project::create([
            'user_id' => $owner->id,
            'category_id' => $category->id,
            'title' => 'Karya Publik Rahasia',
            'slug' => 'karya-publik-rahasia',
            'tagline' => 'Terbuka',
            'description' => 'Aman dilihat semua orang',
            'tech_stacks' => ['PHP'],
        ]);

        $flagged = Project::create([
            'user_id' => $owner->id,
            'category_id' => $category->id,
            'title' => 'Proyek Peringatan Rahasia',
            'slug' => 'proyek-peringatan-rahasia',
            'tagline' => 'Privat',
            'description' => 'Tidak boleh bocor',
            'tech_stacks' => ['PHP'],
            'is_flagged' => true,
        ]);

        ProjectComment::create([
            'project_id' => $flagged->id,
            'user_id' => $owner->id,
            'content' => 'Komentar Rahasia di proyek flagged',
        ]);

        $this->actingAs($visitor)
            ->get(route('profile.show', ['username' => $owner->username, 'q' => 'Rahasia']))
            ->assertOk()
            ->assertSee('Karya Publik Rahasia')
            ->assertDontSee('Proyek Peringatan Rahasia')
            ->assertDontSee('Komentar Rahasia di proyek flagged');
    }

    public function test_profile_search_filters_reposts_and_preserves_active_tab(): void
    {
        $owner = User::factory()->create(['username' => 'repost_searcher']);
        $author = User::factory()->create(['username' => 'author_lain']);
        $category = Category::create(['name' => 'Systems', 'slug' => 'systems']);

        $rustProject = Project::create([
            'user_id' => $author->id,
            'category_id' => $category->id,
            'title' => 'Compiler Rust Mini',
            'slug' => 'compiler-rust-mini',
            'tagline' => 'Parser cepat',
            'description' => 'Dibuat pakai Rust',
            'tech_stacks' => ['Rust'],
        ]);

        $phpProject = Project::create([
            'user_id' => $author->id,
            'category_id' => $category->id,
            'title' => 'Dashboard PHP Klasik',
            'slug' => 'dashboard-php-klasik',
            'tagline' => 'Admin panel',
            'description' => 'Dibuat pakai PHP',
            'tech_stacks' => ['PHP'],
        ]);

        $owner->reposts()->create(['project_id' => $rustProject->id]);
        $owner->reposts()->create(['project_id' => $phpProject->id]);

        Cache::forget('rail:trending_projects_v2');

        $response = $this->actingAs($owner)->get(route('profile.show', [
            'username' => $owner->username,
            'q' => 'Rust',
            'tab' => 'reposts',
        ]));

        $response->assertOk()
            ->assertSee("activeTab: 'reposts'", false)
            ->assertSee('Compiler Rust Mini')
            ->assertViewHas('repostedProjects', function ($reposts) {
                $titles = $reposts->pluck('title')->all();

                return in_array('Compiler Rust Mini', $titles, true)
                    && ! in_array('Dashboard PHP Klasik', $titles, true);
            });
    }

    public function test_settings_page_requires_authentication_and_renders_security_section(): void
    {
        $this->get(route('settings.index'))
            ->assertRedirect(route('projects.index'))
            ->assertSessionHas('auth_required', true);

        $user = User::factory()->create(['username' => 'pengguna_uji']);

        $this->actingAs($user)
            ->get(route('settings.index'))
            ->assertOk()
            ->assertSee('Pengaturan Akun')
            ->assertSee('Keamanan')
            ->assertSee(route('settings.password.edit'), false)
            ->assertSee(route('settings.index'), false);
    }

    public function test_user_can_update_password_from_settings_and_requires_valid_current_password(): void
    {
        $user = User::factory()->create([
            'password' => 'password-lama-123',
        ]);

        // Wrong current password is rejected at the verification step.
        $this->actingAs($user)
            ->from(route('settings.password.edit'))
            ->post(route('settings.password.verify'), ['current_password' => 'salah-banget'])
            ->assertSessionHasErrors('current_password');

        // Updating without verification keeps the old password and blocks the change.
        $this->actingAs($user)
            ->from(route('settings.password.edit'))
            ->post(route('settings.password.update'), [
                'password' => 'password-baru-456',
                'password_confirmation' => 'password-baru-456',
            ])
            ->assertRedirect(route('settings.password.edit'))
            ->assertSessionHas('error');

        $this->assertTrue(Hash::check('password-lama-123', $user->fresh()->password));

        // Verify, then update.
        $this->actingAs($user)
            ->post(route('settings.password.verify'), ['current_password' => 'password-lama-123'])
            ->assertRedirect(route('settings.password.edit'));

        $this->actingAs($user)
            ->post(route('settings.password.update'), [
                'password' => 'password-baru-456',
                'password_confirmation' => 'password-baru-456',
            ])
            ->assertRedirect(route('settings.index', ['tab' => 'keamanan']))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $this->assertTrue(Hash::check('password-baru-456', $user->fresh()->password));
    }

    public function test_google_user_without_password_can_set_initial_password_on_settings(): void
    {
        $googleUser = User::factory()->create([
            'password' => null,
            'google_id' => 'google-uid-999',
        ]);

        $this->actingAs($googleUser)
            ->get(route('settings.password.edit'))
            ->assertOk()
            ->assertDontSee('name="current_password"', false)
            ->assertSee('Buat Password Akun');

        $this->actingAs($googleUser)
            ->post(route('settings.password.update'), [
                'password' => 'sandi-awal-789',
                'password_confirmation' => 'sandi-awal-789',
            ])
            ->assertRedirect(route('settings.index', ['tab' => 'keamanan']))
            ->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('sandi-awal-789', $googleUser->fresh()->password));
    }

    public function test_profile_edit_modal_uses_x_style_and_no_longer_contains_password_form(): void
    {
        $user = User::factory()->create(['username' => 'kreator_x']);
        $category = Category::create(['name' => 'Web', 'slug' => 'web']);

        Project::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'title' => 'Satu Postingan Profil',
            'slug' => 'satu-postingan-profil',
            'tagline' => 'Tes count header',
            'description' => 'Deskripsi singkat',
            'tech_stacks' => ['Laravel'],
        ]);

        $this->actingAs($user)
            ->get(route('profile.show', $user->username))
            ->assertOk()
            ->assertSee('1 postingan')
            ->assertSee('composer-seamless', false)
            ->assertDontSee('aria-label="Notifikasi"', false)
            ->assertSee('Edit profil')
            ->assertSee(route('profile.update'), false)
            ->assertSee(route('settings.index'), false)
            ->assertDontSee(route('settings.password.edit'), false);
    }

    public function test_profile_lists_followers_and_following_on_their_own_pages(): void
    {
        $target = User::factory()->create(['username' => 'pemilik_profil']);
        $follower = User::factory()->create(['username' => 'pengikut_setia']);
        $target->followers()->attach($follower->id);

        $this->get(route('profile.followers', $target->username))
            ->assertOk()
            ->assertSee('pengikut_setia', false);

        $this->get(route('profile.following', $target->username))
            ->assertOk()
            ->assertDontSee('pengikut_setia', false);

        $this->get(route('profile.following', $follower->username))
            ->assertOk()
            ->assertSee('pemilik_profil', false);
    }

    public function test_profile_shows_relation_badge_for_follower_and_mutual_friend(): void
    {
        $viewer = User::factory()->create();
        $target = User::factory()->create(['username' => 'target_dev']);

        // Guest and unrelated logged-in visitor do not see the badge.
        $this->get(route('profile.show', $target->username))
            ->assertOk()
            ->assertDontSee('Mengikutimu')
            ->assertDontSee('mutual:', false);

        $this->actingAs($viewer)
            ->get(route('profile.show', $target->username))
            ->assertOk()
            ->assertDontSee('Mengikutimu')
            ->assertDontSee('mutual:', false);

        // One-way: target follows viewer -> badge shows "Mengikutimu".
        $target->following()->attach($viewer->id);

        $this->actingAs($viewer)
            ->get(route('profile.show', $target->username))
            ->assertOk()
            ->assertSee('mutual: false', false)
            ->assertSee('Mengikutimu');

        // Mutual follow -> badge shows "Teman".
        $viewer->following()->attach($target->id);

        $this->actingAs($viewer)
            ->get(route('profile.show', $target->username))
            ->assertOk()
            ->assertSee('mutual: true', false)
            ->assertSee('Teman');

        // Owner viewing own profile never sees relation badge.
        $this->actingAs($target)
            ->get(route('profile.show', $target->username))
            ->assertOk()
            ->assertDontSee('Mengikutimu')
            ->assertDontSee('mutual:', false);
    }
}
