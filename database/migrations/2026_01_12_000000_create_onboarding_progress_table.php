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
        if (!Schema::hasTable('onboarding_progress')) {
            Schema::create('onboarding_progress', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('user_id');
                $table->string('current_step')->nullable();
                $table->json('completed_steps')->nullable();
                $table->json('skipped_steps')->nullable();
                $table->boolean('is_completed')->default(false);
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();

                $table->index('user_id');
                $table->index('is_completed');
                $table->unique('user_id');

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('onboarding_progress');
    }
};
