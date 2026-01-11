<?php

namespace Webkul\Marketplace\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Webkul\Marketplace\Contracts\Extension;
use Webkul\Marketplace\Contracts\ExtensionVersion;

class ExtensionUpdateAvailable extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param  \Webkul\Marketplace\Contracts\Extension  $extension
     * @param  \Webkul\Marketplace\Contracts\ExtensionVersion  $newVersion
     * @param  \Webkul\Marketplace\Contracts\ExtensionVersion  $currentVersion
     * @return void
     */
    public function __construct(
        public Extension $extension,
        public ExtensionVersion $newVersion,
        public ExtensionVersion $currentVersion
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
        return (new MailMessage)
            ->subject(trans('marketplace::app.notifications.extension-update.subject', ['name' => $this->extension->name]))
            ->view('marketplace::emails.notifications.extension-update', [
                'user_name'       => $notifiable->name,
                'extension_name'  => $this->extension->name,
                'current_version' => $this->currentVersion->version,
                'new_version'     => $this->newVersion->version,
                'changelog'       => $this->newVersion->changelog,
                'extension_url'   => route('marketplace.extensions.show', $this->extension->slug),
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
            'extension_id'    => $this->extension->id,
            'extension_name'  => $this->extension->name,
            'extension_slug'  => $this->extension->slug,
            'current_version' => $this->currentVersion->version,
            'new_version'     => $this->newVersion->version,
            'changelog'       => $this->newVersion->changelog,
            'type'            => 'extension_update',
        ];
    }
}
