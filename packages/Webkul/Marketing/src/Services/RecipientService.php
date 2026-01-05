<?php

namespace Webkul\Marketing\Services;

use Webkul\Marketing\Repositories\CampaignRecipientRepository;
use Webkul\Contact\Repositories\PersonRepository;
use Webkul\Lead\Repositories\LeadRepository;

class RecipientService
{
    public function __construct(
        protected CampaignRecipientRepository $recipientRepository,
        protected PersonRepository $personRepository,
        protected LeadRepository $leadRepository
    ) {}

    /**
     * Get recipients from leads/persons based on filters.
     */
    public function getRecipientsFromFilters(array $filters): array
    {
        $recipients = [];

        // Get persons if filter specified
        if (isset($filters['person_ids']) && is_array($filters['person_ids'])) {
            $persons = $this->personRepository->findWhereIn('id', $filters['person_ids']);
            foreach ($persons as $person) {
                if (!empty($person->emails)) {
                    $recipients[] = [
                        'person_id' => $person->id,
                        'email' => $person->emails[0],
                    ];
                }
            }
        }

        // Get leads if filter specified
        if (isset($filters['lead_ids']) && is_array($filters['lead_ids'])) {
            $leads = $this->leadRepository->findWhereIn('id', $filters['lead_ids']);
            foreach ($leads as $lead) {
                if ($lead->person && !empty($lead->person->emails)) {
                    $recipients[] = [
                        'lead_id' => $lead->id,
                        'person_id' => $lead->person->id,
                        'email' => $lead->person->emails[0],
                    ];
                }
            }
        }

        // Get from tags if specified
        if (isset($filters['tag_ids']) && is_array($filters['tag_ids'])) {
            // This would require tag relationships - simplified for now
            // In full implementation, would query persons/leads with these tags
        }

        return $recipients;
    }

    /**
     * Parse CSV file and return recipients.
     */
    public function parseCsvRecipients(string $csvContent): array
    {
        $lines = explode("\n", trim($csvContent));
        $recipients = [];

        // Skip header row if present
        $startIndex = 0;
        if (count($lines) > 0 && (stripos($lines[0], 'email') !== false || stripos($lines[0], 'name') !== false)) {
            $startIndex = 1;
        }

        for ($i = $startIndex; $i < count($lines); $i++) {
            $line = trim($lines[$i]);
            if (empty($line)) {
                continue;
            }

            $data = str_getcsv($line);
            if (count($data) > 0 && filter_var($data[0], FILTER_VALIDATE_EMAIL)) {
                $recipients[] = [
                    'email' => $data[0],
                    'name' => $data[1] ?? null,
                ];
            }
        }

        return $recipients;
    }

    /**
     * Validate recipient email.
     */
    public function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}

