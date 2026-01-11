<?php

namespace Webkul\Marketplace\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExtensionRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $extensionId = $this->route('id') ?? $this->route('extension');

        return [
            'name' => 'required|string|max:255',
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('extensions', 'slug')->ignore($extensionId),
            ],
            'description' => 'nullable|string|max:1000',
            'long_description' => 'nullable|string',
            'type' => [
                'required',
                'string',
                Rule::in(['plugin', 'theme', 'integration']),
            ],
            'category_id' => [
                'nullable',
                'integer',
                Rule::exists('extension_categories', 'id'),
            ],
            'price' => 'nullable|numeric|min:0|max:999999.99',
            'logo' => 'nullable|image|mimes:jpeg,jpg,png,gif,svg|max:2048',
            'screenshots' => 'nullable|array|max:10',
            'screenshots.*' => 'image|mimes:jpeg,jpg,png,gif|max:5120',
            'documentation_url' => 'nullable|url|max:255',
            'demo_url' => 'nullable|url|max:255',
            'repository_url' => 'nullable|url|max:255',
            'support_email' => 'nullable|email|max:255',
            'tags' => 'nullable|array|max:20',
            'tags.*' => 'string|max:50',
            'requirements' => 'nullable|array',
            'requirements.php_version' => 'nullable|string|max:50',
            'requirements.laravel_version' => 'nullable|string|max:50',
            'requirements.extensions' => 'nullable|array',
            'requirements.extensions.*' => 'string|max:100',
        ];
    }

    /**
     * Get custom attribute names for validation errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'extension name',
            'slug' => 'extension slug',
            'description' => 'short description',
            'long_description' => 'detailed description',
            'type' => 'extension type',
            'category_id' => 'category',
            'price' => 'price',
            'logo' => 'logo image',
            'screenshots' => 'screenshots',
            'screenshots.*' => 'screenshot',
            'documentation_url' => 'documentation URL',
            'demo_url' => 'demo URL',
            'repository_url' => 'repository URL',
            'support_email' => 'support email',
            'tags' => 'tags',
            'tags.*' => 'tag',
            'requirements' => 'requirements',
            'requirements.php_version' => 'PHP version requirement',
            'requirements.laravel_version' => 'Laravel version requirement',
            'requirements.extensions' => 'required extensions',
            'requirements.extensions.*' => 'required extension',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The extension name is required.',
            'name.max' => 'The extension name must not exceed 255 characters.',
            'slug.unique' => 'This slug is already taken. Please choose a different one.',
            'slug.alpha_dash' => 'The slug may only contain letters, numbers, dashes and underscores.',
            'type.required' => 'Please select an extension type.',
            'type.in' => 'The selected extension type is invalid. Valid types are: plugin, theme, or integration.',
            'category_id.exists' => 'The selected category does not exist.',
            'price.numeric' => 'The price must be a valid number.',
            'price.min' => 'The price must be at least 0.',
            'price.max' => 'The price must not exceed 999,999.99.',
            'logo.image' => 'The logo must be an image file.',
            'logo.mimes' => 'The logo must be a file of type: jpeg, jpg, png, gif, or svg.',
            'logo.max' => 'The logo must not be larger than 2MB.',
            'screenshots.max' => 'You can upload a maximum of 10 screenshots.',
            'screenshots.*.image' => 'Each screenshot must be an image file.',
            'screenshots.*.mimes' => 'Screenshots must be files of type: jpeg, jpg, png, or gif.',
            'screenshots.*.max' => 'Each screenshot must not be larger than 5MB.',
            'documentation_url.url' => 'The documentation URL must be a valid URL.',
            'demo_url.url' => 'The demo URL must be a valid URL.',
            'repository_url.url' => 'The repository URL must be a valid URL.',
            'support_email.email' => 'The support email must be a valid email address.',
            'tags.max' => 'You can add a maximum of 20 tags.',
            'tags.*.max' => 'Each tag must not exceed 50 characters.',
        ];
    }
}
