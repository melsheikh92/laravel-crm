<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('extension_review_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('review_id');
            $table->foreign('review_id')->references('id')->on('extension_reviews')->onDelete('cascade');
            $table->unsignedInteger('reported_by');
            $table->foreign('reported_by')->references('id')->on('users')->onDelete('cascade');
            $table->text('reason');
            $table->enum('status', ['pending', 'reviewed', 'resolved', 'dismissed'])->default('pending');
            $table->unsignedInteger('reviewed_by')->nullable();
            $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('set null');
            $table->text('admin_notes')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            // Ensure a user can only report a review once
            $table->unique(['review_id', 'reported_by']);

            // Indexes for performance
            $table->index('review_id');
            $table->index('reported_by');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('extension_review_reports');
    }
};
