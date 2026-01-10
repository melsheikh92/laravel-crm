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
        // Knowledge Base Categories
        if (!Schema::hasTable('kb_categories')) {
            Schema::create('kb_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->foreignId('parent_id')->nullable()->constrained('kb_categories')->onDelete('cascade');
                $table->string('icon')->nullable();
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->enum('visibility', ['public', 'internal', 'customer_portal'])->default('public');
                $table->timestamps();

                $table->index('slug');
                $table->index('visibility');
            });
        }

        // Knowledge Base Articles
        if (!Schema::hasTable('kb_articles')) {
            Schema::create('kb_articles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('category_id')->constrained('kb_categories')->onDelete('cascade');
                $table->string('title');
                $table->string('slug')->unique();
                $table->longText('content');
                $table->text('excerpt')->nullable();
                $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
                $table->enum('visibility', ['public', 'internal', 'customer_portal'])->default('public');
                $table->integer('view_count')->default(0);
                $table->integer('helpful_count')->default(0);
                $table->integer('not_helpful_count')->default(0);
                $table->unsignedInteger('author_id');
                $table->timestamp('published_at')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index('slug');
                $table->index(['status', 'visibility']);
                $table->index('category_id');

                $table->foreign('author_id')->references('id')->on('users')->onDelete('cascade');
            });
        }

        // Knowledge Base Article Versions
        if (!Schema::hasTable('kb_article_versions')) {
            Schema::create('kb_article_versions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('article_id')->constrained('kb_articles')->onDelete('cascade');
                $table->string('title');
                $table->longText('content');
                $table->integer('version_number');
                $table->unsignedInteger('created_by');
                $table->timestamp('created_at');

                $table->index('article_id');

                $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            });
        }

        // Knowledge Base Article Tags
        if (!Schema::hasTable('kb_article_tags')) {
            Schema::create('kb_article_tags', function (Blueprint $table) {
                $table->id();
                $table->foreignId('article_id')->constrained('kb_articles')->onDelete('cascade');
                $table->unsignedInteger('tag_id');
                $table->timestamps();

                $table->unique(['article_id', 'tag_id']);

                $table->foreign('tag_id')->references('id')->on('tags')->onDelete('cascade');
            });
        }

        // Knowledge Base Article Attachments
        if (!Schema::hasTable('kb_article_attachments')) {
            Schema::create('kb_article_attachments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('article_id')->constrained('kb_articles')->onDelete('cascade');
                $table->string('file_name');
                $table->string('file_path');
                $table->integer('file_size');
                $table->string('mime_type');
                $table->timestamp('created_at');

                $table->index('article_id');
            });
        }

        // Knowledge Base Article Feedback
        if (!Schema::hasTable('kb_article_feedback')) {
            Schema::create('kb_article_feedback', function (Blueprint $table) {
                $table->id();
                $table->foreignId('article_id')->constrained('kb_articles')->onDelete('cascade');
                $table->unsignedInteger('user_id')->nullable();
                $table->boolean('is_helpful');
                $table->text('comment')->nullable();
                $table->timestamp('created_at');

                $table->index('article_id');

                $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kb_article_feedback');
        Schema::dropIfExists('kb_article_attachments');
        Schema::dropIfExists('kb_article_tags');
        Schema::dropIfExists('kb_article_versions');
        Schema::dropIfExists('kb_articles');
        Schema::dropIfExists('kb_categories');
    }
};
