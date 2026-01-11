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
        Schema::create('sales_forecasts', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->integer('team_id')->unsigned()->nullable();

            $table->enum('period_type', ['week', 'month', 'quarter']);
            $table->date('period_start');
            $table->date('period_end');

            $table->decimal('forecast_value', 12, 4);
            $table->decimal('weighted_forecast', 12, 4);
            $table->decimal('best_case', 12, 4);
            $table->decimal('worst_case', 12, 4);
            $table->decimal('confidence_score', 5, 2)->comment('Confidence score from 0 to 100');

            $table->json('metadata')->nullable();

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
        Schema::dropIfExists('sales_forecasts');
    }
};
