<?php

namespace Webkul\Marketplace\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Webkul\Marketplace\Contracts\ExtensionInstallation;
use Webkul\Marketplace\Contracts\ExtensionVersion;

class ExtensionUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public ExtensionInstallation $installation,
        public ExtensionVersion $newVersion,
        public ExtensionVersion $oldVersion
    ) {
    }
}
