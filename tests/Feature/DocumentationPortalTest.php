<?php

use App\Models\DocArticle;
use App\Models\DocCategory;
use Webkul\User\Models\User;

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('documentation home page loads successfully', function () {
    $response = $this->get('/docs');
    $response->assertStatus(200);
});

test('documentation home page displays categories', function () {
    DocCategory::create([
        'name' => 'Getting Started',
        'slug' => 'getting-started',
        'is_active' => true,
        'visibility' => 'public',
    ]);

    $response = $this->get('/docs');
    $response->assertStatus(200);
    $response->assertSee('Getting Started');
});

test('article page loads with published article', function () {
    $category = DocCategory::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
        'is_active' => true,
        'visibility' => 'public',
    ]);

    $article = DocArticle::create([
        'title' => 'Test Article',
        'slug' => 'test-article',
        'content' => '<p>Test content</p>',
        'type' => 'tutorial',
        'status' => 'published',
        'published_at' => now(),
        'visibility' => 'public',
        'category_id' => $category->id,
    ]);

    $response = $this->get("/docs/{$article->id}");
    $response->assertStatus(200);
    $response->assertSee('Test Article');
    $response->assertSee('Test content');
});

test('article page returns 404 for unpublished articles', function () {
    $category = DocCategory::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
        'is_active' => true,
    ]);

    $article = DocArticle::create([
        'title' => 'Draft Article',
        'slug' => 'draft-article',
        'content' => '<p>Draft content</p>',
        'type' => 'tutorial',
        'status' => 'draft',
        'category_id' => $category->id,
    ]);

    $response = $this->get("/docs/{$article->id}");
    $response->assertStatus(404);
});

test('article page returns 404 for private visibility', function () {
    $category = DocCategory::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
        'is_active' => true,
    ]);

    $article = DocArticle::create([
        'title' => 'Private Article',
        'slug' => 'private-article',
        'content' => '<p>Private content</p>',
        'type' => 'tutorial',
        'status' => 'published',
        'published_at' => now(),
        'visibility' => 'internal',
        'category_id' => $category->id,
    ]);

    $response = $this->get("/docs/{$article->id}");
    $response->assertStatus(404);
});

test('vote endpoint increments helpful count', function () {
    $category = DocCategory::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
        'is_active' => true,
    ]);

    $article = DocArticle::create([
        'title' => 'Test Article',
        'slug' => 'test-article',
        'content' => '<p>Test</p>',
        'type' => 'tutorial',
        'status' => 'published',
        'published_at' => now(),
        'category_id' => $category->id,
    ]);

    $response = $this->post("/docs/{$article->id}/vote", ['helpful' => true]);
    $response->assertStatus(302); // Redirect back

    $article->refresh();
    expect($article->helpful_count)->toBe(1);
});

test('search returns results for matching articles', function () {
    $category = DocCategory::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
        'is_active' => true,
    ]);

    DocArticle::create([
        'title' => 'Getting Started with CRM',
        'slug' => 'getting-started-crm',
        'content' => '<p>This is a getting started guide</p>',
        'type' => 'tutorial',
        'status' => 'published',
        'published_at' => now(),
        'category_id' => $category->id,
    ]);

    $response = $this->post('/docs/search', ['query' => 'getting started']);
    $response->assertStatus(200);
    $response->assertJsonCount(1);
});

test('search requires query parameter', function () {
    $response = $this->post('/docs/search', []);
    $response->assertStatus(302); // Validation redirect
});

test('popular articles endpoint returns most viewed', function () {
    $category = DocCategory::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
        'is_active' => true,
    ]);

    $article1 = DocArticle::create([
        'title' => 'Popular Article',
        'slug' => 'popular-article',
        'content' => '<p>Test</p>',
        'type' => 'tutorial',
        'status' => 'published',
        'published_at' => now(),
        'view_count' => 100,
        'category_id' => $category->id,
    ]);

    DocArticle::create([
        'title' => 'Unpopular Article',
        'slug' => 'unpopular-article',
        'content' => '<p>Test</p>',
        'type' => 'tutorial',
        'status' => 'published',
        'published_at' => now(),
        'view_count' => 10,
        'category_id' => $category->id,
    ]);

    $response = $this->get('/api/docs/popular');
    $response->assertStatus(200);
    $data = json_decode($response->getContent(), true);
    expect($data[0]['id'])->toBe($article1->id);
});

test('helpful articles endpoint returns highest rated', function () {
    $category = DocCategory::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
        'is_active' => true,
    ]);

    $article1 = DocArticle::create([
        'title' => 'Helpful Article',
        'slug' => 'helpful-article',
        'content' => '<p>Test</p>',
        'type' => 'tutorial',
        'status' => 'published',
        'published_at' => now(),
        'helpful_count' => 10,
        'not_helpful_count' => 1,
        'category_id' => $category->id,
    ]);

    DocArticle::create([
        'title' => 'Unhelpful Article',
        'slug' => 'unhelpful-article',
        'content' => '<p>Test</p>',
        'type' => 'tutorial',
        'status' => 'published',
        'published_at' => now(),
        'helpful_count' => 2,
        'not_helpful_count' => 10,
        'category_id' => $category->id,
    ]);

    $response = $this->get('/api/docs/helpful');
    $response->assertStatus(200);
    $data = json_decode($response->getContent(), true);
    expect($data[0]['id'])->toBe($article1->id);
});

test('autocomplete returns limited results', function () {
    $category = DocCategory::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
        'is_active' => true,
    ]);

    for ($i = 1; $i <= 15; $i++) {
        DocArticle::create([
            'title' => "Article {$i}",
            'slug' => "article-{$i}",
            'content' => '<p>Test</p>',
            'type' => 'tutorial',
            'status' => 'published',
            'published_at' => now(),
            'category_id' => $category->id,
        ]);
    }

    $response = $this->get('/api/docs/autocomplete?query=Article');
    $response->assertStatus(200);
    $data = json_decode($response->getContent(), true);
    expect($data)->toHaveCount(10); // Default limit is 10
});

test('article by type endpoint filters correctly', function () {
    $category = DocCategory::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
        'is_active' => true,
    ]);

    DocArticle::create([
        'title' => 'Getting Started',
        'slug' => 'getting-started',
        'content' => '<p>Test</p>',
        'type' => 'getting-started',
        'status' => 'published',
        'published_at' => now(),
        'category_id' => $category->id,
    ]);

    DocArticle::create([
        'title' => 'API Doc',
        'slug' => 'api-doc',
        'content' => '<p>Test</p>',
        'type' => 'api-doc',
        'status' => 'published',
        'published_at' => now(),
        'category_id' => $category->id,
    ]);

    $response = $this->get('/api/docs/type/getting-started');
    $response->assertStatus(200);
    $data = json_decode($response->getContent(), true);
    expect($data)->toHaveCount(1);
    expect($data[0]['type'])->toBe('getting-started');
});
