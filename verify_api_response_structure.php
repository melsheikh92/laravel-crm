<?php

use Webkul\Admin\Http\Resources\LeadResource;
use Webkul\Admin\Http\Resources\StageResource;
use Webkul\Lead\Repositories\LeadRepository;
use Webkul\Lead\Repositories\PipelineRepository;
use Prettus\Repository\Criteria\RequestCriteria;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $pipelineRepo = app(PipelineRepository::class);
    $pipeline = $pipelineRepo->getDefaultPipeline();
    $stage = $pipeline->stages->first();

    echo "Testing Stage: " . $stage->name . "\n";

    $query = app(LeadRepository::class)->where([
        'lead_pipeline_id' => $pipeline->id,
        'lead_pipeline_stage_id' => $stage->id,
    ]);

    $paginator = $query->with([
        'tags',
        'type',
        'source',
        'user',
        'person',
        'person.organization',
        'pipeline',
        'pipeline.stages',
        'stage',
        'attribute_values',
    ])->paginate(10);

    // Mimic the controller logic exactly, but with resolve()
    $resourceCollection = LeadResource::collection($paginator->getCollection())->resolve();

    // Simulate JSON serialization
    $json = json_encode($resourceCollection);

    echo "Output Type: " . gettype($resourceCollection) . "\n";
    echo "JSON Output (First 500 chars):\n" . substr($json, 0, 500) . "...\n";

    // Decode to check structure
    $decoded = json_decode($json, true);
    if (isset($decoded['data']) && is_array($decoded['data'])) {
        echo "WARNING: Output is wrapped in 'data' key!\n";
    } elseif (array_keys($decoded) === range(0, count($decoded) - 1)) {
        echo "SUCCESS: Output is a plain indexed array.\n";
    } else {
        echo "WARNING: Output is an Object/Associative Array.\n";
    }

} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
