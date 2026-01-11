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
        Schema::create('deal_scores', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('lead_id')->unsigned();
            $table->foreign('lead_id')->references('id')->on('leads')->onDelete('cascade');

            $table->decimal('score', 5, 2)->comment('Overall deal score from 0 to 100');
            $table->decimal('win_probability', 5, 2)->comment('Predicted win probability from 0 to 100');
            $table->decimal('velocity_score', 5, 2)->comment('Deal velocity score from 0 to 100');
            $table->decimal('engagement_score', 5, 2)->comment('Customer engagement score from 0 to 100');
            $table->decimal('value_score', 5, 2)->comment('Deal value score from 0 to 100');
            $table->decimal('historical_pattern_score', 5, 2)->comment('Historical pattern match score from 0 to 100');

            $table->json('factors')->nullable()->comment('Contributing factors and their weights');

            $table->datetime('generated_at')->comment('When the score was calculated');

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
        Schema::dropIfExists('deal_scores');
    }
};
