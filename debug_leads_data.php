<?php

use Webkul\Lead\Repositories\LeadRepository;
use Webkul\Lead\Repositories\PipelineRepository;
use Webkul\User\Repositories\UserRepository;
use Prettus\Repository\Criteria\RequestCriteria;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking Lead Data...\n";

try {
    // 1. Check Total Leads
    $totalLeads = \DB::table('leads')->count();
    echo "Total Leads in DB: " . $totalLeads . "\n";

    if ($totalLeads === 0) {
        echo "WARNING: No leads found in database. Did the seeder run?\n";
        exit;
    }

    // 2. Check Pipelines
    $pipelineRepo = app(PipelineRepository::class);
    $pipeline = $pipelineRepo->getDefaultPipeline();

    if (!$pipeline) {
        echo "ERROR: No default pipeline found.\n";
        exit;
    }
    echo "Default Pipeline ID: " . $pipeline->id . "\n";

    // 3. Check Stages
    $stages = $pipeline->stages;
    echo "Stages found: " . $stages->count() . "\n";

    // 4. Simulate Controller Query for User ID 1 (Admin)
    echo "Simulating Query for User ID 1...\n";

    // Mocking request parameters if needed
    // request()->merge(['pipeline_id' => $pipeline->id]);

    $leadRepository = app(LeadRepository::class);

    foreach ($stages as $stage) {
        $query = $leadRepository->where([
            'lead_pipeline_id' => $pipeline->id,
            'lead_pipeline_stage_id' => $stage->id,
        ]);

        // Simulate Bouncer permission check (assuming User 1 has all access or limited)
        // Note: We can't easily simulate bouncer() context in CLI without login, 
        // but we can check if leads exist for this stage generally.

        $count = $query->count();
        echo "  Stage '{$stage->name}' (ID: {$stage->id}): {$count} leads found.\n";

        if ($count > 0) {
            $firstLead = $query->first();
            echo "    Sample Lead ID: {$firstLead->id}, Title: {$firstLead->title}\n";
        }
    }

} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
