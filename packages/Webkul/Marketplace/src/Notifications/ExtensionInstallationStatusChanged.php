<?php

namespace Webkul\Marketplace\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Webkul\Marketplace\Contracts\ExtensionInstallation;

class ExtensionInstallationStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param  \Webkul\Marketplace\Contracts\ExtensionInstallation  $installation
     * @param  string  $status
     * @param  string|null  $message
     * @return void
     */
    public function __construct(
        public ExtensionInstallation $installation,
        public string $status,
        public ?string $message = null
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
        $mailMessage = (new MailMessage)
            ->subject(trans('marketplace::app.notifications.installation-status.subject', [
                'name' => $this->installation->extension->name,
            ]))
            ->view('marketplace::emails.notifications.installation-status', [
                'user_name'      => $notifiable->name,
                'extension_name' => $this->installation->extension->name,
                'version'        => $this->installation->version->version,
                'status'         => $this->status,
                'message'        => $this->message,
                'extension_url'  => route('marketplace.my-extensions.index'),
            ]);

        return $mailMessage;
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
            'installation_id' => $this->installation->id,
            'extension_id'    => $this->installation->extension_id,
            'extension_name'  => $this->installation->extension->name,
            'version'         => $this->installation->version->version,
            'status'          => $this->status,
            'message'         => $this->message,
            'type'            => 'installation_status',
        ];
    }
}
