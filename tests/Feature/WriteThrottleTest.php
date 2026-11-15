<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class WriteThrottleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Rate limiter counters live in the cache; start every test clean.
        Cache::flush();
    }

    protected function tearDown(): void
    {
        Cache::flush();

        parent::tearDown();
    }

    public function test_report_endpoint_throttles_after_ten_writes_per_minute(): void
    {
        $reporter = User::factory()->create();
        $target = User::factory()->create();

        $payload = [
            'reportable_type' => 'user',
            'reportable_id' => $target->id,
            'reason' => 'spam',
        ];

        for ($i = 1; $i <= 10; $i++) {
            $this->actingAs($reporter)
                ->postJson(route('reports.store'), $payload)
                ->assertStatus(200);
        }

        $this->actingAs($reporter)
            ->postJson(route('reports.store'), $payload)
            ->assertStatus(429);
    }

    public function test_report_endpoint_below_limit_is_not_throttled(): void
    {
        $reporter = User::factory()->create();
        $target = User::factory()->create();

        $payload = [
            'reportable_type' => 'user',
            'reportable_id' => $target->id,
            'reason' => 'spam',
        ];

        for ($i = 1; $i <= 3; $i++) {
            $this->actingAs($reporter)
                ->postJson(route('reports.store'), $payload)
                ->assertStatus(200);
        }
    }
}
