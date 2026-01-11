<?php

namespace Webkul\Marketplace\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Webkul\Marketplace\Contracts\ExtensionSubmission;

class SubmissionStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param  \Webkul\Marketplace\Contracts\ExtensionSubmission  $submission
     * @param  string  $status
     * @return void
     */
    public function __construct(
        public ExtensionSubmission $submission,
        public string $status
    ) {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $viewTemplate = $this->status === 'approved'
            ? 'marketplace::emails.notifications.submission-approved'
            : 'marketplace::emails.notifications.submission-rejected';

        return (new MailMessage)
            ->subject(trans('marketplace::app.notifications.submission-status.subject', [
                'status' => ucfirst($this->status),
            ]))
            ->view($viewTemplate, [
                'user_name'      => $notifiable->name,
                'extension_name' => $this->submission->extension->name ?? $this->submission->extension_version->extension->name ?? 'Extension',
                'version'        => $this->submission->extension_version->version ?? 'N/A',
                'status'         => $this->status,
                'review_notes'   => $this->submission->review_notes,
                'submission_url' => route('developer.submissions.show', $this->submission->id),
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'submission_id'   => $this->submission->id,
            'extension_id'    => $this->submission->extension_id,
            'extension_name'  => $this->submission->extension->name ?? $this->submission->extension_version->extension->name ?? 'Extension',
            'version'         => $this->submission->extension_version->version ?? null,
            'status'          => $this->status,
            'review_notes'    => $this->submission->review_notes,
            'type'            => 'submission_status',
        ];
    }
}
