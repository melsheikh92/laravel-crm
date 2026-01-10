<?php

namespace Webkul\Territory\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Webkul\Lead\Models\LeadProxy;
use Webkul\Territory\Repositories\TerritoryAssignmentRepository;
use Webkul\Territory\Repositories\TerritoryRepository;

class TerritoryAnalyticsService
{
    /**
     * Create a new service instance.
     */
    public function __construct(
        protected TerritoryRepository $territoryRepository,
        protected TerritoryAssignmentRepository $assignmentRepository
    ) {}

    /**
     * Get lead count by territory.
     *
     * @param  int  $territoryId
     * @return int
     */
    public function getLeadCount(int $territoryId): int
    {
        return $this->assignmentRepository->getAssignmentCountByType(
            $territoryId,
            LeadProxy::modelClass()
        );
    }

    /**
     * Get all leads assigned to a territory.
     *
     * @param  int  $territoryId
     * @return \Illuminate\Support\Collection
     */
    public function getLeads(int $territoryId): Collection
    {
        $assignments = $this->assignmentRepository->getAssignmentsByTerritory($territoryId);

        return $assignments
            ->where('assignable_type', LeadProxy::modelClass())
            ->map(function ($assignment) {
                return $assignment->assignable;
            })
            ->filter();
    }

    /**
     * Get conversion rate for a territory.
     *
     * @param  int  $territoryId
     * @return float
     */
    public function getConversionRate(int $territoryId): float
    {
        $leads = $this->getLeads($territoryId);

        if ($leads->isEmpty()) {
            return 0.0;
        }

        $totalLeads = $leads->count();
        $wonLeads = $leads->filter(function ($lead) {
            return $lead->stage && $lead->stage->code === 'won';
        })->count();

        return round(($wonLeads / $totalLeads) * 100, 2);
    }

    /**
     * Get revenue by territory.
     *
     * @param  int  $territoryId
     * @return float
     */
    public function getRevenue(int $territoryId): float
    {
        $leads = $this->getLeads($territoryId);

        return $leads
            ->filter(function ($lead) {
                return $lead->stage && $lead->stage->code === 'won';
            })
            ->sum('lead_value') ?? 0.0;
    }

    /**
     * Get won leads count by territory.
     *
     * @param  int  $territoryId
     * @return int
     */
    public function getWonLeadsCount(int $territoryId): int
    {
        $leads = $this->getLeads($territoryId);

        return $leads->filter(function ($lead) {
            return $lead->stage && $lead->stage->code === 'won';
        })->count();
    }

    /**
     * Get lost leads count by territory.
     *
     * @param  int  $territoryId
     * @return int
     */
    public function getLostLeadsCount(int $territoryId): int
    {
        $leads = $this->getLeads($territoryId);

        return $leads->filter(function ($lead) {
            return $lead->stage && $lead->stage->code === 'lost';
        })->count();
    }

    /**
     * Get comprehensive performance metrics for a territory.
     *
     * @param  int  $territoryId
     * @return array
     */
    public function getPerformanceMetrics(int $territoryId): array
    {
        $territory = $this->territoryRepository->find($territoryId);

        if (! $territory) {
            return [];
        }

        $leads = $this->getLeads($territoryId);
        $totalLeads = $leads->count();
        $wonLeads = $leads->filter(fn($lead) => $lead->stage && $lead->stage->code === 'won');
        $lostLeads = $leads->filter(fn($lead) => $lead->stage && $lead->stage->code === 'lost');
        $openLeads = $leads->filter(fn($lead) => $lead->stage && !in_array($lead->stage->code, ['won', 'lost']));

        return [
            'territory_id'     => $territoryId,
            'territory_name'   => $territory->name,
            'total_leads'      => $totalLeads,
            'won_leads'        => $wonLeads->count(),
            'lost_leads'       => $lostLeads->count(),
            'open_leads'       => $openLeads->count(),
            'conversion_rate'  => $this->getConversionRate($territoryId),
            'revenue'          => $wonLeads->sum('lead_value') ?? 0.0,
            'average_deal_size' => $wonLeads->count() > 0 ? round($wonLeads->avg('lead_value') ?? 0, 2) : 0.0,
            'pipeline_value'   => $openLeads->sum('lead_value') ?? 0.0,
        ];
    }

    /**
     * Get performance metrics for all territories.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAllTerritoriesPerformance(): Collection
    {
        $territories = $this->territoryRepository->getActiveTerritories();

        return $territories->map(function ($territory) {
            return $this->getPerformanceMetrics($territory->id);
        });
    }

    /**
     * Get performance metrics for territories filtered by type.
     *
     * @param  string  $type  'geographic' or 'account-based'
     * @return \Illuminate\Support\Collection
     */
    public function getPerformanceByType(string $type): Collection
    {
        $territories = $this->territoryRepository->getByType($type);

        return $territories->map(function ($territory) {
            return $this->getPerformanceMetrics($territory->id);
        });
    }

    /**
     * Get top performing territories by revenue.
     *
     * @param  int  $limit
     * @return \Illuminate\Support\Collection
     */
    public function getTopTerritoriesByRevenue(int $limit = 10): Collection
    {
        return $this->getAllTerritoriesPerformance()
            ->sortByDesc('revenue')
            ->take($limit)
            ->values();
    }

    /**
     * Get top performing territories by conversion rate.
     *
     * @param  int  $limit
     * @return \Illuminate\Support\Collection
     */
    public function getTopTerritoriesByConversionRate(int $limit = 10): Collection
    {
        return $this->getAllTerritoriesPerformance()
            ->filter(fn($metrics) => $metrics['total_leads'] > 0)
            ->sortByDesc('conversion_rate')
            ->take($limit)
            ->values();
    }

    /**
     * Get top performing territories by lead count.
     *
     * @param  int  $limit
     * @return \Illuminate\Support\Collection
     */
    public function getTopTerritoriesByLeadCount(int $limit = 10): Collection
    {
        return $this->getAllTerritoriesPerformance()
            ->sortByDesc('total_leads')
            ->take($limit)
            ->values();
    }

    /**
     * Get performance metrics within a date range.
     *
     * @param  int  $territoryId
     * @param  string  $startDate
     * @param  string  $endDate
     * @return array
     */
    public function getPerformanceByDateRange(int $territoryId, string $startDate, string $endDate): array
    {
        $territory = $this->territoryRepository->find($territoryId);

        if (! $territory) {
            return [];
        }

        $assignments = $this->assignmentRepository->getAssignmentsByDateRange(
            $territoryId,
            $startDate,
            $endDate
        );

        $leads = $assignments
            ->where('assignable_type', LeadProxy::modelClass())
            ->map(fn($assignment) => $assignment->assignable)
            ->filter();

        $wonLeads = $leads->filter(fn($lead) => $lead->stage && $lead->stage->code === 'won');
        $lostLeads = $leads->filter(fn($lead) => $lead->stage && $lead->stage->code === 'lost');
        $openLeads = $leads->filter(fn($lead) => $lead->stage && !in_array($lead->stage->code, ['won', 'lost']));

        $totalLeads = $leads->count();

        return [
            'territory_id'     => $territoryId,
            'territory_name'   => $territory->name,
            'start_date'       => $startDate,
            'end_date'         => $endDate,
            'total_leads'      => $totalLeads,
            'won_leads'        => $wonLeads->count(),
            'lost_leads'       => $lostLeads->count(),
            'open_leads'       => $openLeads->count(),
            'conversion_rate'  => $totalLeads > 0 ? round(($wonLeads->count() / $totalLeads) * 100, 2) : 0.0,
            'revenue'          => $wonLeads->sum('lead_value') ?? 0.0,
            'average_deal_size' => $wonLeads->count() > 0 ? round($wonLeads->avg('lead_value') ?? 0, 2) : 0.0,
            'pipeline_value'   => $openLeads->sum('lead_value') ?? 0.0,
        ];
    }

    /**
     * Compare performance between multiple territories.
     *
     * @param  array  $territoryIds
     * @return \Illuminate\Support\Collection
     */
    public function comparePerformance(array $territoryIds): Collection
    {
        return collect($territoryIds)->map(function ($territoryId) {
            return $this->getPerformanceMetrics($territoryId);
        })->filter();
    }

    /**
     * Get aggregated performance metrics across all territories.
     *
     * @return array
     */
    public function getAggregatedMetrics(): array
    {
        $allMetrics = $this->getAllTerritoriesPerformance();

        return [
            'total_territories' => $allMetrics->count(),
            'total_leads'       => $allMetrics->sum('total_leads'),
            'total_won_leads'   => $allMetrics->sum('won_leads'),
            'total_lost_leads'  => $allMetrics->sum('lost_leads'),
            'total_open_leads'  => $allMetrics->sum('open_leads'),
            'overall_conversion_rate' => $allMetrics->sum('total_leads') > 0
                ? round(($allMetrics->sum('won_leads') / $allMetrics->sum('total_leads')) * 100, 2)
                : 0.0,
            'total_revenue'     => $allMetrics->sum('revenue'),
            'total_pipeline_value' => $allMetrics->sum('pipeline_value'),
            'average_territory_revenue' => $allMetrics->count() > 0
                ? round($allMetrics->avg('revenue'), 2)
                : 0.0,
        ];
    }

    /**
     * Get performance trend data for a territory.
     *
     * @param  int  $territoryId
     * @param  int  $months
     * @return \Illuminate\Support\Collection
     */
    public function getPerformanceTrend(int $territoryId, int $months = 12): Collection
    {
        $trends = collect([]);

        for ($i = $months - 1; $i >= 0; $i--) {
            $startDate = now()->subMonths($i)->startOfMonth()->format('Y-m-d');
            $endDate = now()->subMonths($i)->endOfMonth()->format('Y-m-d');

            $metrics = $this->getPerformanceByDateRange($territoryId, $startDate, $endDate);
            $metrics['month'] = now()->subMonths($i)->format('F Y');

            $trends->push($metrics);
        }

        return $trends;
    }

    /**
     * Get territory rankings by performance.
     *
     * @param  string  $metric  'revenue', 'conversion_rate', or 'lead_count'
     * @return \Illuminate\Support\Collection
     */
    public function getTerritoryRankings(string $metric = 'revenue'): Collection
    {
        $allMetrics = $this->getAllTerritoriesPerformance();

        $sortColumn = match($metric) {
            'conversion_rate' => 'conversion_rate',
            'lead_count' => 'total_leads',
            default => 'revenue',
        };

        return $allMetrics
            ->sortByDesc($sortColumn)
            ->values()
            ->map(function ($metrics, $index) use ($sortColumn) {
                $metrics['rank'] = $index + 1;
                return $metrics;
            });
    }
}
