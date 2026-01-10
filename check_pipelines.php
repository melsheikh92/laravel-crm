<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Webkul\Lead\Models\Pipeline;

$pipelines = Pipeline::all();

if ($pipelines->isEmpty()) {
    echo "NO PIPELINES FOUND IN DATABASE.\n";
} else {
    echo "Found " . $pipelines->count() . " pipeline(s):\n\n";
    foreach ($pipelines as $pipeline) {
        echo "Pipeline ID: " . $pipeline->id . "\n";
        echo "Name: " . $pipeline->name . "\n";
        echo "Is Default: " . ($pipeline->is_default ? 'Yes' : 'No') . "\n";
        echo "Stages: " . $pipeline->stages->count() . "\n";
        echo "------------------------\n";
    }
}
