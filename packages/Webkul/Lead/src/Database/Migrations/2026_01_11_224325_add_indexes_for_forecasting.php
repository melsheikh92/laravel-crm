<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add indexes to sales_forecasts table
        Schema::table('sales_forecasts', function (Blueprint $table) {
            $table->index('team_id', 'sales_forecasts_team_id_index');
            $table->index(['period_start', 'period_end'], 'sales_forecasts_period_index');
            $table->index(['user_id', 'period_start', 'period_end'], 'sales_forecasts_user_period_index');
        });

        // Add indexes to deal_scores table
        Schema::table('deal_scores', function (Blueprint $table) {
            $table->index('generated_at', 'deal_scores_generated_at_index');
            $table->index(['lead_id', 'generated_at'], 'deal_scores_lead_generated_index');
        });

        // Add indexes to forecast_actuals table
        Schema::table('forecast_actuals', function (Blueprint $table) {
            $table->index('closed_at', 'forecast_actuals_closed_at_index');
        });

        // Add indexes to historical_conversions table
        Schema::table('historical_conversions', function (Blueprint $table) {
            $table->index(['period_start', 'period_end'], 'historical_conversions_period_index');
            $table->index(['stage_id', 'pipeline_id', 'user_id'], 'historical_conversions_composite_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drop indexes from sales_forecasts table
        Schema::table('sales_forecasts', function (Blueprint $table) {
            $table->dropIndex('sales_forecasts_team_id_index');
            $table->dropIndex('sales_forecasts_period_index');
            $table->dropIndex('sales_forecasts_user_period_index');
        });

        // Drop indexes from deal_scores table
        Schema::table('deal_scores', function (Blueprint $table) {
            $table->dropIndex('deal_scores_generated_at_index');
            $table->dropIndex('deal_scores_lead_generated_index');
        });

        // Drop indexes from forecast_actuals table
        Schema::table('forecast_actuals', function (Blueprint $table) {
            $table->dropIndex('forecast_actuals_closed_at_index');
        });

        // Drop indexes from historical_conversions table
        Schema::table('historical_conversions', function (Blueprint $table) {
            $table->dropIndex('historical_conversions_period_index');
            $table->dropIndex('historical_conversions_composite_index');
        });
    }
};
