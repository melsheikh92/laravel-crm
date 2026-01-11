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
        Schema::create('historical_conversions', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('stage_id')->unsigned();
            $table->foreign('stage_id')->references('id')->on('lead_stages')->onDelete('cascade');

            $table->integer('pipeline_id')->unsigned();
            $table->foreign('pipeline_id')->references('id')->on('lead_pipelines')->onDelete('cascade');

            $table->integer('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->decimal('conversion_rate', 5, 2)->comment('Conversion rate as percentage from 0 to 100');
            $table->decimal('average_time_in_stage', 8, 2)->comment('Average time in stage in days');
            $table->integer('sample_size')->unsigned()->comment('Number of leads in the sample');

            $table->date('period_start')->comment('Start date of the analysis period');
            $table->date('period_end')->comment('End date of the analysis period');

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
        Schema::dropIfExists('historical_conversions');
    }
};
