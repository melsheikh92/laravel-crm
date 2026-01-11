<?php

namespace Webkul\Marketplace\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Webkul\Marketplace\Contracts\ExtensionInstallation;

class ExtensionUninstalled
{
    use Dispatchable, SerializesModels;

    public function __construct(public ExtensionInstallation $installation)
    {
    }
}
