<?php

namespace Webkul\Marketplace\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Webkul\Marketplace\Contracts\ExtensionReview;

class ExtensionReviewed
{
    use Dispatchable, SerializesModels;

    public function __construct(public ExtensionReview $review)
    {
    }
}
