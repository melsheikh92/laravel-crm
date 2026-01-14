<?php

use App\Models\DocArticle;
use App\Models\DocCategory;

test('search returns articles matching title', function () {
    $category = DocCategory::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
        'is_active' => true,
    ]);

    DocArticle::create([
        'title' => 'How to Configure CRM Settings',
        'slug' => 'configure-crm',
        'content' => '<p>Content here</p>',
        'type' => 'tutorial',
        'status' => 'published',
        'published_at' => now(),
        'category_id' => $category->id,
    ]);

    $response = $this->post('/api/docs/search', ['query' => 'configure']);
    $response->assertStatus(200);
    $data = json_decode($response->getContent(), true);
    expect($data)->toHaveCount(1);
    expect($data[0]['title'])->toContain('Configure');
});

test('search returns articles matching content', function () {
    $category = DocCategory::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
        'is_active' => true,
    ]);

    DocArticle::create([
        'title' => 'Installation Guide',
        'slug' => 'installation',
        'content' => '<p>This guide covers database configuration and setup</p>',
        'type' => 'tutorial',
        'status' => 'published',
        'published_at' => now(),
        'category_id' => $category->id,
    ]);

    $response = $this->post('/api/docs/search', ['query' => 'database']);
    $response->assertStatus(200);
    $data = json_decode($response->getContent(), true);
    expect($data)->toHaveCount(1);
});

test('search returns empty for no matches', function () {
    $category = DocCategory::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
        'is_active' => true,
    ]);

    DocArticle::create([
        'title' => 'Getting Started',
        'slug' => 'getting-started',
        'content' => '<p>Content</p>',
        'type' => 'tutorial',
        'status' => 'published',
        'published_at' => now(),
        'category_id' => $category->id,
    ]);

    $response = $this->post('/api/docs/search', ['query' => 'nonexistent term xyz']);
    $response->assertStatus(200);
    $data = json_decode($response->getContent(), true);
    expect($data)->toHaveCount(0);
});

test('search validation requires query', function () {
    $response = $this->post('/api/docs/search', []);
    $response->assertStatus(422); // Validation error
});

test('popular endpoint returns most viewed articles', function () {
    $category = DocCategory::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
        'is_active' => true,
    ]);

    $article1 = DocArticle::create([
        'title' => 'Most Popular',
        'slug' => 'most-popular',
        'content' => '<p>Test</p>',
        'type' => 'tutorial',
        'status' => 'published',
        'published_at' => now(),
        'view_count' => 500,
        'category_id' => $category->id,
    ]);

    $article2 = DocArticle::create([
        'title' => 'Less Popular',
        'slug' => 'less-popular',
        'content' => '<p>Test</p>',
        'type' => 'tutorial',
        'status' => 'published',
        'published_at' => now(),
        'view_count' => 100,
        'category_id' => $category->id,
    ]);

    $response = $this->get('/api/docs/popular?limit=10');
    $response->assertStatus(200);
    $data = json_decode($response->getContent(), true);
    expect($data)->toHaveCount(2);
    expect($data[0]['id'])->toBe($article1->id);
    expect($data[1]['id'])->toBe($article2->id);
});

test('helpful endpoint returns highest rated articles', function () {
    $category = DocCategory::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
        'is_active' => true,
    ]);

    $article1 = DocArticle::create([
        'title' => 'Highly Rated',
        'slug' => 'highly-rated',
        'content' => '<p>Test</p>',
        'type' => 'tutorial',
        'status' => 'published',
        'published_at' => now(),
        'helpful_count' => 50,
        'not_helpful_count' => 5,
        'category_id' => $category->id,
    ]);

    $article2 = DocArticle::create([
        'title' => 'Low Rated',
        'slug' => 'low-rated',
        'content' => '<p>Test</p>',
        'type' => 'tutorial',
        'status' => 'published',
        'published_at' => now(),
        'helpful_count' => 10,
        'not_helpful_count' => 20,
        'category_id' => $category->id,
    ]);

    $response = $this->get('/api/docs/helpful?limit=10');
    $response->assertStatus(200);
    $data = json_decode($response->getContent(), true);
    expect($data)->toHaveCount(2);
    expect($data[0]['id'])->toBe($article1->id);
});

test('autocomplete returns limited results', function () {
    $category = DocCategory::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
        'is_active' => true,
    ]);

    for ($i = 1; $i <= 20; $i++) {
        DocArticle::create([
            'title' => "Search Result {$i}",
            'slug' => "search-result-{$i}",
            'content' => '<p>Test</p>',
            'type' => 'tutorial',
            'status' => 'published',
            'published_at' => now(),
            'category_id' => $category->id,
        ]);
    }

    $response = $this->get('/api/docs/autocomplete?query=Search&limit=10');
    $response->assertStatus(200);
    $data = json_decode($response->getContent(), true);
    expect($data)->toHaveCount(10);
});

test('search by category endpoint filters correctly', function () {
    $category1 = DocCategory::create([
        'name' => 'Getting Started',
        'slug' => 'getting-started',
        'is_active' => true,
    ]);

    $category2 = DocCategory::create([
        'name' => 'API Docs',
        'slug' => 'api-docs',
        'is_active' => true,
    ]);

    $article1 = DocArticle::create([
        'title' => 'Getting Started Guide',
        'slug' => 'getting-started-guide',
        'content' => '<p>Test</p>',
        'type' => 'tutorial',
        'status' => 'published',
        'published_at' => now(),
        'category_id' => $category1->id,
    ]);

    DocArticle::create([
        'title' => 'API Reference',
        'slug' => 'api-reference',
        'content' => '<p>Test</p>',
        'type' => 'tutorial',
        'status' => 'published',
        'published_at' => now(),
        'category_id' => $category2->id,
    ]);

    $response = $this->get("/api/docs/category/{$category1->id}");
    $response->assertStatus(200);
    $data = json_decode($response->getContent(), true);
    expect($data)->toHaveCount(1);
    expect($data[0]['id'])->toBe($article1->id);
});

test('search by type endpoint filters correctly', function () {
    $category = DocCategory::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
        'is_active' => true,
    ]);

    $article1 = DocArticle::create([
        'title' => 'Quick Start',
        'slug' => 'quick-start',
        'content' => '<p>Test</p>',
        'type' => 'getting-started',
        'status' => 'published',
        'published_at' => now(),
        'category_id' => $category->id,
    ]);

    DocArticle::create([
        'title' => 'API Reference',
        'slug' => 'api-reference',
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
    expect($data[0]['id'])->toBe($article1->id);
});

test('search respects visibility filters', function () {
    $category = DocCategory::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
        'is_active' => true,
    ]);

    DocArticle::create([
        'title' => 'Public Article',
        'slug' => 'public-article',
        'content' => '<p>Public</p>',
        'type' => 'tutorial',
        'status' => 'published',
        'published_at' => now(),
        'visibility' => 'public',
        'category_id' => $category->id,
    ]);

    DocArticle::create([
        'title' => 'Internal Article',
        'slug' => 'internal-article',
        'content' => '<p>Internal</p>',
        'type' => 'tutorial',
        'status' => 'published',
        'published_at' => now(),
        'visibility' => 'internal',
        'category_id' => $category->id,
    ]);

    $response = $this->post('/api/docs/search', ['query' => 'article', 'visibility' => 'public']);
    $response->assertStatus(200);
    $data = json_decode($response->getContent(), true);
    expect($data)->toHaveCount(1);
    expect($data[0]['slug'])->toBe('public-article');
});

test('search excludes unpublished articles', function () {
    $category = DocCategory::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
        'is_active' => true,
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

    DocArticle::create([
        'title' => 'Draft Article',
        'slug' => 'draft-article',
        'content' => '<p>Test</p>',
        'type' => 'tutorial',
        'status' => 'draft',
        'category_id' => $category->id,
    ]);

    $response = $this->post('/api/docs/search', ['query' => 'article']);
    $response->assertStatus(200);
    $data = json_decode($response->getContent(), true);
    expect($data)->toHaveCount(1);
    expect($data[0]['slug'])->toBe('published-article');
});
