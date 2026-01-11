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
        Schema::create('extension_submissions', function (Blueprint $table) {
            $table->increments('id');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('review_notes')->nullable();
            $table->json('security_scan_results')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();

            $table->integer('extension_id')->unsigned();
            $table->foreign('extension_id')->references('id')->on('extensions')->onDelete('cascade');

            $table->integer('version_id')->unsigned();
            $table->foreign('version_id')->references('id')->on('extension_versions')->onDelete('cascade');

            $table->integer('submitted_by')->unsigned();
            $table->foreign('submitted_by')->references('id')->on('users')->onDelete('cascade');

            $table->integer('reviewer_id')->unsigned()->nullable();
            $table->foreign('reviewer_id')->references('id')->on('users')->onDelete('set null');

            $table->timestamps();

            // Add indexes for faster queries
            $table->index(['extension_id', 'status']);
            $table->index(['version_id', 'status']);
            $table->index(['submitted_by', 'status']);
            $table->index(['reviewer_id', 'status']);
            $table->index('status');
            $table->index('submitted_at');
            $table->index('reviewed_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('extension_submissions');
    }
};
