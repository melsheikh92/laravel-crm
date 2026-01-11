<?php

namespace App\Console\Commands;

use App\Services\Compliance\DataRetentionService;
use Illuminate\Console\Command;

class CleanupExpiredData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'compliance:cleanup-expired-data
                            {--dry-run : Preview changes without actually deleting data}
                            {--force : Force deletion even if auto_delete is disabled}
                            {--model= : Process only specific model type}
                            {--stats : Show retention statistics only}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired data according to retention policies';

    /**
     * The DataRetentionService instance.
     *
     * @var DataRetentionService
     */
    protected DataRetentionService $retentionService;

    /**
     * Create a new command instance.
     *
     * @param DataRetentionService $retentionService
     */
    public function __construct(DataRetentionService $retentionService)
    {
        parent::__construct();
        $this->retentionService = $retentionService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->info('🗑️  Data Retention Cleanup');
        $this->line('----------------------------');

        // Show statistics only if requested
        if ($this->option('stats')) {
            return $this->showStatistics();
        }

        $dryRun = $this->option('dry-run');
        $force = $this->option('force');
        $modelType = $this->option('model');

        // Display mode information
        if ($dryRun) {
            $this->warn('Running in DRY RUN mode - no data will be deleted');
        }

        if ($force) {
            $this->warn('Force mode enabled - will delete even if auto_delete is disabled');
        }

        if ($modelType) {
            $this->info("Processing model type: {$modelType}");
        } else {
            $this->info('Processing all model types');
        }

        $this->line('');

        try {
            // Apply retention policies
            $this->info('Applying retention policies...');
            $result = $this->retentionService->applyPolicies($dryRun, $modelType);

            // Check if data retention is disabled
            if ($result['status'] === 'disabled') {
                $this->error('❌ Data retention is disabled in configuration');
                return Command::FAILURE;
            }

            // Display results
            $this->displayResults($result);

            // Show summary
            $this->showSummary($result);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Error during cleanup: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            return Command::FAILURE;
        }
    }

    /**
     * Display the results of applying retention policies.
     *
     * @param array $result
     * @return void
     */
    protected function displayResults(array $result): void
    {
        if (empty($result['details'])) {
            $this->warn('No policies applied or no expired data found');
            return;
        }

        $this->line('');
        $this->info('Policy Results:');
        $this->line('');

        foreach ($result['details'] as $detail) {
            if (isset($detail['status']) && $detail['status'] === 'error') {
                $this->error("❌ {$detail['model_type']}: {$detail['error']}");
                continue;
            }

            $modelType = $detail['model_type'];
            $expired = $detail['expired_count'] ?? 0;
            $deletable = $detail['deletable_count'] ?? 0;
            $deleted = $detail['deleted_count'] ?? 0;
            $anonymized = $detail['anonymized_count'] ?? 0;

            $this->line("📋 <comment>{$modelType}</comment>");
            $this->line("   Retention Period: {$detail['retention_period_days']} days");
            $this->line("   Delete After: {$detail['delete_after_days']} days");
            $this->line("   Expired Records: {$expired}");
            $this->line("   Deletable Records: {$deletable}");

            if ($deleted > 0) {
                $this->line("   <fg=red>Deleted: {$deleted}</>");
            }

            if ($anonymized > 0) {
                $this->line("   <fg=yellow>Anonymized: {$anonymized}</>");
            }

            $this->line('');
        }
    }

    /**
     * Show summary of cleanup operation.
     *
     * @param array $result
     * @return void
     */
    protected function showSummary(array $result): void
    {
        $this->line('----------------------------');
        $this->info('Summary:');
        $this->line("Policies Applied: {$result['policies_applied']}");
        $this->line("Records Expired: {$result['records_expired']}");

        if ($result['dry_run']) {
            $this->warn("Would Delete: {$result['records_deleted']}");
            $this->warn("Would Anonymize: {$result['records_anonymized']}");
        } else {
            if ($result['records_deleted'] > 0) {
                $this->line("<fg=red>Records Deleted: {$result['records_deleted']}</>");
            }

            if ($result['records_anonymized'] > 0) {
                $this->line("<fg=yellow>Records Anonymized: {$result['records_anonymized']}</>");
            }

            if ($result['records_deleted'] === 0 && $result['records_anonymized'] === 0) {
                $this->info('No records were deleted or anonymized');
            }
        }

        $this->line('----------------------------');

        if ($result['dry_run']) {
            $this->info('✅ Dry run completed successfully');
            $this->line('Run without --dry-run to perform actual cleanup');
        } else {
            $this->info('✅ Cleanup completed successfully');
        }
    }

    /**
     * Show retention statistics.
     *
     * @return int
     */
    protected function showStatistics(): int
    {
        try {
            $modelType = $this->option('model');
            $stats = $this->retentionService->getRetentionStatistics($modelType);

            if ($stats['status'] === 'disabled') {
                $this->error('❌ Data retention is disabled in configuration');
                return Command::FAILURE;
            }

            $this->info('📊 Retention Statistics');
            $this->line('');
            $this->line("Total Policies: {$stats['total_policies']}");
            $this->line("Total Expired Records: {$stats['total_expired_records']}");
            $this->line("Total Deletable Records: {$stats['total_deletable_records']}");
            $this->line('');

            if (empty($stats['policies'])) {
                $this->warn('No active retention policies found');
                return Command::SUCCESS;
            }

            $this->info('Policy Breakdown:');
            $this->line('');

            foreach ($stats['policies'] as $policy) {
                $this->line("📋 <comment>{$policy['model_type']}</comment>");
                $this->line("   Policy ID: {$policy['policy_id']}");
                $this->line("   Retention Period: {$policy['retention_period_days']} days");
                $this->line("   Delete After: {$policy['delete_after_days']} days");
                $this->line("   Total Records: {$policy['total_records']}");
                $this->line("   Expired: {$policy['total_expired']}");
                $this->line("   Deletable: {$policy['total_deletable']}");
                $this->line('');
            }

            $this->info('✅ Statistics retrieved successfully');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Error retrieving statistics: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
