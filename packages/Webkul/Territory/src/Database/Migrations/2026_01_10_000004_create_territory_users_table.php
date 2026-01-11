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
        Schema::create('territory_users', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('territory_id')->unsigned();
            $table->foreign('territory_id')->references('id')->on('territories')->onDelete('cascade');
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->enum('role', ['owner', 'member', 'viewer'])->default('member');
            $table->timestamps();

            $table->index('territory_id');
            $table->index('user_id');
            $table->index('role');
            $table->unique(['territory_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('territory_users');
    }
};
