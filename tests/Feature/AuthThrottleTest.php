<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AuthThrottleTest extends TestCase
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

    /**
     * @return array<string, string>
     */
    private function registrationData(string $email = 'throttle@example.com'): array
    {
        return [
            'name' => 'Throttle Tester',
            'username' => 'throttle_tester',
            'email' => $email,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];
    }

    public function test_register_is_throttled_after_five_attempts_per_minute(): void
    {
        Notification::fake();

        for ($i = 1; $i <= 5; $i++) {
            $this->from(route('register'))
                ->post(route('register.store'), $this->registrationData())
                ->assertStatus(302);
        }

        $this->from(route('register'))
            ->post(route('register.store'), $this->registrationData())
            ->assertStatus(429);
    }
}
