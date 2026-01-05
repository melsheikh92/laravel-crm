<?php

namespace Webkul\Marketing\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Webkul\Marketing\Repositories\EmailCampaignRepository;
use Webkul\Marketing\Repositories\CampaignRecipientRepository;
use Webkul\Marketing\Services\TemplateService;

class SendCampaignJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $campaignId
    ) {}

    /**
     * Execute the job.
     */
    public function handle(
        EmailCampaignRepository $campaignRepository,
        CampaignRecipientRepository $recipientRepository,
        TemplateService $templateService
    ): void {
        $campaign = $campaignRepository->findOrFail($this->campaignId);

        // Check if campaign should be sent
        if ($campaign->status === 'cancelled') {
            return;
        }

        // Update status to sending if not already
        if ($campaign->status !== 'sending') {
            $campaignRepository->update([
                'status' => 'sending',
                'started_at' => now(),
            ], $this->campaignId);
        }

        // Get pending recipients
        $recipients = $recipientRepository->findWhere([
            'campaign_id' => $this->campaignId,
            'status' => 'pending',
        ]);

        // Dispatch individual email jobs in batches to prevent overload
        $batchSize = 50; // Send 50 emails per batch
        $recipients->chunk($batchSize)->each(function ($batch) {
            foreach ($batch as $recipient) {
                SendCampaignEmailJob::dispatch($recipient->id)->delay(now()->addSeconds(rand(1, 10)));
            }
        });

        // If no more pending recipients, mark campaign as completed
        $pendingCount = $recipientRepository->findWhere([
            'campaign_id' => $this->campaignId,
            'status' => 'pending',
        ])->count();

        if ($pendingCount === 0) {
            $campaignRepository->update([
                'status' => 'completed',
                'completed_at' => now(),
            ], $this->campaignId);
        }
    }
}

