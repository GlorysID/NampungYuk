<?php

namespace App\Http\Requests;

use App\Models\Community;
use App\Rules\SafeUrl;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCommunitySettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $community = $this->route('community');

        return $this->user() !== null
            && $community instanceof Community
            && $community->isAdmin($this->user()->id);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:60'],
            'topic' => ['nullable', 'string', 'max:60'],
            'location' => ['nullable', 'string', 'max:80'],
            'website_url' => ['nullable', 'string', 'max:255', new SafeUrl],
            'description' => ['nullable', 'string', 'max:1000'],
            'welcome_message' => ['nullable', 'string', 'max:500'],
            'visibility' => ['required', 'in:public,private'],
            'join_policy' => ['required', 'in:open,approval'],
            'post_policy' => ['required', 'in:all_members,approval_required,admins_only'],
            'allow_member_comments' => ['nullable', 'boolean'],
            'allow_member_images' => ['nullable', 'boolean'],
            'icon_file' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'cover_file' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
            'icon' => ['nullable', 'string', 'max:255', new SafeUrl],
            'remove_icon' => ['nullable', 'boolean'],
            'remove_cover' => ['nullable', 'boolean'],
            'join_questions' => ['nullable', 'array', 'max:3'],
            'join_questions.*' => ['nullable', 'string', 'max:200'],
            'rules_items' => ['nullable', 'array', 'max:10'],
            'rules_items.*.title' => ['nullable', 'string', 'max:80'],
            'rules_items.*.description' => ['nullable', 'string', 'max:300'],
        ];
    }
}
