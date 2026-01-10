<?php

namespace Webkul\Marketing\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Webkul\Marketing\Repositories\EmailCampaignRepository;
use Webkul\Marketing\Repositories\CampaignRecipientRepository;
use Webkul\Marketing\Repositories\EmailTemplateRepository;
use Webkul\Marketing\Jobs\SendCampaignJob;
use Webkul\Marketing\Jobs\SendCampaignEmailJob;

class CampaignService
{
    public function __construct(
        protected EmailCampaignRepository $campaignRepository,
        protected CampaignRecipientRepository $recipientRepository,
        protected EmailTemplateRepository $templateRepository
    ) {
    }

    /**
     * Create a new campaign.
     */
    public function create(array $data): \Webkul\Marketing\Contracts\EmailCampaign
    {
        $data['user_id'] = auth()->guard('user')->id();
        $data['status'] = $data['status'] ?? 'draft';

        // If scheduled, validate scheduled_at
        if ($data['status'] === 'scheduled' && empty($data['scheduled_at'])) {
            throw new Exception('Scheduled campaigns must have a scheduled_at date.');
        }

        $campaign = $this->campaignRepository->create($data);

        // Add recipients if provided
        if (isset($data['recipients']) && is_array($data['recipients'])) {
            $this->addRecipients($campaign->id, $data['recipients']);
        }

        return $campaign;
    }

    /**
     * Update a campaign.
     */
    public function update(array $data, int $campaignId): \Webkul\Marketing\Contracts\EmailCampaign
    {
        $campaign = $this->campaignRepository->findOrFail($campaignId);

        // Prevent updating campaigns that are sending or completed
        if (in_array($campaign->status, ['sending', 'completed'])) {
            throw new Exception('Cannot update a campaign that is sending or completed.');
        }

        // If status is being changed to scheduled, validate scheduled_at
        if (isset($data['status']) && $data['status'] === 'scheduled' && empty($data['scheduled_at'])) {
            throw new Exception('Scheduled campaigns must have a scheduled_at date.');
        }

        $campaign = $this->campaignRepository->update($data, $campaignId);

        // Update recipients if provided
        if (isset($data['recipients']) && is_array($data['recipients'])) {
            // Remove existing recipients
            $this->recipientRepository->deleteWhere(['campaign_id' => $campaignId]);
            // Add new recipients
            $this->addRecipients($campaignId, $data['recipients']);
        }

        return $campaign;
    }

    /**
     * Add recipients to a campaign.
     */
    public function addRecipients(int $campaignId, array $recipients): void
    {
        $campaign = $this->campaignRepository->findOrFail($campaignId);

        foreach ($recipients as $recipient) {
            $recipientData = [
                'campaign_id' => $campaignId,
                'email' => $recipient['email'] ?? null,
                'person_id' => $recipient['person_id'] ?? null,
                'lead_id' => $recipient['lead_id'] ?? null,
                'status' => 'pending',
            ];

            if (empty($recipientData['email'])) {
                // Try to get email from person or lead
                if ($recipientData['person_id']) {
                    $person = app(\Webkul\Contact\Repositories\PersonRepository::class)->find($recipientData['person_id']);
                    $recipientData['email'] = $person->emails[0] ?? null;
                } elseif ($recipientData['lead_id']) {
                    $lead = app(\Webkul\Lead\Repositories\LeadRepository::class)->find($recipientData['lead_id']);
                    if ($lead->person) {
                        $recipientData['email'] = $lead->person->emails[0] ?? null;
                        $recipientData['person_id'] = $lead->person->id;
                    }
                }
            }

            if ($recipientData['email']) {
                $this->recipientRepository->create($recipientData);
            }
        }
    }

    /**
     * Schedule a campaign for sending.
     */
    public function schedule(int $campaignId, ?string $scheduledAt = null): \Webkul\Marketing\Contracts\EmailCampaign
    {
        $campaign = $this->campaignRepository->findOrFail($campaignId);

        if ($campaign->status !== 'draft') {
            throw new Exception('Only draft campaigns can be scheduled.');
        }

        if (empty($campaign->recipients) || $campaign->recipients->count() === 0) {
            throw new Exception('Campaign must have at least one recipient before scheduling.');
        }

        $data = [
            'status' => 'scheduled',
            'scheduled_at' => $scheduledAt ?? now()->addHour(),
        ];

        $campaign = $this->campaignRepository->update($data, $campaignId);

        // Dispatch job to send at scheduled time
        SendCampaignJob::dispatch($campaignId)->delay($campaign->scheduled_at);

        return $campaign;
    }

    /**
     * Start sending a campaign.
     */
    public function send(int $campaignId): \Webkul\Marketing\Contracts\EmailCampaign
    {
        $campaign = $this->campaignRepository->findOrFail($campaignId);

        if (!in_array($campaign->status, ['draft', 'scheduled'])) {
            throw new Exception('Campaign cannot be sent. Current status: ' . $campaign->status);
        }

        if (empty($campaign->recipients) || $campaign->recipients->count() === 0) {
            throw new Exception('Campaign must have at least one recipient.');
        }

        // Update status to sending
        $this->campaignRepository->update([
            'status' => 'sending',
            'started_at' => now(),
        ], $campaignId);

        // Dispatch job to send campaign
        SendCampaignJob::dispatch($campaignId);

        return $this->campaignRepository->find($campaignId);
    }

    /**
     * Cancel a campaign.
     */
    public function cancel(int $campaignId): \Webkul\Marketing\Contracts\EmailCampaign
    {
        $campaign = $this->campaignRepository->findOrFail($campaignId);

        if (!in_array($campaign->status, ['draft', 'scheduled', 'sending'])) {
            throw new Exception('Campaign cannot be cancelled. Current status: ' . $campaign->status);
        }

        return $this->campaignRepository->update([
            'status' => 'cancelled',
        ], $campaignId);
    }

    /**
     * Get campaign statistics.
     */
    public function getStatistics(int $campaignId): array
    {
        $campaign = $this->campaignRepository->findOrFail($campaignId);
        $total = $this->recipientRepository->findWhere(['campaign_id' => $campaignId])->count();

        $stats = [
            'total_recipients' => $total,
            'sent' => $this->recipientRepository->findWhere([['campaign_id', '=', $campaignId], ['sent_at', '<>', null]])->count(),
            'failed' => $this->recipientRepository->findWhere(['campaign_id' => $campaignId, 'status' => 'failed'])->count(),
            'bounced' => $this->recipientRepository->findWhere(['campaign_id' => $campaignId, 'status' => 'bounced'])->count(),
            'unsubscribed' => $this->recipientRepository->findWhere(['campaign_id' => $campaignId, 'status' => 'unsubscribed'])->count(),
            'opened' => $this->recipientRepository->findWhere([['campaign_id', '=', $campaignId], ['opened_at', '<>', null]])->count(),
            'clicked' => $this->recipientRepository->findWhere([['campaign_id', '=', $campaignId], ['clicked_at', '<>', null]])->count(),
        ];

        // Calculate rates
        $sentCount = $stats['sent'];
        $stats['open_rate'] = $sentCount > 0 ? round(($stats['opened'] / $sentCount) * 100, 1) : 0;
        $stats['click_rate'] = $sentCount > 0 ? round(($stats['clicked'] / $sentCount) * 100, 1) : 0;

        // Timeline data (last 24 hours of activity or since start)
        $timeline = DB::table('email_campaign_recipients')
            ->select(DB::raw('DATE_FORMAT(opened_at, "%Y-%m-%d %H:00:00") as hour'), DB::raw('count(*) as count'))
            ->where('campaign_id', $campaignId)
            ->whereNotNull('opened_at')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        $stats['timeline'] = $timeline;

        return $stats;
    }
}

