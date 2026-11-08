<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Community;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_open_search_page_and_see_tabs(): void
    {
        $response = $this->get(route('search.index'));

        $response->assertOk();
        $response->assertSee('Populer');
        $response->assertSee('Terbaru');
        $response->assertSee('Orang');
        $response->assertSee('Karya');
        $response->assertSee('Komunitas');
    }

    public function test_search_returns_matching_projects_in_karya_tab(): void
    {
        $category = Category::create(['name' => 'CLI Tools', 'slug' => 'cli-tools']);
        $user = User::factory()->create(['username' => 'cli_maker']);

        Project::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'title' => 'Laravel CLI Tool',
            'slug' => 'laravel-cli-tool',
            'tagline' => 'CLI cepat untuk Laravel',
            'tech_stacks' => ['Laravel', 'PHP'],
            'score' => 25,
        ]);

        $response = $this->get(route('search.index', ['q' => 'Laravel', 'tab' => 'karya']));

        $response->assertOk();
        $response->assertSee('Laravel CLI Tool');
    }

    public function test_search_returns_matching_people_in_orang_tab(): void
    {
        User::factory()->create([
            'name' => 'Siti Rahmawati',
            'username' => 'siti_rahma',
        ]);

        $response = $this->get(route('search.index', ['q' => 'Siti', 'tab' => 'orang']));

        $response->assertOk();
        $response->assertSee('Siti Rahmawati');
        $response->assertSee('siti_rahma');
    }

    public function test_search_returns_matching_communities_in_komunitas_tab(): void
    {
        $owner = User::factory()->create();

        Community::create([
            'owner_id' => $owner->id,
            'name' => 'Laravel Indonesia',
            'topic' => 'Laravel',
            'slug' => 'laravel-indonesia',
            'visibility' => 'public',
        ]);

        $response = $this->get(route('search.index', ['q' => 'Laravel', 'tab' => 'komunitas']));

        $response->assertOk();
        $response->assertSee('Laravel Indonesia');
    }

    public function test_search_does_not_leak_private_projects_to_guests(): void
    {
        $category = Category::create(['name' => 'Web App', 'slug' => 'web-app']);
        $owner = User::factory()->create();

        Project::create([
            'user_id' => $owner->id,
            'category_id' => $category->id,
            'title' => 'Project Rahasia',
            'slug' => 'project-rahasia',
            'tagline' => 'Tidak boleh bocor',
            'tech_stacks' => ['Laravel'],
            'visibility' => 'private',
        ]);

        $response = $this->get(route('search.index', ['q' => 'Rahasia', 'tab' => 'karya']));

        $response->assertOk();
        $response->assertDontSee('Project Rahasia');
    }

    public function test_invalid_tab_falls_back_to_populer(): void
    {
        $response = $this->get(route('search.index', ['q' => 'laravel', 'tab' => 'bogus']));

        $response->assertOk();
        $response->assertSee('Populer');
    }
}
