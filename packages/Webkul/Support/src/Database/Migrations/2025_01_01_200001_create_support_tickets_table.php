<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->increments('id');
            $table->string('ticket_number')->unique();
            $table->string('subject');
            $table->text('description');
            $table->enum('status', ['open', 'assigned', 'in_progress', 'waiting_customer', 'resolved', 'closed'])->default('open');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('type', ['bug', 'feature_request', 'question', 'other'])->default('question');
            $table->unsignedInteger('customer_id'); // person_id
            $table->unsignedInteger('assigned_to')->nullable();
            $table->unsignedInteger('sla_id')->nullable();
            $table->timestamp('sla_due_at')->nullable();
            $table->boolean('sla_breached')->default(false);
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->unsignedInteger('closed_by')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('persons')->onDelete('cascade');
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
            $table->foreign('sla_id')->references('id')->on('support_slas')->onDelete('set null');
            $table->foreign('closed_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->index('status');
            $table->index('priority');
            $table->index('ticket_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_tickets');
    }
};

