<?php

use App\Models\DocArticle;
use App\Models\DocCategory;
use App\Models\DocSection;
use Webkul\User\Models\User;

test('article has relationships with category, author, and sections', function () {
    $user = \Webkul\User\Models\User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
        'role_id' => 1,
        'status' => 1,
    ]);
    $category = DocCategory::create([
        'name' => 'Getting Started',
        'slug' => 'getting-started',
        'is_active' => true,
        'visibility' => 'public',
    ]);

    $article = DocArticle::create([
        'title' => 'Test Article',
        'slug' => 'test-article',
        'content' => '<p>Test content</p>',
        'type' => 'getting-started',
        'difficulty_level' => 'beginner',
        'status' => 'published',
        'visibility' => 'public',
        'category_id' => $category->id,
        'author_id' => $user->id,
        'published_at' => now(),
    ]);

    expect($article->category)->toBeInstanceOf(DocCategory::class)
        ->and($article->category->id)->toBe($category->id)
        ->and($article->author)->toBeInstanceOf(User::class)
        ->and($article->author->id)->toBe($user->id)
        ->and($article->sections)->toHaveCount(0);
});

test('article can have multiple sections', function () {
    $category = DocCategory::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
        'is_active' => true,
    ]);

    $article = DocArticle::create([
        'title' => 'Test Article',
        'slug' => 'test-article',
        'content' => '<p>Test content</p>',
        'type' => 'tutorial',
        'category_id' => $category->id,
    ]);

    $section1 = DocSection::create([
        'article_id' => $article->id,
        'title' => 'Section 1',
        'slug' => 'section-1',
        'content' => '<p>Content 1</p>',
        'level' => 1,
        'sort_order' => 1,
    ]);

    $section2 = DocSection::create([
        'article_id' => $article->id,
        'title' => 'Section 2',
        'slug' => 'section-2',
        'content' => '<p>Content 2</p>',
        'level' => 1,
        'sort_order' => 2,
    ]);

    $article->load('sections');

    expect($article->sections)->toHaveCount(2)
        ->and($article->sections->pluck('id')->toArray())->toContain($section1->id, $section2->id);
});

test('youtube video url parsing extracts correct video id', function () {
    $category = DocCategory::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
    ]);

    $article1 = DocArticle::create([
        'title' => 'YouTube Video Article',
        'slug' => 'youtube-video',
        'content' => '<p>Test</p>',
        'type' => 'tutorial',
        'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        'video_type' => 'youtube',
        'category_id' => $category->id,
    ]);

    expect($article1->hasVideo())->toBeTrue()
        ->and($article1->getVideoEmbedUrlAttribute())->toBe('https://www.youtube.com/embed/dQw4w9WgXcQ');

    $article2 = DocArticle::create([
        'title' => 'YouTube Short Video Article',
        'slug' => 'youtube-short-video',
        'content' => '<p>Test</p>',
        'type' => 'tutorial',
        'video_url' => 'https://youtu.be/dQw4w9WgXcQ',
        'video_type' => 'youtube',
        'category_id' => $category->id,
    ]);

    expect($article2->getVideoEmbedUrlAttribute())->toBe('https://www.youtube.com/embed/dQw4w9WgXcQ');
});

test('vimeo video url parsing extracts correct video id', function () {
    $category = DocCategory::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
    ]);

    $article = DocArticle::create([
        'title' => 'Vimeo Video Article',
        'slug' => 'vimeo-video',
        'content' => '<p>Test</p>',
        'type' => 'tutorial',
        'video_url' => 'https://vimeo.com/123456789',
        'video_type' => 'vimeo',
        'category_id' => $category->id,
    ]);

    expect($article->hasVideo())->toBeTrue()
        ->and($article->getVideoEmbedUrlAttribute())->toBe('https://player.vimeo.com/video/123456789');
});

test('reading time calculation estimates correctly', function () {
    $category = DocCategory::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
    ]);

    // 200 words = 1 minute
    $article1 = DocArticle::create([
        'title' => 'Short Article',
        'slug' => 'short-article',
        'content' => '<p>' . str_repeat('word ', 200) . '</p>',
        'type' => 'tutorial',
        'category_id' => $category->id,
    ]);

    expect($article1->reading_time_minutes)->toBe(1);

    // 400 words = 2 minutes
    $article2 = DocArticle::create([
        'title' => 'Medium Article',
        'slug' => 'medium-article',
        'content' => '<p>' . str_repeat('word ', 400) . '</p>',
        'type' => 'tutorial',
        'category_id' => $category->id,
    ]);

    expect($article2->reading_time_minutes)->toBe(2);
});

test('slug is auto-generated from title on create', function () {
    $category = DocCategory::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
    ]);

    $article = DocArticle::create([
        'title' => 'Test Article Title',
        'content' => '<p>Test content</p>',
        'type' => 'tutorial',
        'category_id' => $category->id,
    ]);

    expect($article->slug)->toBe('test-article-title');
});

test('excerpt is auto-generated from content if not provided', function () {
    $category = DocCategory::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
    ]);

    $article = DocArticle::create([
        'title' => 'Test Article',
        'slug' => 'test-article',
        'content' => '<p>This is a long content that should be truncated to create an excerpt automatically when none is provided.</p>',
        'type' => 'tutorial',
        'category_id' => $category->id,
    ]);

    expect($article->excerpt)->not->toBeEmpty()
        ->and(strlen($article->excerpt))->toBeLessThanOrEqual(200);
});

test('scope by type filters articles correctly', function () {
    $category = DocCategory::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
    ]);

    DocArticle::create([
        'title' => 'Getting Started Article',
        'slug' => 'getting-started-article',
        'content' => '<p>Test</p>',
        'type' => 'getting-started',
        'status' => 'published',
        'published_at' => now(),
        'category_id' => $category->id,
    ]);

    DocArticle::create([
        'title' => 'API Documentation',
        'slug' => 'api-documentation',
        'content' => '<p>Test</p>',
        'type' => 'api-doc',
        'status' => 'published',
        'published_at' => now(),
        'category_id' => $category->id,
    ]);

    $gettingStartedArticles = DocArticle::byType('getting-started')->get();
    $apiArticles = DocArticle::byType('api-doc')->get();

    expect($gettingStartedArticles)->toHaveCount(1)
        ->and($gettingStartedArticles->first()->type)->toBe('getting-started')
        ->and($apiArticles)->toHaveCount(1)
        ->and($apiArticles->first()->type)->toBe('api-doc');
});

test('scope by difficulty filters articles correctly', function () {
    $category = DocCategory::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
    ]);

    DocArticle::create([
        'title' => 'Beginner Article',
        'slug' => 'beginner-article',
        'content' => '<p>Test</p>',
        'type' => 'tutorial',
        'difficulty_level' => 'beginner',
        'status' => 'published',
        'published_at' => now(),
        'category_id' => $category->id,
    ]);

    DocArticle::create([
        'title' => 'Advanced Article',
        'slug' => 'advanced-article',
        'content' => '<p>Test</p>',
        'type' => 'tutorial',
        'difficulty_level' => 'advanced',
        'status' => 'published',
        'published_at' => now(),
        'category_id' => $category->id,
    ]);

    $beginnerArticles = DocArticle::byDifficulty('beginner')->get();
    $advancedArticles = DocArticle::byDifficulty('advanced')->get();

    expect($beginnerArticles)->toHaveCount(1)
        ->and($beginnerArticles->first()->difficulty_level)->toBe('beginner')
        ->and($advancedArticles)->toHaveCount(1)
        ->and($advancedArticles->first()->difficulty_level)->toBe('advanced');
});

test('scope with video filters articles correctly', function () {
    $category = DocCategory::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
    ]);

    DocArticle::create([
        'title' => 'Article With Video',
        'slug' => 'article-with-video',
        'content' => '<p>Test</p>',
        'type' => 'tutorial',
        'video_url' => 'https://www.youtube.com/watch?v=test',
        'video_type' => 'youtube',
        'category_id' => $category->id,
    ]);

    DocArticle::create([
        'title' => 'Article Without Video',
        'slug' => 'article-without-video',
        'content' => '<p>Test</p>',
        'type' => 'tutorial',
        'category_id' => $category->id,
    ]);

    $articlesWithVideo = DocArticle::withVideo()->get();

    expect($articlesWithVideo)->toHaveCount(1)
        ->and($articlesWithVideo->first()->slug)->toBe('article-with-video');
});

test('helper methods work correctly', function () {
    $category = DocCategory::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
    ]);

    $article = DocArticle::create([
        'title' => 'Published Article',
        'slug' => 'published-article',
        'content' => '<p>Test</p>',
        'type' => 'tutorial',
        'status' => 'published',
        'published_at' => now()->subDay(),
        'category_id' => $category->id,
    ]);

    expect($article->isPublished())->toBeTrue()
        ->and($article->hasVideo())->toBeFalse();

    $article->update([
        'video_url' => 'https://www.youtube.com/watch?v=test',
        'video_type' => 'youtube',
    ]);

    expect($article->fresh()->hasVideo())->toBeTrue();
});

test('published scope filters published articles', function () {
    $category = DocCategory::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
    ]);

    DocArticle::create([
        'title' => 'Draft Article',
        'slug' => 'draft-article',
        'content' => '<p>Test</p>',
        'type' => 'tutorial',
        'status' => 'draft',
        'category_id' => $category->id,
    ]);

    DocArticle::create([
        'title' => 'Published Article',
        'slug' => 'published-article',
        'content' => '<p>Test</p>',
        'type' => 'tutorial',
        'status' => 'published',
        'published_at' => now(),
        'category_id' => $category->id,
    ]);

    $publishedArticles = DocArticle::published()->get();

    expect($publishedArticles)->toHaveCount(1)
        ->and($publishedArticles->first()->slug)->toBe('published-article');
});

test('public scope filters public articles', function () {
    $category = DocCategory::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
    ]);

    DocArticle::create([
        'title' => 'Public Article',
        'slug' => 'public-article',
        'content' => '<p>Test</p>',
        'type' => 'tutorial',
        'visibility' => 'public',
        'status' => 'published',
        'published_at' => now(),
        'category_id' => $category->id,
    ]);

    DocArticle::create([
        'title' => 'Internal Article',
        'slug' => 'internal-article',
        'content' => '<p>Test</p>',
        'type' => 'tutorial',
        'visibility' => 'internal',
        'status' => 'published',
        'published_at' => now(),
        'category_id' => $category->id,
    ]);

    $publicArticles = DocArticle::public()->get();

    expect($publicArticles)->toHaveCount(1)
        ->and($publicArticles->first()->slug)->toBe('public-article');
});

test('helpfulness ratio calculation works correctly', function () {
    $category = DocCategory::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
    ]);

    $article = DocArticle::create([
        'title' => 'Test Article',
        'slug' => 'test-article',
        'content' => '<p>Test</p>',
        'type' => 'tutorial',
        'helpful_count' => 8,
        'not_helpful_count' => 2,
        'category_id' => $category->id,
    ]);

    expect($article->helpfulness_ratio)->toBe(80.0);
});

test('soft deletes work correctly', function () {
    $category = DocCategory::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
    ]);

    $article = DocArticle::create([
        'title' => 'Test Article',
        'slug' => 'test-article',
        'content' => '<p>Test</p>',
        'type' => 'tutorial',
        'category_id' => $category->id,
    ]);

    $articleId = $article->id;
    $article->delete();

    expect(DocArticle::find($articleId))->toBeNull()
        ->and(DocArticle::withTrashed()->find($articleId))->not->toBeNull();
});
