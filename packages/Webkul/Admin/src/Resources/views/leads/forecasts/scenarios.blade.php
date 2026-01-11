<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.layouts.scenario-modeling')
        </x-slot>

        <div class="content full-page">
            {{-- Debug: View is loading --}}
            <v-scenario-modeling
            endpoint="{{ route('admin.forecasts.analytics.scenarios') }}"
            user-id="{{ auth()->user()->id }}"
            :strings="{{ json_encode([
                'title' => __('admin::app.leads.forecasts.scenarios.title'),
                'period_monthly' => __('admin::app.leads.forecasts.scenarios.period-monthly'),
                'period_quarterly' => __('admin::app.leads.forecasts.scenarios.period-quarterly'),
                'period_yearly' => __('admin::app.leads.forecasts.scenarios.period-yearly'),
                'error_title' => __('admin::app.leads.forecasts.scenarios.error-title'),
                'weighted_forecast' => __('admin::app.leads.forecasts.scenarios.weighted-forecast'),
                'best_case' => __('admin::app.leads.forecasts.scenarios.best-case'),
                'worst_case' => __('admin::app.leads.forecasts.scenarios.worst-case'),
                'pipeline_metrics' => __('admin::app.leads.forecasts.scenarios.pipeline-metrics'),
                'total_open_leads' => __('admin::app.leads.forecasts.scenarios.total-open-leads'),
                'avg_deal_value' => __('admin::app.leads.forecasts.scenarios.avg-deal-value'),
                'ai_recommendations' => __('admin::app.leads.forecasts.scenarios.ai-recommendations'),
                'no_recommendations' => __('admin::app.leads.forecasts.scenarios.no-recommendations'),
            ]) }}"
        ></v-scenario-modeling>
        </div>
</x-admin::layouts>