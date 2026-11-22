<?php

namespace Tests\Feature;

use App\Events\ProjectPublished;
use App\Models\Category;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ProjectDraftsTest extends TestCase
{
    use RefreshDatabase;

    public function test_bare_domain_demo_url_gets_https_prepended_while_unsafe_scheme_is_rejected(): void
    {
        $category = Category::create(['name' => 'Cybersecurity', 'slug' => 'cybersecurity']);
        $author = User::factory()->create();

        $this->actingAs($author)
            ->post(route('projects.store'), [
                'title' => 'Nampung Yuk',
                'tagline' => 'ini gua buat website',
                'category_id' => $category->id,
                'tech_stacks' => 'Laravel, TailwindCSS',
                'demo_url' => 'radityabagushardana.ai.id',
                'github_url' => 'https://github.com/GlorysID/NampungYuk',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('projects', [
            'user_id' => $author->id,
            'title' => 'Nampung Yuk',
            'demo_url' => 'https://radityabagushardana.ai.id',
        ]);

        $this->actingAs($author)
            ->post(route('projects.store'), [
                'title' => 'XSS URL Project',
                'tagline' => 'Percobaan skema berbahaya',
                'category_id' => $category->id,
                'tech_stacks' => 'PHP',
                'demo_url' => 'javascript:alert(1)',
            ])
            ->assertSessionHasErrors('demo_url');

        // AJAX submit with invalid URL returns 422 JSON (no page reload)
        $this->actingAs($author)
            ->postJson(route('projects.store'), [
                'title' => 'Invalid URL Project',
                'tagline' => 'Testing JSON validation',
                'category_id' => $category->id,
                'tech_stacks' => 'PHP',
                'demo_url' => 'not-a-valid-url',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('demo_url');
    }

    public function test_autosave_upserts_single_draft_and_manual_save_as_draft_redirects_to_drafts(): void
    {
        Event::fake([ProjectPublished::class]);

        $category = Category::create(['name' => 'Web App', 'slug' => 'web-app']);
        $author = User::factory()->create();

        // 1. First autosave (e.g. before refresh/crash) creates a draft without bumping category count
        $first = $this->actingAs($author)
            ->postJson(route('projects.autosave'), [
                'title' => 'Draft Awal',
                'tagline' => 'Baru mulai nulis',
                'category_id' => $category->id,
                'tech_stacks' => 'Laravel',
            ])
            ->assertOk()
            ->assertJson(['saved' => true])
            ->json();

        $draftId = $first['draft_id'];
        $this->assertNotNull($draftId);
        $this->assertSame(0, $category->fresh()->projects_count);
        Event::assertNotDispatched(ProjectPublished::class);

        // 2. Second autosave updates the same draft row
        $this->actingAs($author)
            ->postJson(route('projects.autosave'), [
                'draft_id' => $draftId,
                'title' => 'Draft Revisi',
                'tagline' => 'Sudah ditambah catatan',
                'category_id' => $category->id,
                'tech_stacks' => 'Laravel, AlpineJS',
                'demo_url' => 'contohdemo.id',
            ])
            ->assertOk()
            ->assertJson(['saved' => true, 'draft_id' => $draftId]);

        $this->assertSame(1, Project::where('user_id', $author->id)->count());
        $this->assertDatabaseHas('projects', [
            'id' => $draftId,
            'visibility' => 'draft',
            'title' => 'Draft Revisi',
            'demo_url' => 'https://contohdemo.id',
        ]);

        // 3. Explicit "Simpan ke Draft" with incomplete tagline still succeeds cleanly
        $this->actingAs($author)
            ->post(route('projects.store'), [
                'save_as_draft' => '1',
                'title' => 'Draft Manual',
                'tagline' => '',
                'category_id' => $category->id,
                'tech_stacks' => '',
            ])
            ->assertRedirect(route('projects.drafts'));

        $this->assertDatabaseHas('projects', [
            'user_id' => $author->id,
            'title' => 'Draft Manual',
            'visibility' => 'draft',
        ]);
        $this->assertSame(0, $category->fresh()->projects_count);
    }

    public function test_drafts_are_hidden_from_feeds_and_other_users_until_published(): void
    {
        Event::fake([ProjectPublished::class]);

        $category = Category::create(['name' => 'Backend', 'slug' => 'backend']);
        $author = User::factory()->create();
        $otherUser = User::factory()->create();
        $admin = User::factory()->create(['is_admin' => true]);

        $draft = Project::create([
            'user_id' => $author->id,
            'category_id' => $category->id,
            'title' => 'Karya Masih Draft',
            'slug' => 'karya-masih-draft',
            'tagline' => 'Belum siap tayang',
            'tech_stacks' => ['Laravel', 'PostgreSQL'],
            'visibility' => 'draft',
        ]);

        // Hidden from homepage feed & latest JSON even for author & admin
        $this->actingAs($author)
            ->get(route('projects.index'))
            ->assertOk()
            ->assertDontSee('Karya Masih Draft');

        $this->actingAs($author)
            ->getJson(route('projects.latest'))
            ->assertOk()
            ->assertDontSee('Karya Masih Draft');

        // Visible in /draft and profile Draft tab for owner
        $this->actingAs($author)
            ->get(route('projects.drafts'))
            ->assertOk()
            ->assertSee('Karya Masih Draft');

        $this->actingAs($author)
            ->get(route('profile.show', $author->username))
            ->assertOk()
            ->assertSee('Terpublikasi')
            ->assertSee('Draft')
            ->assertSee('Edit Draft')
            ->assertSee('Post Sekarang')
            ->assertSee('Karya Masih Draft');

        $this->actingAs($author)
            ->get(route('profile.show', ['username' => $author->username, 'tab' => 'drafts']))
            ->assertOk()
            ->assertSee('Karya Masih Draft');

        $this->actingAs($otherUser)
            ->get(route('profile.show', $author->username))
            ->assertOk()
            ->assertDontSee('Karya Masih Draft')
            ->assertDontSee('Post Sekarang');

        // Direct show returns 404 for other users and even non-owner admins
        $this->actingAs($otherUser)
            ->get(route('projects.show', $draft->slug))
            ->assertNotFound();

        $this->actingAs($admin)
            ->get(route('projects.show', $draft->slug))
            ->assertNotFound();

        // Interactions and publish are forbidden for non-owners
        $this->actingAs($otherUser)
            ->postJson(route('projects.vote', $draft))
            ->assertForbidden();

        $this->actingAs($otherUser)
            ->post(route('projects.publish', $draft->slug))
            ->assertForbidden();

        // Owner publishes draft
        $this->actingAs($author)
            ->post(route('projects.publish', $draft->slug))
            ->assertRedirect();

        $draft->refresh();
        $this->assertSame('public', $draft->visibility);
        $this->assertSame(1, $category->fresh()->projects_count);
        Event::assertDispatched(ProjectPublished::class);

        // Now visible on homepage feed
        $this->get(route('projects.index'))
            ->assertOk()
            ->assertSee('Karya Masih Draft');

        // Owner moves it back to draft via edit/update -> category count decrements
        $this->actingAs($author)
            ->put(route('projects.update', $draft->slug), [
                'title' => 'Karya Masih Draft',
                'tagline' => 'Kembali jadi draft',
                'category_id' => $category->id,
                'tech_stacks' => 'Laravel, PostgreSQL',
                'visibility' => 'draft',
            ])
            ->assertRedirect(route('projects.drafts'));

        $this->assertSame('draft', $draft->fresh()->visibility);
        $this->assertSame(0, $category->fresh()->projects_count);
    }
}
