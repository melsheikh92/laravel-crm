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
        if (!Schema::hasTable('data_retention_policies')) {
            Schema::create('data_retention_policies', function (Blueprint $table) {
                $table->id();
                $table->string('model_type');
                $table->integer('retention_period_days');
                $table->integer('delete_after_days');
                $table->json('conditions')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index('model_type');
                $table->index('is_active');
                $table->index(['model_type', 'is_active']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_retention_policies');
    }
};
