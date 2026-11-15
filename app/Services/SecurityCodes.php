<?php

namespace App\Services;

use App\Mail\SecurityCodeMail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Six-digit verification codes sent by email for email change and password recovery.
 *
 * Codes live in the cache (not the database) and are single-use.
 */
final class SecurityCodes
{
    public const PURPOSE_EMAIL_CHANGE = 'email-change';

    public const PURPOSE_PASSWORD_RESET = 'password-reset';

    private const TTL_MINUTES = 15;

    private const MAX_ATTEMPTS = 5;

    /**
     * Generate and email a fresh code, replacing any previous one for the same purpose.
     *
     * @param  array<int, string>  $lines  body paragraphs shown above the code
     * @return string|null the plain code, or null when the mail could not be delivered
     */
    public function send(string $purpose, int|string $key, string $email, string $headline, array $lines = []): ?string
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $cacheKey = $this->key($purpose, $key);

        Cache::put($cacheKey, [
            'hash' => Hash::make($code),
            'attempts' => 0,
        ], now()->addMinutes(self::TTL_MINUTES));

        try {
            Mail::to($email)->send(new SecurityCodeMail($code, $headline, $lines));
        } catch (Throwable) {
            // Mailer unreachable (bad SMTP credentials, network): drop the code so the next attempt starts clean.
            Cache::forget($cacheKey);

            return null;
        }

        return $code;
    }

    public function verify(string $purpose, int|string $key, string $code): bool
    {
        $cacheKey = $this->key($purpose, $key);
        $payload = Cache::get($cacheKey);

        if (! is_array($payload) || ($payload['attempts'] ?? 0) >= self::MAX_ATTEMPTS) {
            Cache::forget($cacheKey);

            return false;
        }

        if (! Hash::check($code, $payload['hash'])) {
            // shortcut: a wrong guess restarts the 15 minute window; move expiry into the payload if this ever leaves localhost
            $payload['attempts']++;
            Cache::put($cacheKey, $payload, now()->addMinutes(self::TTL_MINUTES));

            return false;
        }

        Cache::forget($cacheKey);

        return true;
    }

    public function isEmpty(string $purpose, int|string $key): bool
    {
        return ! Cache::has($this->key($purpose, $key));
    }

    private function key(string $purpose, int|string $key): string
    {
        return "security-code:{$purpose}:{$key}";
    }
}
