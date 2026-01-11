<?php

namespace Webkul\Marketplace\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubmissionReviewRequest extends FormRequest
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
        return [
            'status' => [
                'required',
                'string',
                Rule::in(['approved', 'rejected']),
            ],
            'review_notes' => [
                Rule::requiredIf($this->input('status') === 'rejected'),
                'nullable',
                'string',
                'min:10',
                'max:5000',
            ],
            'security_scan_results' => 'nullable|array',
            'security_scan_results.passed' => 'nullable|boolean',
            'security_scan_results.scanned_at' => 'nullable|date',
            'security_scan_results.issues' => 'nullable|array',
            'security_scan_results.issues.*.severity' => [
                'nullable',
                'string',
                Rule::in(['low', 'medium', 'high', 'critical']),
            ],
            'security_scan_results.issues.*.type' => 'nullable|string|max:255',
            'security_scan_results.issues.*.description' => 'nullable|string|max:1000',
            'security_scan_results.issues.*.file' => 'nullable|string|max:500',
            'security_scan_results.issues.*.line' => 'nullable|integer|min:1',
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
            'status' => 'review status',
            'review_notes' => 'review notes',
            'security_scan_results' => 'security scan results',
            'security_scan_results.passed' => 'security scan status',
            'security_scan_results.scanned_at' => 'scan date',
            'security_scan_results.issues' => 'security issues',
            'security_scan_results.issues.*.severity' => 'issue severity',
            'security_scan_results.issues.*.type' => 'issue type',
            'security_scan_results.issues.*.description' => 'issue description',
            'security_scan_results.issues.*.file' => 'affected file',
            'security_scan_results.issues.*.line' => 'line number',
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
            'status.required' => 'Please select a review status (approved or rejected).',
            'status.in' => 'The review status must be either "approved" or "rejected".',
            'review_notes.required' => 'Review notes are required when rejecting a submission. Please provide detailed feedback to help the developer.',
            'review_notes.min' => 'Review notes must be at least 10 characters long. Please provide more details.',
            'review_notes.max' => 'Review notes must not exceed 5000 characters. Please keep your feedback concise.',
            'security_scan_results.array' => 'Security scan results must be a valid data structure.',
            'security_scan_results.passed.boolean' => 'Security scan status must be true or false.',
            'security_scan_results.scanned_at.date' => 'Scan date must be a valid date.',
            'security_scan_results.issues.array' => 'Security issues must be a valid list.',
            'security_scan_results.issues.*.severity.in' => 'Issue severity must be one of: low, medium, high, or critical.',
            'security_scan_results.issues.*.type.max' => 'Issue type must not exceed 255 characters.',
            'security_scan_results.issues.*.description.max' => 'Issue description must not exceed 1000 characters.',
            'security_scan_results.issues.*.file.max' => 'File path must not exceed 500 characters.',
            'security_scan_results.issues.*.line.min' => 'Line number must be at least 1.',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        // Trim whitespace from review notes
        if ($this->has('review_notes')) {
            $this->merge([
                'review_notes' => trim($this->input('review_notes')),
            ]);
        }

        // Ensure status is lowercase
        if ($this->has('status')) {
            $this->merge([
                'status' => strtolower(trim($this->input('status'))),
            ]);
        }

        // Convert string booleans to actual booleans for security scan results
        if ($this->has('security_scan_results.passed') && is_string($this->input('security_scan_results.passed'))) {
            $passed = $this->input('security_scan_results.passed');
            $this->merge([
                'security_scan_results' => array_merge(
                    $this->input('security_scan_results', []),
                    ['passed' => filter_var($passed, FILTER_VALIDATE_BOOLEAN)]
                ),
            ]);
        }
    }

    /**
     * Get the validated data from the request with custom processing.
     *
     * @param  array|null  $keys
     * @return array
     */
    public function validated($keys = null, $default = null): array
    {
        $validated = parent::validated($keys, $default);

        // Remove empty review notes if not rejecting
        if (isset($validated['status']) && $validated['status'] === 'approved' && empty($validated['review_notes'])) {
            unset($validated['review_notes']);
        }

        return $validated;
    }
}
