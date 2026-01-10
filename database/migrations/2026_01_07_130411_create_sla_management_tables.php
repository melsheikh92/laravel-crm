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
        // SLA Policies
        Schema::create('sla_policies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->boolean('business_hours_only')->default(false);
            $table->timestamps();
        });

        // SLA Policy Rules
        Schema::create('sla_policy_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sla_policy_id')->constrained('sla_policies')->onDelete('cascade');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent']);
            $table->integer('first_response_time'); // in minutes
            $table->integer('resolution_time'); // in minutes
            $table->timestamps();

            $table->unique(['sla_policy_id', 'priority']);
        });

        // SLA Policy Conditions
        Schema::create('sla_policy_conditions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sla_policy_id')->constrained('sla_policies')->onDelete('cascade');
            $table->enum('condition_type', ['category', 'customer_type', 'priority', 'tag']);
            $table->string('condition_value');
            $table->timestamps();
        });

        // Business Hours
        Schema::create('business_hours', function (Blueprint $table) {
            $table->id();
            $table->integer('day_of_week'); // 0-6 (Sunday to Saturday)
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('day_of_week');
        });

        // SLA Breaches
        Schema::create('sla_breaches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('support_tickets')->onDelete('cascade');
            $table->foreignId('sla_policy_id')->constrained('sla_policies')->onDelete('cascade');
            $table->enum('breach_type', ['first_response', 'resolution']);
            $table->timestamp('due_at');
            $table->timestamp('breached_at');
            $table->integer('breach_duration'); // in minutes
            $table->timestamp('created_at');

            $table->index('ticket_id');
            $table->index('breach_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sla_breaches');
        Schema::dropIfExists('business_hours');
        Schema::dropIfExists('sla_policy_conditions');
        Schema::dropIfExists('sla_policy_rules');
        Schema::dropIfExists('sla_policies');
    }
};
