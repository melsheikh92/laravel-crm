<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('extension_transactions')) {
            return;
        }

        Schema::create('extension_transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('transaction_id')->unique()->nullable();
            $table->decimal('amount', 12, 2);
            $table->decimal('platform_fee', 12, 2)->default(0);
            $table->decimal('seller_revenue', 12, 2);
            $table->string('payment_method')->nullable();
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();

            $table->integer('extension_id')->unsigned();
            $table->foreign('extension_id')->references('id')->on('extensions')->onDelete('cascade');

            $table->integer('buyer_id')->unsigned();
            $table->foreign('buyer_id')->references('id')->on('users')->onDelete('cascade');

            $table->integer('seller_id')->unsigned();
            $table->foreign('seller_id')->references('id')->on('users')->onDelete('cascade');

            $table->timestamps();

            // Add indexes for faster queries
            $table->index(['extension_id', 'status']);
            $table->index(['buyer_id', 'status']);
            $table->index(['seller_id', 'status']);
            $table->index('status');
            $table->index('payment_method');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('extension_transactions');
    }
};
