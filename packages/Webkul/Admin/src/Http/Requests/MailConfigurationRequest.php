<?php

namespace Webkul\Admin\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MailConfigurationRequest extends FormRequest
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
            'email.smtp.account.host'         => 'nullable|string|max:255',
            'email.smtp.account.port'         => 'nullable|integer|min:1|max:65535',
            'email.smtp.account.encryption'   => 'nullable|string|in:tls,ssl',
            'email.smtp.account.username'     => 'nullable|string|max:255',
            'email.smtp.account.password'     => 'nullable|string|max:255',
            'email.smtp.account.from_address' => 'nullable|email|max:255',
            'email.smtp.account.from_name'    => 'nullable|string|max:255',
            'email.imap.account.host'         => 'nullable|string|max:255',
            'email.imap.account.port'         => 'nullable|integer|min:1|max:65535',
            'email.imap.account.encryption'   => 'nullable|string|in:tls,ssl,notls',
            'email.imap.account.username'     => 'nullable|string|max:255',
            'email.imap.account.password'     => 'nullable|string|max:255',
        ];
    }
}
