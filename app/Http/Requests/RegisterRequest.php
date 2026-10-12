<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'min:3',
                'max:30',
                'regex:/^[a-zA-Z0-9_]+$/',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (User::whereRaw('LOWER(username) = ?', [strtolower((string) $value)])->exists()) {
                        $fail('Username ini sudah digunakan. Silakan gunakan username lain.');
                    }
                },
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (User::whereRaw('LOWER(email) = ?', [strtolower((string) $value)])->exists()) {
                        $fail('Email ini sudah terdaftar. Silakan gunakan email lain atau masuk ke akun Anda.');
                    }
                },
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
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
            'username.regex' => 'Username hanya boleh berisi huruf, angka, dan underscore.',
            'username.unique' => 'Username ini sudah digunakan. Silakan gunakan username lain.',
            'email.unique' => 'Email ini sudah terdaftar. Silakan gunakan email lain atau masuk ke akun Anda.',
        ];
    }
}
