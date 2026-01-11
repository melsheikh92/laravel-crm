<?php

namespace Webkul\Marketplace\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VersionRequest extends FormRequest
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
        $versionId = $this->route('id') ?? $this->route('version');
        $extensionId = $this->route('extensionId') ?? $this->route('extension');

        $rules = [
            'version' => [
                'required',
                'string',
                'regex:/^\d+\.\d+\.\d+(-[a-zA-Z0-9]+(\.[a-zA-Z0-9]+)*)?$/',
                'max:50',
            ],
            'changelog' => 'nullable|string|max:65535',
            'laravel_version' => 'nullable|string|max:50|regex:/^[\d\.\*\^\>\<\=\|\s\-]+$/',
            'crm_version' => 'nullable|string|max:50|regex:/^[\d\.\*\^\>\<\=\|\s\-]+$/',
            'php_version' => 'nullable|string|max:50|regex:/^[\d\.\*\^\>\<\=\|\s\-]+$/',
            'dependencies' => 'nullable|array|max:50',
            'dependencies.*.name' => 'required_with:dependencies|string|max:255',
            'dependencies.*.version' => 'required_with:dependencies|string|max:50',
            'release_date' => 'nullable|date|after_or_equal:today',
            'status' => [
                'nullable',
                'string',
                Rule::in(['draft', 'pending', 'approved', 'rejected', 'archived']),
            ],
            'package' => 'nullable|file|mimes:zip|max:51200',
        ];

        // If this is a create request and we have extension_id in the route
        if (!$versionId && $extensionId) {
            $rules['version'][] = Rule::unique('extension_versions', 'version')
                ->where('extension_id', $extensionId);
        }

        // If this is an update request
        if ($versionId) {
            $rules['version'][] = Rule::unique('extension_versions', 'version')
                ->where('extension_id', $extensionId)
                ->ignore($versionId);
        }

        return $rules;
    }

    /**
     * Get custom attribute names for validation errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'version' => 'version number',
            'changelog' => 'changelog',
            'laravel_version' => 'Laravel version requirement',
            'crm_version' => 'CRM version requirement',
            'php_version' => 'PHP version requirement',
            'dependencies' => 'dependencies',
            'dependencies.*.name' => 'dependency name',
            'dependencies.*.version' => 'dependency version',
            'release_date' => 'release date',
            'status' => 'status',
            'package' => 'package file',
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
            'version.required' => 'The version number is required.',
            'version.regex' => 'The version number must follow semantic versioning format (e.g., 1.0.0 or 1.0.0-beta.1).',
            'version.unique' => 'This version number already exists for this extension. Please use a different version number.',
            'version.max' => 'The version number must not exceed 50 characters.',
            'changelog.max' => 'The changelog must not exceed 65,535 characters.',
            'laravel_version.regex' => 'The Laravel version must be a valid version constraint (e.g., ^10.0, >=10.0, 10.*).',
            'laravel_version.max' => 'The Laravel version requirement must not exceed 50 characters.',
            'crm_version.regex' => 'The CRM version must be a valid version constraint (e.g., ^1.0, >=1.0, 1.*).',
            'crm_version.max' => 'The CRM version requirement must not exceed 50 characters.',
            'php_version.regex' => 'The PHP version must be a valid version constraint (e.g., ^8.1, >=8.1, 8.*).',
            'php_version.max' => 'The PHP version requirement must not exceed 50 characters.',
            'dependencies.array' => 'Dependencies must be an array.',
            'dependencies.max' => 'You can specify a maximum of 50 dependencies.',
            'dependencies.*.name.required_with' => 'Each dependency must have a name.',
            'dependencies.*.name.max' => 'Dependency name must not exceed 255 characters.',
            'dependencies.*.version.required_with' => 'Each dependency must have a version constraint.',
            'dependencies.*.version.max' => 'Dependency version must not exceed 50 characters.',
            'release_date.date' => 'The release date must be a valid date.',
            'release_date.after_or_equal' => 'The release date must be today or a future date.',
            'status.in' => 'The selected status is invalid. Valid statuses are: draft, pending, approved, rejected, or archived.',
            'package.file' => 'The package must be a valid file.',
            'package.mimes' => 'The package must be a ZIP file.',
            'package.max' => 'The package file must not be larger than 50MB.',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        // Trim version number
        if ($this->has('version')) {
            $this->merge([
                'version' => trim($this->input('version')),
            ]);
        }

        // Ensure dependencies is an array if provided
        if ($this->has('dependencies') && is_string($this->input('dependencies'))) {
            try {
                $dependencies = json_decode($this->input('dependencies'), true);
                if (is_array($dependencies)) {
                    $this->merge(['dependencies' => $dependencies]);
                }
            } catch (\Exception $e) {
                // Keep as is, validation will catch the error
            }
        }
    }
}
