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
        // Ticket Categories
        if (!Schema::hasTable('ticket_categories')) {
            Schema::create('ticket_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->foreignId('parent_id')->nullable()->constrained('ticket_categories')->onDelete('cascade');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // Support Tickets
        if (!Schema::hasTable('support_tickets')) {
            Schema::create('support_tickets', function (Blueprint $table) {
                $table->id();
                $table->string('ticket_number')->unique();
                $table->string('subject');
                $table->text('description');
                $table->enum('status', ['open', 'in_progress', 'waiting_customer', 'waiting_internal', 'resolved', 'closed'])->default('open');
                $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
                $table->foreignId('category_id')->nullable()->constrained('ticket_categories')->onDelete('set null');
                $table->unsignedInteger('assigned_to')->nullable();
                $table->foreignId('customer_id')->nullable()->constrained('persons')->onDelete('cascade');
                $table->foreignId('contact_id')->nullable()->constrained('persons')->onDelete('set null');
                $table->foreignId('lead_id')->nullable()->constrained('leads')->onDelete('set null');
                $table->enum('source', ['email', 'web_form', 'phone', 'chat', 'customer_portal'])->default('web_form');
                $table->foreignId('sla_policy_id')->nullable()->index();
                $table->timestamp('first_response_at')->nullable();
                $table->timestamp('first_response_due_at')->nullable();
                $table->timestamp('resolution_due_at')->nullable();
                $table->timestamp('resolved_at')->nullable();
                $table->timestamp('closed_at')->nullable();
                $table->boolean('sla_breached')->default(false);
                $table->unsignedInteger('created_by');
                $table->timestamps();
                $table->softDeletes();

                $table->index(['status', 'priority']);
                $table->index('ticket_number');
                $table->index('assigned_to');

                $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
                $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            });
        }

        // Ticket Messages
        if (!Schema::hasTable('ticket_messages')) {
            Schema::create('ticket_messages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ticket_id')->constrained('support_tickets')->onDelete('cascade');
                $table->unsignedInteger('user_id')->nullable();
                $table->text('message');
                $table->boolean('is_internal')->default(false);
                $table->boolean('is_from_customer')->default(false);
                $table->timestamps();

                $table->index('ticket_id');

                $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            });
        }

        // Ticket Attachments
        if (!Schema::hasTable('ticket_attachments')) {
            Schema::create('ticket_attachments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ticket_id')->constrained('support_tickets')->onDelete('cascade');
                $table->foreignId('message_id')->nullable()->constrained('ticket_messages')->onDelete('cascade');
                $table->string('file_name');
                $table->string('file_path');
                $table->integer('file_size');
                $table->string('mime_type');
                $table->unsignedInteger('uploaded_by');
                $table->timestamp('created_at');

                $table->index('ticket_id');

                $table->foreign('uploaded_by')->references('id')->on('users')->onDelete('cascade');
            });
        }

        // Ticket Tags (many-to-many)
        if (!Schema::hasTable('ticket_tags')) {
            Schema::create('ticket_tags', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ticket_id')->constrained('support_tickets')->onDelete('cascade');
                $table->unsignedInteger('tag_id');
                $table->timestamps();

                $table->unique(['ticket_id', 'tag_id']);

                $table->foreign('tag_id')->references('id')->on('tags')->onDelete('cascade');
            });
        }

        // Ticket Watchers
        if (!Schema::hasTable('ticket_watchers')) {
            Schema::create('ticket_watchers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ticket_id')->constrained('support_tickets')->onDelete('cascade');
                $table->unsignedInteger('user_id');
                $table->timestamps();

                $table->unique(['ticket_id', 'user_id']);

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_watchers');
        Schema::dropIfExists('ticket_tags');
        Schema::dropIfExists('ticket_attachments');
        Schema::dropIfExists('ticket_messages');
        Schema::dropIfExists('support_tickets');
        Schema::dropIfExists('ticket_categories');
    }
};
