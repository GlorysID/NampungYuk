<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ProfileUrlValidationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{string}>
     */
    public static function unsafeUrls(): array
    {
        return [
            'javascript with encoded newline' => ['javascript://x%0Aalert(1)'],
            'javascript scheme' => ['javascript:alert(1)'],
            'data scheme' => ['data:text/html,x'],
            'vbscript scheme' => ['vbscript:msgbox(1)'],
            'file scheme' => ['file:///etc/passwd'],
            'newline inside https url' => ["https://example.com/\nalert(1)"],
        ];
    }

    #[DataProvider('unsafeUrls')]
    public function test_unsafe_website_url_is_rejected_and_old_value_is_kept(string $url): void
    {
        $user = User::factory()->create([
            'username' => 'website_owner',
            'website_url' => 'https://old.example.com',
        ]);

        $response = $this->actingAs($user)
            ->from(route('settings.index'))
            ->put(route('profile.update'), [
                'name' => $user->name,
                'username' => 'website_owner',
                'website_url' => $url,
            ]);

        $response->assertSessionHasErrors('website_url');
        $this->assertSame('https://old.example.com', $user->fresh()->website_url);
    }

    #[DataProvider('unsafeUrls')]
    public function test_unsafe_github_url_is_rejected_and_old_value_is_kept(string $url): void
    {
        $user = User::factory()->create([
            'username' => 'github_owner',
            'github_url' => 'https://github.com/oldowner',
        ]);

        $response = $this->actingAs($user)
            ->from(route('settings.index'))
            ->put(route('profile.update'), [
                'name' => $user->name,
                'username' => 'github_owner',
                'github_url' => $url,
            ]);

        $response->assertSessionHasErrors('github_url');
        $this->assertSame('https://github.com/oldowner', $user->fresh()->github_url);
    }

    #[DataProvider('unsafeUrls')]
    public function test_unsafe_profile_link_url_is_rejected_and_old_link_is_kept(string $url): void
    {
        $user = User::factory()->create(['username' => 'link_owner']);
        $user->links()->create(['label' => 'Blog', 'url' => 'https://old.example.com', 'sort_order' => 0]);

        $response = $this->actingAs($user)
            ->from(route('settings.index'))
            ->put(route('profile.update'), [
                'name' => $user->name,
                'username' => 'link_owner',
                'links' => [['label' => 'Blog', 'url' => $url]],
            ]);

        $response->assertSessionHasErrors('links.0.url');
        $this->assertSame('https://old.example.com', $user->fresh()->links()->first()->url);
    }

    public function test_https_urls_are_saved(): void
    {
        $user = User::factory()->create([
            'username' => 'safe_owner',
            'github_url' => null,
            'website_url' => null,
        ]);

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => $user->name,
            'username' => 'safe_owner',
            'github_url' => 'https://github.com/safeowner',
            'website_url' => 'HTTPS://Example.com',
            'links' => [['label' => 'Docs', 'url' => 'https://docs.example.com']],
        ]);

        $response->assertSessionHasNoErrors();

        $user->refresh();
        $this->assertSame('https://github.com/safeowner', $user->github_url);
        $this->assertSame('HTTPS://Example.com', $user->website_url);
        $this->assertSame('https://docs.example.com', $user->links()->first()->url);
    }
}
