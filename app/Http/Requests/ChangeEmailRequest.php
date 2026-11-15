<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ChangeEmailRequest extends FormRequest
{
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
        /** @var User|null $user */
        $user = $this->user();
        $current = strtolower((string) $user?->email);

        return [
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                function (string $attribute, mixed $value, \Closure $fail) use ($user, $current): void {
                    $normalized = strtolower(trim((string) $value));

                    if ($normalized === '') {
                        return;
                    }

                    if ($normalized === $current) {
                        $fail('Alamat email baru harus berbeda dari alamat email kamu saat ini.');

                        return;
                    }

                    if (User::whereRaw('LOWER(email) = ?', [$normalized])->whereKeyNot($user?->id)->exists()) {
                        $fail('Alamat email ini sudah digunakan oleh akun lain.');
                    }
                },
            ],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'email.max' => 'Alamat email terlalu panjang.',
        ];
    }
}
