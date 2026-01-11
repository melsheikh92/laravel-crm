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
        if (!Schema::hasTable('data_deletion_requests')) {
            Schema::create('data_deletion_requests', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('user_id')->nullable();
                $table->string('email');
                $table->timestamp('requested_at');
                $table->timestamp('processed_at')->nullable();
                $table->string('status');
                $table->text('notes')->nullable();
                $table->unsignedInteger('processed_by')->nullable();
                $table->timestamps();

                $table->index('user_id');
                $table->index('email');
                $table->index('status');
                $table->index('requested_at');
                $table->index('processed_at');
                $table->index('processed_by');

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('processed_by')->references('id')->on('users')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_deletion_requests');
    }
};
