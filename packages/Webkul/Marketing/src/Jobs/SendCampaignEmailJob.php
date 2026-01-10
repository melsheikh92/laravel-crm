<?php

namespace Webkul\Marketing\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Webkul\Marketing\Repositories\EmailCampaignRepository;
use Webkul\Marketing\Repositories\CampaignRecipientRepository;
use Webkul\Marketing\Services\TemplateService;
use Webkul\Marketing\Mails\CampaignMail;

class SendCampaignEmailJob implements ShouldQueue
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
        public int $recipientId
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(
        EmailCampaignRepository $campaignRepository,
        CampaignRecipientRepository $recipientRepository,
        TemplateService $templateService
    ): void {
        $recipient = $recipientRepository->findOrFail($this->recipientId);
        $campaign = $campaignRepository->findOrFail($recipient->campaign_id);

        // Check if campaign is still active
        if (in_array($campaign->status, ['cancelled', 'completed'])) {
            $recipientRepository->update([
                'status' => 'failed',
                'error_message' => 'Campaign was cancelled or completed',
            ], $this->recipientId);
            return;
        }

        try {
            // Get email content
            $subject = $campaign->subject;
            $content = $campaign->content;

            // If template is used, render it
            if ($campaign->template_id) {
                if ($recipient->person_id) {
                    $rendered = $templateService->renderForPerson($campaign->template_id, $recipient->person_id);
                } elseif ($recipient->lead_id) {
                    $rendered = $templateService->renderForLead($campaign->template_id, $recipient->lead_id);
                } else {
                    $rendered = $templateService->render($campaign->template_id, ['email' => $recipient->email]);
                }
                $subject = $rendered['subject'];
                $content = $rendered['content'];
            } else {
                // Replace basic variables even without template
                $variables = ['email' => $recipient->email];
                if ($recipient->person_id) {
                    $person = app(\Webkul\Contact\Repositories\PersonRepository::class)->find($recipient->person_id);
                    $variables['name'] = $person->name ?? '';
                    $variables['company'] = $person->organization->name ?? '';
                }
                $content = str_replace('{{email}}', $variables['email'], $content);
                $content = str_replace('{{name}}', $variables['name'] ?? '', $content);
                $content = str_replace('{{company}}', $variables['company'] ?? '', $content);
            }

            // Send email
            $senderEmail = $campaign->sender_email ?? config('mail.from.address');
            $senderName = $campaign->sender_name ?? config('mail.from.name');
            $replyTo = $campaign->reply_to ?? $senderEmail;

            // Process content for tracking (Pixel + Links)
            $content = $this->processContentForTracking($content, $recipient->id);

            Mail::to($recipient->email)->send(new CampaignMail([
                'subject' => $subject,
                'content' => $content,
                'from' => ['address' => $senderEmail, 'name' => $senderName],
                'reply_to' => $replyTo,
            ]));

            // Update recipient status
            $recipientRepository->update([
                'status' => 'sent',
                'sent_at' => now(),
            ], $this->recipientId);

            // Update campaign sent count
            $campaignRepository->update([
                'sent_count' => $campaign->sent_count + 1,
            ], $campaign->id);

        } catch (Exception $e) {
            // Update recipient status to failed
            $recipientRepository->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ], $this->recipientId);

            // Update campaign failed count
            $campaignRepository->update([
                'failed_count' => $campaign->failed_count + 1,
            ], $campaign->id);

            throw $e;
        }
    }

    /**
     * Inject tracking pixel and wrap links
     */
    protected function processContentForTracking(string $content, int $recipientId): string
    {
        // 1. Inject Tracking Pixel
        $trackingUrl = route('marketing.track.open', $recipientId);
        $pixel = '<img src="' . $trackingUrl . '" width="1" height="1" style="display:none;" alt="" />';

        if (strpos($content, '</body>') !== false) {
            $content = str_replace('</body>', $pixel . '</body>', $content);
        } else {
            $content .= $pixel;
        }

        // 2. Wrap Links
        $content = preg_replace_callback('/<a\s+(?:[^>]*\s+)?href=["\']([^"\']*)["\']([^>]*)>/i', function ($matches) use ($recipientId) {
            $originalUrl = $matches[1];
            $otherAttributes = $matches[2];

            // Skip anchor links, mailto:, javascript:, or existing tracking links
            if (
                strpos($originalUrl, '#') === 0 ||
                strpos($originalUrl, 'mailto:') === 0 ||
                strpos($originalUrl, 'tel:') === 0 ||
                strpos($originalUrl, 'javascript:') === 0
            ) {
                return $matches[0];
            }

            $trackingLink = route('marketing.track.click', [
                'id' => $recipientId,
                'url' => $originalUrl
            ]);

            return '<a href="' . $trackingLink . '" ' . $otherAttributes . '>';
        }, $content);

        return $content;
    }
}

