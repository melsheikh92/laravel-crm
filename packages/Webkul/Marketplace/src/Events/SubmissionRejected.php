<?php

namespace Webkul\Marketplace\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Webkul\Marketplace\Contracts\ExtensionSubmission;

class SubmissionRejected
{
    use Dispatchable, SerializesModels;

    public function __construct(public ExtensionSubmission $submission)
    {
    }
}
