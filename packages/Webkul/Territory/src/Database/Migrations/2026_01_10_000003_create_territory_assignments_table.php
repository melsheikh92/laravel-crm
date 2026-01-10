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
        Schema::create('territory_assignments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('territory_id')->unsigned();
            $table->foreign('territory_id')->references('id')->on('territories')->onDelete('cascade');
            $table->string('assignable_type');
            $table->unsignedInteger('assignable_id');
            $table->integer('assigned_by')->unsigned()->nullable();
            $table->foreign('assigned_by')->references('id')->on('users')->onDelete('set null');
            $table->enum('assignment_type', ['manual', 'automatic'])->default('automatic');
            $table->timestamp('assigned_at')->nullable();
            $table->timestamps();

            $table->index('territory_id');
            $table->index(['assignable_type', 'assignable_id']);
            $table->index('assignment_type');
            $table->index('assigned_by');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('territory_assignments');
    }
};
