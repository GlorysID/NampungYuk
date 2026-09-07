<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Maximum number of login attempts before throttling.
     */
    protected int $maxAttempts = 5;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Handle a passed validation attempt and ensure the request is not rate limited.
     *
     * @throws ValidationException
     */
    protected function passedValidation(): void
    {
        $this->ensureIsNotRateLimited();
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), $this->maxAttempts)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'login' => trans('Terlalu banyak percobaan login. Coba lagi dalam :seconds detik.', ['seconds' => $seconds]),
        ]);
    }

    /**
     * Get the login throttling key for the request.
     */
    public function throttleKey(): string
    {
        return sha1(strtolower($this->string('login')).'|'.$this->ip());
    }

    /**
     * Increment the failed attempt counter for the request.
     */
    public function hitRateLimiter(): void
    {
        RateLimiter::hit($this->throttleKey(), 60);
    }
}
