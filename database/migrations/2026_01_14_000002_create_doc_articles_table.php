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
        // Documentation Articles
        if (!Schema::hasTable('doc_articles')) {
            Schema::create('doc_articles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('category_id')->nullable()->constrained('doc_categories')->onDelete('set null');
                $table->string('title');
                $table->string('slug')->unique();
                $table->longText('content');
                $table->text('excerpt')->nullable();
                $table->enum('type', ['getting-started', 'api-doc', 'feature-guide', 'troubleshooting', 'tutorial'])->default('tutorial');
                $table->enum('difficulty_level', ['beginner', 'intermediate', 'advanced'])->default('beginner');
                $table->string('video_url')->nullable();
                $table->enum('video_type', ['youtube', 'vimeo'])->nullable();
                $table->integer('reading_time_minutes')->default(0);
                $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
                $table->enum('visibility', ['public', 'internal', 'customer_portal'])->default('public');
                $table->boolean('featured')->default(false);
                $table->integer('sort_order')->default(0);
                $table->integer('view_count')->default(0);
                $table->integer('helpful_count')->default(0);
                $table->integer('not_helpful_count')->default(0);
                $table->unsignedInteger('author_id')->nullable();
                $table->timestamp('published_at')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index('slug');
                $table->index(['status', 'visibility']);
                $table->index('type');
                $table->index('category_id');

                $table->foreign('author_id')->references('id')->on('users')->onDelete('set null');
            });
        }

        // Documentation Sections
        if (!Schema::hasTable('doc_sections')) {
            Schema::create('doc_sections', function (Blueprint $table) {
                $table->id();
                $table->foreignId('article_id')->constrained('doc_articles')->onDelete('cascade');
                $table->foreignId('parent_id')->nullable()->constrained('doc_sections')->onDelete('cascade');
                $table->string('title');
                $table->string('slug');
                $table->longText('content')->nullable();
                $table->integer('level')->default(1);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
                $table->softDeletes();

                $table->index(['article_id', 'sort_order']);
                $table->index('parent_id');
            });
        }

        // Documentation Article Versions
        if (!Schema::hasTable('doc_article_versions')) {
            Schema::create('doc_article_versions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('article_id')->constrained('doc_articles')->onDelete('cascade');
                $table->string('title');
                $table->longText('content');
                $table->integer('version_number');
                $table->unsignedInteger('created_by')->nullable();
                $table->timestamp('created_at');

                $table->index('article_id');

                $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            });
        }

        // Documentation Article Tags
        if (!Schema::hasTable('doc_article_tags')) {
            Schema::create('doc_article_tags', function (Blueprint $table) {
                $table->id();
                $table->foreignId('article_id')->constrained('doc_articles')->onDelete('cascade');
                $table->unsignedInteger('tag_id');
                $table->timestamps();

                $table->unique(['article_id', 'tag_id']);

                $table->foreign('tag_id')->references('id')->on('tags')->onDelete('cascade');
            });
        }

        // Documentation Article Attachments
        if (!Schema::hasTable('doc_article_attachments')) {
            Schema::create('doc_article_attachments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('article_id')->constrained('doc_articles')->onDelete('cascade');
                $table->string('file_name');
                $table->string('file_path');
                $table->integer('file_size');
                $table->string('mime_type');
                $table->timestamp('created_at');

                $table->index('article_id');
            });
        }

        // Documentation Article Feedback
        if (!Schema::hasTable('doc_article_feedback')) {
            Schema::create('doc_article_feedback', function (Blueprint $table) {
                $table->id();
                $table->foreignId('article_id')->constrained('doc_articles')->onDelete('cascade');
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
        Schema::dropIfExists('doc_article_feedback');
        Schema::dropIfExists('doc_article_attachments');
        Schema::dropIfExists('doc_article_tags');
        Schema::dropIfExists('doc_article_versions');
        Schema::dropIfExists('doc_sections');
        Schema::dropIfExists('doc_articles');
    }
};
