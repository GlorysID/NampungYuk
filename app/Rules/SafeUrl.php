<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Rejects URL values whose scheme is not http/https.
 *
 * Laravel's `url` rule relies on FILTER_VALIDATE_URL, which accepts
 * `javascript://x%0Aalert(1)`; rendering that as an href executes script.
 */
class SafeUrl implements ValidationRule
{
    /**
     * Validate the given value.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $url = (string) $value;

        if ($url === '') {
            return;
        }

        $valid = ! preg_match('/[\x00-\x1F\x7F\s]/', $url)
            && filter_var($url, FILTER_VALIDATE_URL) !== false
            && in_array(strtolower((string) parse_url($url, PHP_URL_SCHEME)), ['http', 'https'], true);

        if (! $valid) {
            $fail('Kolom :attribute harus berupa alamat URL yang valid dan diawali dengan http:// atau https://.');
        }
    }
}
