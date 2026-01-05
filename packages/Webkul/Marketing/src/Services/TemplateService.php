<?php

namespace Webkul\Marketing\Services;

use Webkul\Marketing\Repositories\EmailTemplateRepository;
use Webkul\Contact\Repositories\PersonRepository;
use Webkul\Lead\Repositories\LeadRepository;

class TemplateService
{
    public function __construct(
        protected EmailTemplateRepository $templateRepository,
        protected PersonRepository $personRepository,
        protected LeadRepository $leadRepository
    ) {}

    /**
     * Create a new email template.
     */
    public function create(array $data): \Webkul\Marketing\Contracts\EmailTemplate
    {
        $data['user_id'] = auth()->guard('user')->id() ?? null;
        $data['type'] = $data['type'] ?? 'custom';
        $data['is_active'] = $data['is_active'] ?? true;

        // Default variables if not provided
        if (empty($data['variables'])) {
            $data['variables'] = [
                'name' => 'Contact name',
                'company' => 'Company name',
                'email' => 'Email address',
                'phone' => 'Phone number',
            ];
        }

        return $this->templateRepository->create($data);
    }

    /**
     * Update an email template.
     */
    public function update(array $data, int $templateId): \Webkul\Marketing\Contracts\EmailTemplate
    {
        return $this->templateRepository->update($data, $templateId);
    }

    /**
     * Render template with variables.
     */
    public function render(int $templateId, array $variables = []): array
    {
        $template = $this->templateRepository->findOrFail($templateId);

        $subject = $this->replaceVariables($template->subject, $variables);
        $content = $this->replaceVariables($template->content, $variables);

        return [
            'subject' => $subject,
            'content' => $content,
        ];
    }

    /**
     * Render template content for a person.
     */
    public function renderForPerson(int $templateId, int $personId): array
    {
        $person = $this->personRepository->findOrFail($personId);

        $variables = [
            'name' => $person->name ?? '',
            'company' => $person->organization->name ?? '',
            'email' => $person->emails[0] ?? '',
            'phone' => $person->contact_numbers[0] ?? '',
        ];

        return $this->render($templateId, $variables);
    }

    /**
     * Render template content for a lead.
     */
    public function renderForLead(int $templateId, int $leadId): array
    {
        $lead = $this->leadRepository->findOrFail($leadId);

        $variables = [
            'name' => $lead->person->name ?? $lead->title ?? '',
            'company' => $lead->person->organization->name ?? '',
            'email' => $lead->person->emails[0] ?? '',
            'phone' => $lead->person->contact_numbers[0] ?? '',
            'lead_title' => $lead->title ?? '',
            'lead_value' => $lead->lead_value ?? 0,
        ];

        return $this->render($templateId, $variables);
    }

    /**
     * Replace variables in text.
     */
    protected function replaceVariables(string $text, array $variables): string
    {
        foreach ($variables as $key => $value) {
            $text = str_replace('{{' . $key . '}}', $value, $text);
            $text = str_replace('{{ ' . $key . ' }}', $value, $text);
        }

        return $text;
    }
}

