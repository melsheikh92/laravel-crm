<?php

namespace Webkul\Lead\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Webkul\Lead\Repositories\HistoricalConversionRepository;
use Webkul\Lead\Repositories\LeadRepository;
use Webkul\Lead\Repositories\PipelineRepository;
use Webkul\Lead\Repositories\StageRepository;
use Webkul\User\Repositories\UserRepository;

class SeedHistoricalConversions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leads:seed-historical-conversions
                            {--days=90 : Number of days to analyze (default: 90)}
                            {--pipeline= : Analyze specific pipeline ID only}
                            {--user= : Analyze specific user ID only}
                            {--dry-run : Preview results without saving to database}
                            {--force : Force re-seed even if data exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Analyze existing leads and populate historical conversion data for forecasting';

    /**
     * Minimum sample size for statistical significance.
     */
    const MIN_SAMPLE_SIZE = 5;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        protected LeadRepository $leadRepository,
        protected HistoricalConversionRepository $historicalConversionRepository,
        protected PipelineRepository $pipelineRepository,
        protected StageRepository $stageRepository,
        protected UserRepository $userRepository
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->info('📊 Historical Conversions Seeder');
        $this->line('----------------------------');

        $days = (int) $this->option('days');
        $pipelineId = $this->option('pipeline');
        $userId = $this->option('user');
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        if ($dryRun) {
            $this->warn('Running in DRY RUN mode - no data will be saved');
        }

        if ($force) {
            $this->warn('Force mode enabled - existing data will be overwritten');
        }

        $this->line('');
        $this->info("Analysis period: {$days} days");

        if ($pipelineId) {
            $this->info("Pipeline filter: ID {$pipelineId}");
        }

        if ($userId) {
            $this->info("User filter: ID {$userId}");
        }

        $this->line('');

        try {
            $startDate = now()->subDays($days);
            $endDate = now();

            $this->info('Fetching leads for analysis...');

            // Get all leads within the period
            $query = $this->leadRepository->model
                ->with(['stage', 'pipeline', 'user'])
                ->where('created_at', '>=', $startDate)
                ->where('created_at', '<=', $endDate);

            if ($pipelineId) {
                $query->where('lead_pipeline_id', $pipelineId);
            }

            if ($userId) {
                $query->where('user_id', $userId);
            }

            $leads = $query->get();

            if ($leads->isEmpty()) {
                $this->warn('No leads found in the specified period');
                return Command::SUCCESS;
            }

            $this->info("Found {$leads->count()} leads to analyze");
            $this->line('');

            // Group leads by stage, pipeline, and user combinations
            $this->info('Calculating conversion rates...');
            $conversions = $this->calculateConversions($leads, $startDate, $endDate);

            if (empty($conversions)) {
                $this->warn('No conversion data calculated');
                return Command::SUCCESS;
            }

            $this->line('');
            $this->info("Calculated {$conversions->count()} conversion records");
            $this->line('');

            // Display preview
            $this->displayConversionPreview($conversions);

            // Save to database
            if (! $dryRun) {
                $this->info('Saving historical conversion data...');
                $saved = $this->saveConversions($conversions, $force);
                $this->line('');
                $this->info("✅ Successfully saved {$saved} conversion records");
            } else {
                $this->line('');
                $this->info('✅ Dry run completed - no data was saved');
                $this->line('Run without --dry-run to save data to database');
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Error during seeding: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            return Command::FAILURE;
        }
    }

    /**
     * Calculate conversions from leads data.
     *
     * @param  \Illuminate\Support\Collection  $leads
     * @param  Carbon  $startDate
     * @param  Carbon  $endDate
     * @return \Illuminate\Support\Collection
     */
    protected function calculateConversions($leads, Carbon $startDate, Carbon $endDate)
    {
        $conversions = collect();

        // Group by stage, pipeline, and user
        $grouped = $leads->groupBy(function ($lead) {
            return sprintf(
                '%d-%d-%d',
                $lead->lead_pipeline_stage_id,
                $lead->lead_pipeline_id,
                $lead->user_id ?? 0
            );
        });

        foreach ($grouped as $key => $groupLeads) {
            $firstLead = $groupLeads->first();

            $stageId = $firstLead->lead_pipeline_stage_id;
            $pipelineId = $firstLead->lead_pipeline_id;
            $userId = $firstLead->user_id;

            // Skip if sample size is too small
            if ($groupLeads->count() < self::MIN_SAMPLE_SIZE) {
                continue;
            }

            // Calculate conversion rate (leads that moved to "won" stage)
            $wonLeads = $groupLeads->filter(function ($lead) {
                return $lead->stage && $lead->stage->code === 'won';
            });

            $conversionRate = ($groupLeads->count() > 0)
                ? round(($wonLeads->count() / $groupLeads->count()) * 100, 2)
                : 0.0;

            // Calculate average time in stage
            $averageTimeInStage = $this->calculateAverageTimeInStage($groupLeads);

            $conversions->push([
                'stage_id' => $stageId,
                'pipeline_id' => $pipelineId,
                'user_id' => $userId,
                'conversion_rate' => $conversionRate,
                'average_time_in_stage' => $averageTimeInStage,
                'sample_size' => $groupLeads->count(),
                'period_start' => $startDate->format('Y-m-d'),
                'period_end' => $endDate->format('Y-m-d'),
                'stage_name' => $firstLead->stage->name ?? 'Unknown',
                'pipeline_name' => $firstLead->pipeline->name ?? 'Unknown',
                'user_name' => $firstLead->user->name ?? 'System',
            ]);
        }

        return $conversions->sortByDesc('sample_size');
    }

    /**
     * Calculate average time in stage for a group of leads.
     *
     * @param  \Illuminate\Support\Collection  $leads
     * @return float
     */
    protected function calculateAverageTimeInStage($leads): float
    {
        $times = $leads->map(function ($lead) {
            if (! $lead->updated_at || ! $lead->created_at) {
                return 0;
            }

            // If lead is closed, calculate time from created to closed
            if ($lead->closed_at) {
                return $lead->created_at->diffInDays($lead->closed_at);
            }

            // Otherwise, calculate time from last update to now
            return $lead->updated_at->diffInDays(now());
        })->filter(fn ($time) => $time > 0);

        if ($times->isEmpty()) {
            return 0.0;
        }

        return round($times->avg(), 2);
    }

    /**
     * Display a preview of calculated conversions.
     *
     * @param  \Illuminate\Support\Collection  $conversions
     * @return void
     */
    protected function displayConversionPreview($conversions): void
    {
        $this->info('Preview (top 10 records):');
        $this->line('');

        $preview = $conversions->take(10);

        foreach ($preview as $conversion) {
            $stageName = $conversion['stage_name'];
            $pipelineName = $conversion['pipeline_name'];
            $userName = $conversion['user_name'];
            $rate = $conversion['conversion_rate'];
            $avgTime = $conversion['average_time_in_stage'];
            $sample = $conversion['sample_size'];

            $this->line("📈 <comment>{$pipelineName}</comment> / <comment>{$stageName}</comment> / <comment>{$userName}</comment>");
            $this->line("   Conversion Rate: {$rate}%");
            $this->line("   Avg Time in Stage: {$avgTime} days");
            $this->line("   Sample Size: {$sample} leads");
            $this->line('');
        }

        if ($conversions->count() > 10) {
            $remaining = $conversions->count() - 10;
            $this->line("... and {$remaining} more records");
            $this->line('');
        }
    }

    /**
     * Save conversions to database.
     *
     * @param  \Illuminate\Support\Collection  $conversions
     * @param  bool  $force
     * @return int
     */
    protected function saveConversions($conversions, bool $force): int
    {
        $saved = 0;

        foreach ($conversions as $conversion) {
            // Remove display-only fields
            $data = collect($conversion)->except(['stage_name', 'pipeline_name', 'user_name'])->toArray();

            // Check if record exists
            $exists = $this->historicalConversionRepository->model
                ->where('stage_id', $data['stage_id'])
                ->where('pipeline_id', $data['pipeline_id'])
                ->where('user_id', $data['user_id'])
                ->where('period_start', $data['period_start'])
                ->where('period_end', $data['period_end'])
                ->exists();

            if ($exists && ! $force) {
                continue;
            }

            // Update or create
            $this->historicalConversionRepository->model->updateOrCreate(
                [
                    'stage_id' => $data['stage_id'],
                    'pipeline_id' => $data['pipeline_id'],
                    'user_id' => $data['user_id'],
                    'period_start' => $data['period_start'],
                    'period_end' => $data['period_end'],
                ],
                [
                    'conversion_rate' => $data['conversion_rate'],
                    'average_time_in_stage' => $data['average_time_in_stage'],
                    'sample_size' => $data['sample_size'],
                ]
            );

            $saved++;
        }

        return $saved;
    }
}
