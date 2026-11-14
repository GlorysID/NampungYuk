<?php

namespace App\Services;

use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use stdClass;
use Throwable;
use UnexpectedValueException;

class FirebaseIdTokenVerifier
{
    private const JWKS_URL = 'https://www.googleapis.com/service_accounts/v1/jwk/securetoken@system.gserviceaccount.com';

    private const CACHE_KEY = 'firebase.securetoken.jwks';

    /**
     * Verify a Firebase Auth ID token and return normalized Google user claims.
     *
     * @return array{google_id: string, firebase_uid: string, email: string, name: string, avatar: ?string}
     */
    public function verify(string $idToken): array
    {
        $projectId = trim((string) config('services.firebase.project_id'));
        if ($projectId === '') {
            throw new RuntimeException('FIREBASE_PROJECT_ID belum dikonfigurasi.');
        }

        $kid = $this->extractKeyId($idToken);
        $jwks = $this->fetchJwks();

        if (! $this->hasKeyId($jwks, $kid)) {
            $jwks = $this->fetchJwks(forceRefresh: true);
        }

        $keys = JWK::parseKeySet($jwks, 'RS256');
        $payload = $this->decodeWithLeeway($idToken, $keys);

        $this->assertValidClaims($payload, $projectId);

        $email = strtolower(trim((string) ($payload->email ?? '')));
        $googleIdentities = $payload->firebase->identities->{'google.com'} ?? null;
        $googleId = (is_array($googleIdentities) && ! empty($googleIdentities[0]) && is_string($googleIdentities[0]))
            ? $googleIdentities[0]
            : (string) $payload->sub;

        $name = isset($payload->name) && is_string($payload->name) ? trim($payload->name) : '';
        $avatar = isset($payload->picture) && is_string($payload->picture) && trim($payload->picture) !== ''
            ? trim($payload->picture)
            : null;

        return [
            'google_id' => $googleId,
            'firebase_uid' => (string) $payload->sub,
            'email' => $email,
            'name' => $name,
            'avatar' => $avatar,
        ];
    }

    /**
     * Extract the key ID (kid) from the JWT header before signature verification.
     */
    private function extractKeyId(string $idToken): string
    {
        $segments = explode('.', $idToken);
        if (count($segments) !== 3) {
            throw new UnexpectedValueException('Format token Firebase tidak valid.');
        }

        $header = JWT::jsonDecode(JWT::urlsafeB64Decode($segments[0]));
        if (! $header instanceof stdClass || empty($header->kid) || ! is_string($header->kid)) {
            throw new UnexpectedValueException('Header token Firebase tidak menyertakan kid yang valid.');
        }

        return $header->kid;
    }

    /**
     * Decode a JWT while allowing minor local clock skew.
     *
     * @param  array<string, Key>  $keys
     */
    private function decodeWithLeeway(string $idToken, array $keys): stdClass
    {
        $previousLeeway = JWT::$leeway;
        JWT::$leeway = max($previousLeeway, 60);

        try {
            return JWT::decode($idToken, $keys);
        } finally {
            JWT::$leeway = $previousLeeway;
        }
    }

    /**
     * Validate standard Firebase ID token claims and verified email requirement.
     */
    private function assertValidClaims(stdClass $payload, string $projectId): void
    {
        $expectedIssuer = 'https://securetoken.google.com/'.$projectId;

        if (($payload->aud ?? null) !== $projectId) {
            throw new UnexpectedValueException('Audience token Firebase tidak cocok dengan project.');
        }

        if (($payload->iss ?? null) !== $expectedIssuer) {
            throw new UnexpectedValueException('Issuer token Firebase tidak valid.');
        }

        $sub = $payload->sub ?? null;
        if (! is_string($sub) || $sub === '' || strlen($sub) > 128) {
            throw new UnexpectedValueException('Subject token Firebase tidak valid.');
        }

        if (isset($payload->auth_time) && (! is_numeric($payload->auth_time) || (int) $payload->auth_time > now()->timestamp + 60)) {
            throw new UnexpectedValueException('Waktu autentikasi token Firebase tidak valid.');
        }

        $email = isset($payload->email) && is_string($payload->email) ? trim($payload->email) : '';
        if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new UnexpectedValueException('Akun Google tidak menyertakan alamat email yang valid.');
        }

        if (($payload->email_verified ?? false) !== true) {
            throw new UnexpectedValueException('Email pada akun Google belum terverifikasi.');
        }
    }

    /**
     * Check whether the JWK set contains the requested key ID.
     *
     * @param  array<string, mixed>  $jwks
     */
    private function hasKeyId(array $jwks, string $kid): bool
    {
        foreach (($jwks['keys'] ?? []) as $key) {
            if (is_array($key) && ($key['kid'] ?? null) === $kid) {
                return true;
            }
        }

        return false;
    }

    /**
     * Fetch and cache Google's Secure Token JWK set.
     *
     * @return array<string, mixed>
     */
    private function fetchJwks(bool $forceRefresh = false): array
    {
        if (! $forceRefresh) {
            $cached = Cache::get(self::CACHE_KEY);
            if (is_array($cached) && ! empty($cached['keys'])) {
                return $cached;
            }
        } else {
            Cache::forget(self::CACHE_KEY);
        }

        $response = Http::connectTimeout(3)
            ->timeout(8)
            ->retry([100, 300], 0, function (Throwable $exception): bool {
                return $exception instanceof ConnectionException;
            }, throw: false)
            ->get(self::JWKS_URL);

        if (! $response->successful()) {
            throw new RuntimeException('Gagal mengambil kunci publik Firebase (HTTP '.$response->status().').');
        }

        $jwks = $response->json();
        if (! is_array($jwks) || empty($jwks['keys']) || ! is_array($jwks['keys'])) {
            throw new RuntimeException('Respons JWK publik Firebase tidak valid.');
        }

        $ttl = $this->resolveCacheTtl($response->header('Cache-Control'));
        Cache::put(self::CACHE_KEY, $jwks, $ttl);

        return $jwks;
    }

    /**
     * Resolve cache TTL in seconds from Google's Cache-Control header.
     */
    private function resolveCacheTtl(?string $cacheControl): int
    {
        if ($cacheControl && preg_match('/max-age=(\d+)/i', $cacheControl, $matches) === 1) {
            $seconds = (int) $matches[1];

            return max(60, min($seconds, 86400));
        }

        return 3600;
    }
}
