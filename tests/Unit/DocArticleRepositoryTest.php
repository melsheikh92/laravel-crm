<?php

use App\Models\DocArticle;
use App\Models\DocCategory;
use Webkul\User\Models\User;
use App\Repositories\DocArticleRepository;
use Illuminate\Support\Facades\Auth;

test('repository creates article with author and tags', function () {
    $user = \Webkul\User\Models\User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
        'role_id' => 1,
        'status' => 1,
    ]);
    Auth::login($user);

    $category = DocCategory::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
        'is_active' => true,
        'visibility' => 'public',
    ]);

    $repository = app(DocArticleRepository::class);

    $article = $repository->create([
        'title' => 'Test Article',
        'slug' => 'test-article',
        'content' => '<p>Test content</p>',
        'type' => 'tutorial',
        'difficulty_level' => 'beginner',
        'status' => 'draft',
        'category_id' => $category->id,
    ]);

    expect($article)->toBeInstanceOf(DocArticle::class)
        ->and($article->title)->toBe('Test Article')
        ->and($article->author_id)->toBe($user->id)
        ->and($article->category_id)->toBe($category->id);
});

test('repository search filters by title', function () {
    $category = DocCategory::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
        'is_active' => true,
    ]);

    DocArticle::create([
        'title' => 'Getting Started Guide',
        'slug' => 'getting-started-guide',
        'content' => '<p>Content</p>',
        'type' => 'tutorial',
        'status' => 'published',
        'published_at' => now(),
        'category_id' => $category->id,
    ]);

    DocArticle::create([
        'title' => 'Advanced Configuration',
        'slug' => 'advanced-configuration',
        'content' => '<p>Content</p>',
        'type' => 'tutorial',
        'status' => 'published',
        'published_at' => now(),
        'category_id' => $category->id,
    ]);

    $repository = app(DocArticleRepository::class);
    $results = $repository->search('getting started');

    expect($results)->toHaveCount(1)
        ->and($results->first()->title)->toBe('Getting Started Guide');
});

test('repository search filters by content', function () {
    $category = DocCategory::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
        'is_active' => true,
    ]);

    DocArticle::create([
        'title' => 'Article One',
        'slug' => 'article-one',
        'content' => '<p>This contains unique search term</p>',
        'type' => 'tutorial',
        'status' => 'published',
        'published_at' => now(),
        'category_id' => $category->id,
    ]);

    DocArticle::create([
        'title' => 'Article Two',
        'slug' => 'article-two',
        'content' => '<p>Different content here</p>',
        'type' => 'tutorial',
        'status' => 'published',
        'published_at' => now(),
        'category_id' => $category->id,
    ]);

    $repository = app(DocArticleRepository::class);
    $results = $repository->search('unique search term');

    expect($results)->toHaveCount(1)
        ->and($results->first()->title)->toBe('Article One');
});

test('repository get popular articles ordered by views', function () {
    $category = DocCategory::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
        'is_active' => true,
    ]);

    $article1 = DocArticle::create([
        'title' => 'Popular Article',
        'slug' => 'popular-article',
        'content' => '<p>Content</p>',
        'type' => 'tutorial',
        'status' => 'published',
        'published_at' => now(),
        'view_count' => 100,
        'category_id' => $category->id,
    ]);

    $article2 = DocArticle::create([
        'title' => 'Less Popular Article',
        'slug' => 'less-popular-article',
        'content' => '<p>Content</p>',
        'type' => 'tutorial',
        'status' => 'published',
        'published_at' => now(),
        'view_count' => 50,
        'category_id' => $category->id,
    ]);

    $repository = app(DocArticleRepository::class);
    $popular = $repository->getPopularArticles(10);

    expect($popular)->toHaveCount(2)
        ->and($popular->first()->id)->toBe($article1->id)
        ->and($popular->first()->view_count)->toBe(100);
});

test('repository get helpful articles ordered by ratio', function () {
    $category = DocCategory::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
        'is_active' => true,
    ]);

    $article1 = DocArticle::create([
        'title' => 'Helpful Article',
        'slug' => 'helpful-article',
        'content' => '<p>Content</p>',
        'type' => 'tutorial',
        'status' => 'published',
        'published_at' => now(),
        'helpful_count' => 9,
        'not_helpful_count' => 1,
        'category_id' => $category->id,
    ]);

    $article2 = DocArticle::create([
        'title' => 'Less Helpful Article',
        'slug' => 'less-helpful-article',
        'content' => '<p>Content</p>',
        'type' => 'tutorial',
        'status' => 'published',
        'published_at' => now(),
        'helpful_count' => 5,
        'not_helpful_count' => 5,
        'category_id' => $category->id,
    ]);

    $repository = app(DocArticleRepository::class);
    $helpful = $repository->getHelpfulArticles(10);

    expect($helpful)->toHaveCount(2)
        ->and($helpful->first()->id)->toBe($article1->id)
        ->and($helpful->first()->helpful_count)->toBe(9);
});

test('repository publish method changes status to published', function () {
    $category = DocCategory::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
        'is_active' => true,
    ]);

    $article = DocArticle::create([
        'title' => 'Draft Article',
        'slug' => 'draft-article',
        'content' => '<p>Content</p>',
        'type' => 'tutorial',
        'status' => 'draft',
        'category_id' => $category->id,
    ]);

    $repository = app(DocArticleRepository::class);
    $publishedArticle = $repository->publish($article->id);

    expect($publishedArticle->status)->toBe('published')
        ->and($publishedArticle->published_at)->not->toBeNull();
});

test('repository unpublish method changes status to draft', function () {
    $category = DocCategory::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
        'is_active' => true,
    ]);

    $article = DocArticle::create([
        'title' => 'Published Article',
        'slug' => 'published-article',
        'content' => '<p>Content</p>',
        'type' => 'tutorial',
        'status' => 'published',
        'published_at' => now(),
        'category_id' => $category->id,
    ]);

    $repository = app(DocArticleRepository::class);
    $draftArticle = $repository->unpublish($article->id);

    expect($draftArticle->status)->toBe('draft');
});

test('repository get articles by type', function () {
    $category = DocCategory::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
        'is_active' => true,
    ]);

    DocArticle::create([
        'title' => 'Getting Started',
        'slug' => 'getting-started',
        'content' => '<p>Content</p>',
        'type' => 'getting-started',
        'status' => 'published',
        'published_at' => now(),
        'category_id' => $category->id,
    ]);

    DocArticle::create([
        'title' => 'API Doc',
        'slug' => 'api-doc',
        'content' => '<p>Content</p>',
        'type' => 'api-doc',
        'status' => 'published',
        'published_at' => now(),
        'category_id' => $category->id,
    ]);

    $repository = app(DocArticleRepository::class);
    $gettingStarted = $repository->getByType('getting-started');

    expect($gettingStarted)->toHaveCount(1)
        ->and($gettingStarted->first()->type)->toBe('getting-started');
});

test('repository get articles by difficulty', function () {
    $category = DocCategory::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
        'is_active' => true,
    ]);

    DocArticle::create([
        'title' => 'Beginner Guide',
        'slug' => 'beginner-guide',
        'content' => '<p>Content</p>',
        'type' => 'tutorial',
        'difficulty_level' => 'beginner',
        'status' => 'published',
        'published_at' => now(),
        'category_id' => $category->id,
    ]);

    DocArticle::create([
        'title' => 'Advanced Guide',
        'slug' => 'advanced-guide',
        'content' => '<p>Content</p>',
        'type' => 'tutorial',
        'difficulty_level' => 'advanced',
        'status' => 'published',
        'published_at' => now(),
        'category_id' => $category->id,
    ]);

    $repository = app(DocArticleRepository::class);
    $beginnerArticles = $repository->getByDifficulty('beginner');

    expect($beginnerArticles)->toHaveCount(1)
        ->and($beginnerArticles->first()->difficulty_level)->toBe('beginner');
});

test('repository get articles with video', function () {
    $category = DocCategory::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
        'is_active' => true,
    ]);

    DocArticle::create([
        'title' => 'Article With Video',
        'slug' => 'article-with-video',
        'content' => '<p>Content</p>',
        'type' => 'tutorial',
        'video_url' => 'https://youtube.com/watch?v=test',
        'video_type' => 'youtube',
        'status' => 'published',
        'published_at' => now(),
        'category_id' => $category->id,
    ]);

    DocArticle::create([
        'title' => 'Article Without Video',
        'slug' => 'article-without-video',
        'content' => '<p>Content</p>',
        'type' => 'tutorial',
        'status' => 'published',
        'published_at' => now(),
        'category_id' => $category->id,
    ]);

    $repository = app(DocArticleRepository::class);
    $withVideo = $repository->getWithVideo();

    expect($withVideo)->toHaveCount(1)
        ->and($withVideo->first()->slug)->toBe('article-with-video');
});

test('repository filter by multiple criteria', function () {
    $category = DocCategory::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
        'is_active' => true,
    ]);

    DocArticle::create([
        'title' => 'Published Beginner Article',
        'slug' => 'published-beginner',
        'content' => '<p>Content</p>',
        'type' => 'tutorial',
        'difficulty_level' => 'beginner',
        'status' => 'published',
        'published_at' => now(),
        'category_id' => $category->id,
    ]);

    DocArticle::create([
        'title' => 'Draft Article',
        'slug' => 'draft-article',
        'content' => '<p>Content</p>',
        'type' => 'tutorial',
        'difficulty_level' => 'beginner',
        'status' => 'draft',
        'category_id' => $category->id,
    ]);

    DocArticle::create([
        'title' => 'Advanced Article',
        'slug' => 'advanced-article',
        'content' => '<p>Content</p>',
        'type' => 'tutorial',
        'difficulty_level' => 'advanced',
        'status' => 'published',
        'published_at' => now(),
        'category_id' => $category->id,
    ]);

    $repository = app(DocArticleRepository::class);
    $query = $repository->filter([
        'status' => 'published',
        'difficulty_level' => 'beginner',
    ]);

    $results = $query->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->title)->toBe('Published Beginner Article');
});

test('repository update article', function () {
    $user = \Webkul\User\Models\User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
        'role_id' => 1,
        'status' => 1,
    ]);
    Auth::login($user);

    $category = DocCategory::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
        'is_active' => true,
    ]);

    $article = DocArticle::create([
        'title' => 'Original Title',
        'slug' => 'original-title',
        'content' => '<p>Original content</p>',
        'type' => 'tutorial',
        'status' => 'draft',
        'category_id' => $category->id,
    ]);

    $repository = app(DocArticleRepository::class);
    $updated = $repository->update([
        'title' => 'Updated Title',
        'content' => '<p>Updated content</p>',
    ], $article->id);

    expect($updated->title)->toBe('Updated Title')
        ->and($updated->content)->toContain('Updated content');
});

test('repository increment views', function () {
    $category = DocCategory::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
        'is_active' => true,
    ]);

    $article = DocArticle::create([
        'title' => 'Test Article',
        'slug' => 'test-article',
        'content' => '<p>Content</p>',
        'type' => 'tutorial',
        'view_count' => 10,
        'category_id' => $category->id,
    ]);

    $repository = app(DocArticleRepository::class);
    $updated = $repository->incrementViews($article->id);

    expect($updated->view_count)->toBe(11);
});

test('repository add feedback updates helpful counts', function () {
    $user = \Webkul\User\Models\User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
        'role_id' => 1,
        'status' => 1,
    ]);
    Auth::login($user);

    $category = DocCategory::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
        'is_active' => true,
    ]);

    $article = DocArticle::create([
        'title' => 'Test Article',
        'slug' => 'test-article',
        'content' => '<p>Content</p>',
        'type' => 'tutorial',
        'helpful_count' => 5,
        'not_helpful_count' => 2,
        'category_id' => $category->id,
    ]);

    $repository = app(DocArticleRepository::class);
    $feedback = $repository->addFeedback($article->id, [
        'is_helpful' => true,
        'comment' => 'Great article!',
    ]);

    expect($feedback->is_helpful)->toBeTrue();
    $article->refresh();
    expect($article->helpful_count)->toBe(6);
});
