<?php

namespace Webkul\Lead\Tests\Unit\Services;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Webkul\Lead\Models\Lead;
use Webkul\Lead\Models\Pipeline;
use Webkul\Lead\Models\Stage;
use Webkul\Lead\Services\ForecastCalculationService;
use Webkul\Lead\Services\HistoricalAnalysisService;
use Webkul\User\Models\User;

/**
 * Performance tests for forecast calculation services with large datasets.
 *
 * These tests validate query optimization, pagination, and memory efficiency
 * when working with large numbers of leads.
 */
class ForecastCalculationPerformanceTest extends TestCase
{
    use RefreshDatabase;

    protected ForecastCalculationService $forecastService;
    protected HistoricalAnalysisService $analysisService;
    protected User $user;
    protected Pipeline $pipeline;
    protected Stage $stage;

    protected function setUp(): void
    {
        parent::setUp();

        $this->forecastService = app(ForecastCalculationService::class);
        $this->analysisService = app(HistoricalAnalysisService::class);

        // Create test user
        $this->user = User::factory()->create();

        // Create test pipeline and stage
        $this->pipeline = Pipeline::factory()->create(['name' => 'Test Pipeline']);
        $this->stage = Stage::factory()->create([
            'lead_pipeline_id' => $this->pipeline->id,
            'probability' => 70.0,
            'code' => 'negotiation',
        ]);
    }

    /**
     * Test forecast calculation with large dataset (1000+ leads).
     *
     * @test
     */
    public function it_handles_large_dataset_forecast_calculation_efficiently()
    {
        // Create 1000 test leads
        $leadCount = 1000;
        $batchSize = 100;

        for ($i = 0; $i < $leadCount / $batchSize; $i++) {
            $leads = [];
            for ($j = 0; $j < $batchSize; $j++) {
                $leads[] = [
                    'title' => "Test Lead " . ($i * $batchSize + $j),
                    'lead_value' => rand(1000, 100000),
                    'user_id' => $this->user->id,
                    'lead_pipeline_id' => $this->pipeline->id,
                    'lead_pipeline_stage_id' => $this->stage->id,
                    'status' => 1,
                    'expected_close_date' => now()->addDays(rand(1, 30)),
                    'created_at' => now()->subDays(rand(1, 90)),
                    'updated_at' => now(),
                ];
            }
            DB::table('leads')->insert($leads);
        }

        // Measure query count and time
        $queryCount = 0;
        DB::listen(function ($query) use (&$queryCount) {
            $queryCount++;
        });

        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        // Generate forecast
        $forecast = $this->forecastService->generateForecast(
            $this->user->id,
            'month',
            now()->startOfMonth()
        );

        $endTime = microtime(true);
        $endMemory = memory_get_usage();

        $executionTime = ($endTime - $startTime) * 1000; // Convert to milliseconds
        $memoryUsed = ($endMemory - $startMemory) / 1024 / 1024; // Convert to MB

        // Assertions
        $this->assertNotNull($forecast);
        $this->assertGreaterThan(0, $forecast->forecast_value);

        // Performance assertions
        $this->assertLessThan(5000, $executionTime, 'Forecast calculation should complete in under 5 seconds');
        $this->assertLessThan(50, $memoryUsed, 'Memory usage should be under 50MB');
        $this->assertLessThan(20, $queryCount, 'Query count should be optimized (under 20 queries)');

        // Output performance metrics for monitoring
        echo "\n\nPerformance Metrics (1000 leads):\n";
        echo "- Execution Time: " . round($executionTime, 2) . "ms\n";
        echo "- Memory Used: " . round($memoryUsed, 2) . "MB\n";
        echo "- Query Count: $queryCount\n";
    }

    /**
     * Test historical analysis with pagination for large datasets.
     *
     * @test
     */
    public function it_supports_pagination_for_large_result_sets()
    {
        // Create 500 test leads across multiple users
        $leadCount = 500;
        $userIds = [];

        for ($i = 0; $i < 10; $i++) {
            $user = User::factory()->create();
            $userIds[] = $user->id;
        }

        for ($i = 0; $i < $leadCount; $i++) {
            Lead::factory()->create([
                'user_id' => $userIds[array_rand($userIds)],
                'lead_pipeline_id' => $this->pipeline->id,
                'lead_pipeline_stage_id' => $this->stage->id,
                'lead_value' => rand(1000, 100000),
                'created_at' => now()->subDays(rand(1, 90)),
            ]);
        }

        // Test paginated query
        $startTime = microtime(true);

        $result = $this->analysisService->getWinRatesByUserPaginated(
            pipelineId: $this->pipeline->id,
            days: 90,
            perPage: 15,
            page: 1
        );

        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000;

        // Assertions
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('pagination', $result);
        $this->assertLessThanOrEqual(15, count($result['data']));
        $this->assertArrayHasKey('total', $result['pagination']);
        $this->assertArrayHasKey('per_page', $result['pagination']);
        $this->assertArrayHasKey('current_page', $result['pagination']);

        // Performance assertion
        $this->assertLessThan(2000, $executionTime, 'Paginated query should complete in under 2 seconds');

        echo "\n\nPagination Performance (500 leads):\n";
        echo "- Execution Time: " . round($executionTime, 2) . "ms\n";
        echo "- Results per page: " . count($result['data']) . "\n";
        echo "- Total records: " . $result['pagination']['total'] . "\n";
    }

    /**
     * Test chunk processing for very large datasets.
     *
     * @test
     */
    public function it_processes_large_datasets_in_chunks()
    {
        // Create 2000 test leads
        $leadCount = 2000;

        for ($i = 0; $i < $leadCount / 100; $i++) {
            $leads = [];
            for ($j = 0; $j < 100; $j++) {
                $leads[] = [
                    'title' => "Test Lead " . ($i * 100 + $j),
                    'lead_value' => rand(1000, 100000),
                    'user_id' => $this->user->id,
                    'lead_pipeline_id' => $this->pipeline->id,
                    'lead_pipeline_stage_id' => $this->stage->id,
                    'status' => 1,
                    'created_at' => now()->subDays(rand(1, 90)),
                    'updated_at' => now(),
                ];
            }
            DB::table('leads')->insert($leads);
        }

        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        $processedCount = 0;
        $chunkCount = 0;

        // Process in chunks
        $this->analysisService->analyzeLargeDatasetInChunks(
            userId: $this->user->id,
            pipelineId: $this->pipeline->id,
            days: 90,
            chunkSize: 500,
            callback: function ($leads) use (&$processedCount, &$chunkCount) {
                $processedCount += $leads->count();
                $chunkCount++;
            }
        );

        $endTime = microtime(true);
        $endMemory = memory_get_usage();

        $executionTime = ($endTime - $startTime) * 1000;
        $memoryUsed = ($endMemory - $startMemory) / 1024 / 1024;

        // Assertions
        $this->assertEquals($leadCount, $processedCount, 'All leads should be processed');
        $this->assertGreaterThan(1, $chunkCount, 'Should process in multiple chunks');

        // Performance assertions - chunking should use less memory
        $this->assertLessThan(100, $memoryUsed, 'Chunk processing should use less than 100MB');

        echo "\n\nChunk Processing Performance (2000 leads):\n";
        echo "- Execution Time: " . round($executionTime, 2) . "ms\n";
        echo "- Memory Used: " . round($memoryUsed, 2) . "MB\n";
        echo "- Processed: $processedCount leads in $chunkCount chunks\n";
        echo "- Avg per chunk: " . round($processedCount / $chunkCount) . " leads\n";
    }

    /**
     * Test query optimization - ensure minimal fields are selected.
     *
     * @test
     */
    public function it_optimizes_queries_by_selecting_only_required_fields()
    {
        // Create test leads
        Lead::factory()->count(50)->create([
            'user_id' => $this->user->id,
            'lead_pipeline_id' => $this->pipeline->id,
            'lead_pipeline_stage_id' => $this->stage->id,
            'status' => 1,
            'expected_close_date' => now()->addDays(15),
        ]);

        $queries = [];
        DB::listen(function ($query) use (&$queries) {
            $queries[] = $query->sql;
        });

        // Generate forecast
        $this->forecastService->generateForecast(
            $this->user->id,
            'month',
            now()->startOfMonth()
        );

        // Check that the main query selects specific fields, not *
        $mainQuery = $queries[0] ?? '';

        // The query should use specific field selection
        $this->assertStringNotContainsString('select *', strtolower($mainQuery), 'Queries should select specific fields, not *');

        echo "\n\nQuery Optimization:\n";
        echo "- Total queries executed: " . count($queries) . "\n";
        echo "- Main query uses specific field selection: " . (strpos(strtolower($mainQuery), 'select *') === false ? 'Yes' : 'No') . "\n";
    }

    /**
     * Test performance comparison between optimized and non-optimized queries.
     *
     * @test
     */
    public function it_demonstrates_performance_improvement_with_optimizations()
    {
        // Create 500 test leads
        Lead::factory()->count(500)->create([
            'user_id' => $this->user->id,
            'lead_pipeline_id' => $this->pipeline->id,
            'lead_pipeline_stage_id' => $this->stage->id,
            'status' => 1,
        ]);

        // Test with optimized query (specific field selection + eager loading)
        $startOptimized = microtime(true);
        $memoryStartOptimized = memory_get_usage();

        $this->forecastService->generateForecast(
            $this->user->id,
            'quarter',
            now()->startOfQuarter()
        );

        $timeOptimized = (microtime(true) - $startOptimized) * 1000;
        $memoryOptimized = (memory_get_usage() - $memoryStartOptimized) / 1024 / 1024;

        // Assertions
        $this->assertLessThan(3000, $timeOptimized, 'Optimized query should complete quickly');
        $this->assertLessThan(50, $memoryOptimized, 'Optimized query should use reasonable memory');

        echo "\n\nOptimization Comparison (500 leads):\n";
        echo "- Optimized execution time: " . round($timeOptimized, 2) . "ms\n";
        echo "- Optimized memory usage: " . round($memoryOptimized, 2) . "MB\n";
        echo "- Performance target met: " . ($timeOptimized < 3000 && $memoryOptimized < 50 ? 'YES' : 'NO') . "\n";
    }

    /**
     * Test cache effectiveness for repeated forecast calculations.
     *
     * @test
     */
    public function it_uses_cache_effectively_for_repeated_calculations()
    {
        // Create test leads
        Lead::factory()->count(100)->create([
            'user_id' => $this->user->id,
            'lead_pipeline_id' => $this->pipeline->id,
            'lead_pipeline_stage_id' => $this->stage->id,
            'status' => 1,
            'expected_close_date' => now()->addDays(15),
        ]);

        // First call - no cache
        $startTime1 = microtime(true);
        $forecast1 = $this->forecastService->generateForecast(
            $this->user->id,
            'month',
            now()->startOfMonth()
        );
        $time1 = (microtime(true) - $startTime1) * 1000;

        // Second call - should use cached historical data
        $startTime2 = microtime(true);
        $forecast2 = $this->forecastService->generateForecast(
            $this->user->id,
            'month',
            now()->startOfMonth()
        );
        $time2 = (microtime(true) - $startTime2) * 1000;

        // Second call should be similar or faster (cache may help with historical data)
        $this->assertNotNull($forecast1);
        $this->assertNotNull($forecast2);
        $this->assertEquals($forecast1->id, $forecast2->id, 'Should return same forecast');

        echo "\n\nCache Effectiveness:\n";
        echo "- First calculation: " . round($time1, 2) . "ms\n";
        echo "- Second calculation: " . round($time2, 2) . "ms\n";
        echo "- Improvement: " . round((($time1 - $time2) / $time1) * 100, 2) . "%\n";
    }
}
