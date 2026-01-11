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
        Schema::create('territory_rules', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('territory_id')->unsigned();
            $table->foreign('territory_id')->references('id')->on('territories')->onDelete('cascade');
            $table->enum('rule_type', ['geographic', 'industry', 'account_size', 'custom'])->default('custom');
            $table->string('field_name');
            $table->string('operator');
            $table->json('value');
            $table->integer('priority')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('territory_id');
            $table->index('rule_type');
            $table->index('is_active');
            $table->index('priority');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('territory_rules');
    }
};
