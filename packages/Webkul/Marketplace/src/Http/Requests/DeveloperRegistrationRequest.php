<?php

namespace Webkul\Marketplace\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeveloperRegistrationRequest extends FormRequest
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
     * @return array
     */
    public function rules(): array
    {
        return [
            'bio' => 'required|string|min:50|max:1000',
            'company' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'support_email' => 'required|email|max:255',
            'github_url' => 'nullable|url|max:255',
            'twitter_url' => 'nullable|url|max:255',
            'linkedin_url' => 'nullable|url|max:255',
            'terms_accepted' => 'required|accepted',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes(): array
    {
        return [
            'bio' => trans('marketplace::app.developer-registration.bio'),
            'company' => trans('marketplace::app.developer-registration.company'),
            'website' => trans('marketplace::app.developer-registration.website'),
            'support_email' => trans('marketplace::app.developer-registration.support-email'),
            'github_url' => trans('marketplace::app.developer-registration.github-url'),
            'twitter_url' => trans('marketplace::app.developer-registration.twitter-url'),
            'linkedin_url' => trans('marketplace::app.developer-registration.linkedin-url'),
            'terms_accepted' => trans('marketplace::app.developer-registration.terms-accepted'),
        ];
    }

    /**
     * Get custom error messages.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'bio.required' => trans('marketplace::app.developer-registration.bio-required'),
            'bio.min' => trans('marketplace::app.developer-registration.bio-min'),
            'support_email.required' => trans('marketplace::app.developer-registration.support-email-required'),
            'terms_accepted.accepted' => trans('marketplace::app.developer-registration.terms-must-accept'),
        ];
    }
}
