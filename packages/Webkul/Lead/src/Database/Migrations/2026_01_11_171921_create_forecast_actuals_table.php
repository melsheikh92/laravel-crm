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
        Schema::create('forecast_actuals', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('forecast_id')->unsigned();
            $table->foreign('forecast_id')->references('id')->on('sales_forecasts')->onDelete('cascade');

            $table->decimal('actual_value', 12, 4)->comment('Actual revenue achieved in the forecast period');
            $table->decimal('variance', 12, 4)->comment('Difference between actual and forecasted value');
            $table->decimal('variance_percentage', 5, 2)->comment('Variance as a percentage');

            $table->datetime('closed_at')->comment('When the forecast period was closed and actuals calculated');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('forecast_actuals');
    }
};
