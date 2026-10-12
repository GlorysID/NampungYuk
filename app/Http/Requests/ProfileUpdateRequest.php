<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required', 'string', 'min:3', 'max:30',
                'regex:/^[a-zA-Z0-9_]+$/',
                function (string $attribute, mixed $value, \Closure $fail) use ($userId): void {
                    $exists = User::whereRaw('LOWER(username) = ?', [strtolower((string) $value)])
                        ->where('id', '!=', $userId)
                        ->exists();

                    if ($exists) {
                        $fail('Username ini sudah digunakan. Silakan gunakan username lain.');
                    }
                },
            ],
            'bio' => ['nullable', 'string', 'max:280'],
            'github_url' => ['nullable', 'url', 'max:255'],
            'website_url' => ['nullable', 'url', 'max:255'],
            'links' => ['nullable', 'array', 'max:6'],
            'links.*.label' => ['nullable', 'string', 'max:40'],
            'links.*.url' => ['nullable', 'url', 'max:255'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:2048'],
            'banner' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:4096'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $user = $this->user();
            if (! $user) {
                return;
            }

            // Only enforce cooldown if user is actually attempting to change their username
            if ($this->filled('username') && $this->input('username') !== $user->username) {
                if (! $user->canChangeUsername()) {
                    $days = $user->daysUntilNextUsernameChange();
                    $nextDate = $user->nextAllowedUsernameChangeDate()?->translatedFormat('d F Y') ?? 'beberapa hari lagi';

                    $validator->errors()->add(
                        'username',
                        "Username hanya dapat diubah 7 hari sekali. Anda dapat mengubahnya kembali dalam {$days} hari (tanggal {$nextDate})."
                    );
                }
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'username.regex' => 'Username hanya boleh berisi huruf, angka, dan underscore.',
            'username.unique' => 'Username ini sudah digunakan. Silakan gunakan username lain.',
            'bio.max' => 'Bio maksimal 280 karakter.',
            'avatar.max' => 'Ukuran avatar maksimal 2MB.',
            'banner.max' => 'Ukuran banner maksimal 4MB.',
        ];
    }
}
