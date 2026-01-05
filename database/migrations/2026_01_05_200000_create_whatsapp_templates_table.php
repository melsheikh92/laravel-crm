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
        Schema::create('whatsapp_templates', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('language')->default('en_US');
            $table->string('status')->default('PENDING');
            $table->string('category')->default('MARKETING');
            $table->text('body');
            $table->text('header')->nullable();
            $table->text('footer')->nullable();
            $table->json('buttons')->nullable();
            $table->string('meta_template_id')->nullable();

            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
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
        Schema::dropIfExists('whatsapp_templates');
    }
};
