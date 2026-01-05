<?php

namespace Webkul\Marketing\Mails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CampaignMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public array $data
    ) {}

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->from($this->data['from']['address'], $this->data['from']['name'] ?? null)
            ->replyTo($this->data['reply_to'] ?? $this->data['from']['address'])
            ->subject($this->data['subject'])
            ->html($this->data['content']);
    }
}

