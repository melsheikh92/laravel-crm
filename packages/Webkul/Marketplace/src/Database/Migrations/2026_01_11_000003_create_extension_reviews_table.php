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
        Schema::create('extension_reviews', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title')->nullable();
            $table->text('review_text')->nullable();
            $table->unsignedTinyInteger('rating');
            $table->unsignedInteger('helpful_count')->default(0);
            $table->enum('status', ['pending', 'approved', 'rejected', 'flagged'])->default('pending');
            $table->boolean('is_verified_purchase')->default(false);

            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->integer('extension_id')->unsigned();
            $table->foreign('extension_id')->references('id')->on('extensions')->onDelete('cascade');

            $table->timestamps();

            // Add indexes for faster queries
            $table->index(['extension_id', 'status']);
            $table->index('user_id');
            $table->index('rating');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('extension_reviews');
    }
};
