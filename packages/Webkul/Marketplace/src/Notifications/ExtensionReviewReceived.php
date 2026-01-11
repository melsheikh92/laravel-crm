<?php

namespace Webkul\Marketplace\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Webkul\Marketplace\Contracts\ExtensionReview;

class ExtensionReviewReceived extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param  \Webkul\Marketplace\Contracts\ExtensionReview  $review
     * @return void
     */
    public function __construct(public ExtensionReview $review)
    {
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
            ->subject(trans('marketplace::app.notifications.new-review.subject', [
                'name' => $this->review->extension->name,
            ]))
            ->view('marketplace::emails.notifications.new-review', [
                'user_name'      => $notifiable->name,
                'extension_name' => $this->review->extension->name,
                'reviewer_name'  => $this->review->user->name,
                'rating'         => $this->review->rating,
                'review_title'   => $this->review->title,
                'review_text'    => $this->review->review_text,
                'extension_url'  => route('developer.extensions.show', $this->review->extension->id),
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
            'review_id'       => $this->review->id,
            'extension_id'    => $this->review->extension_id,
            'extension_name'  => $this->review->extension->name,
            'reviewer_name'   => $this->review->user->name,
            'reviewer_id'     => $this->review->user_id,
            'rating'          => $this->review->rating,
            'review_title'    => $this->review->title,
            'review_text'     => $this->review->review_text,
            'type'            => 'new_review',
        ];
    }
}
