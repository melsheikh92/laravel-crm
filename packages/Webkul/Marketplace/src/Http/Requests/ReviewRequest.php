<?php

namespace Webkul\Marketplace\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Webkul\Marketplace\Rules\NoProfanity;

class ReviewRequest extends FormRequest
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
        $reviewId = $this->route('id') ?? $this->route('review');

        return [
            'extension_id' => [
                'required',
                'integer',
                Rule::exists('extensions', 'id')->where(function ($query) {
                    $query->where('status', 'approved');
                }),
            ],
            'rating' => [
                'required',
                'integer',
                'min:1',
                'max:5',
            ],
            'title' => [
                'nullable',
                'string',
                'max:255',
                new NoProfanity(),
            ],
            'review_text' => [
                'required',
                'string',
                'min:10',
                'max:2000',
                new NoProfanity(),
            ],
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
            'extension_id' => 'extension',
            'rating' => 'rating',
            'title' => 'review title',
            'review_text' => 'review',
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
            'extension_id.required' => 'Please select an extension to review.',
            'extension_id.exists' => 'The selected extension does not exist or is not available for review.',
            'rating.required' => 'Please provide a rating for this extension.',
            'rating.integer' => 'The rating must be a whole number.',
            'rating.min' => 'The rating must be at least 1 star.',
            'rating.max' => 'The rating cannot exceed 5 stars.',
            'title.max' => 'The review title must not exceed 255 characters.',
            'review_text.required' => 'Please provide a review description.',
            'review_text.string' => 'The review must be a valid text.',
            'review_text.min' => 'The review must be at least 10 characters long. Please provide more details about your experience.',
            'review_text.max' => 'The review must not exceed 2000 characters. Please keep your review concise.',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        // Trim whitespace from text fields
        if ($this->has('title')) {
            $this->merge([
                'title' => trim($this->input('title')),
            ]);
        }

        if ($this->has('review_text')) {
            $this->merge([
                'review_text' => trim($this->input('review_text')),
            ]);
        }

        // Convert rating to integer if it's a string
        if ($this->has('rating') && is_string($this->input('rating'))) {
            $this->merge([
                'rating' => (int) $this->input('rating'),
            ]);
        }
    }
}
