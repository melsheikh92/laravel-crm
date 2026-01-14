<?php

use App\Models\DocArticle;
use App\Models\DocCategory;
use Webkul\User\Models\User;
use Illuminate\Support\Facades\Auth;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('admin index requires authentication', function () {
    $response = $this->get('/admin/docs');
    $response->assertStatus(302); // Redirect to login
});

test('admin index displays articles when authenticated', function () {
    $user = getUser();
    Auth::login($user);

    $response = $this->get('/admin/docs');
    $response->assertStatus(200);
});

test('admin create shows form', function () {
    $user = getUser();
    Auth::login($user);

    $response = $this->get('/admin/docs/create');
    $response->assertStatus(200);
});

test('admin store creates article', function () {
    $user = getUser();
    Auth::login($user);

    $category = DocCategory::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
        'is_active' => true,
    ]);

    $data = [
        'title' => 'Test Article',
        'slug' => 'test-article',
        'content' => '<p>Test content</p>',
        'type' => 'tutorial',
        'difficulty_level' => 'beginner',
        'status' => 'draft',
        'visibility' => 'public',
        'category_id' => $category->id,
    ];

    $response = $this->post('/admin/docs', $data);
    $response->assertStatus(302); // Redirect after success

    $this->assertDatabaseHas('doc_articles', [
        'title' => 'Test Article',
        'slug' => 'test-article',
    ]);
});

test('admin store validates required fields', function () {
    $user = getUser();
    Auth::login($user);

    $response = $this->post('/admin/docs', []);
    $response->assertStatus(302); // Validation redirect
    $response->assertSessionHasErrors();
});

test('admin edit shows form with article', function () {
    $user = getUser();
    Auth::login($user);

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
        'category_id' => $category->id,
    ]);

    $response = $this->get("/admin/docs/{$article->id}/edit");
    $response->assertStatus(200);
    $response->assertSee('Test Article');
});

test('admin update modifies article', function () {
    $user = getUser();
    Auth::login($user);

    $category = DocCategory::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
        'is_active' => true,
    ]);

    $article = DocArticle::create([
        'title' => 'Original Title',
        'slug' => 'original-title',
        'content' => '<p>Original</p>',
        'type' => 'tutorial',
        'category_id' => $category->id,
    ]);

    $data = [
        'title' => 'Updated Title',
        'slug' => 'updated-title',
        'content' => '<p>Updated content</p>',
        'type' => 'tutorial',
        'status' => 'draft',
        'visibility' => 'public',
        'category_id' => $category->id,
    ];

    $response = $this->put("/admin/docs/{$article->id}", $data);
    $response->assertStatus(302);

    $this->assertDatabaseHas('doc_articles', [
        'id' => $article->id,
        'title' => 'Updated Title',
    ]);
});

test('admin delete removes article', function () {
    $user = getUser();
    Auth::login($user);

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
        'category_id' => $category->id,
    ]);

    $response = $this->delete("/admin/docs/{$article->id}");
    $response->assertStatus(302);

    $this->assertSoftDeleted('doc_articles', [
        'id' => $article->id,
    ]);
});

test('admin publish changes status to published', function () {
    $user = getUser();
    Auth::login($user);

    $category = DocCategory::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
        'is_active' => true,
    ]);

    $article = DocArticle::create([
        'title' => 'Draft Article',
        'slug' => 'draft-article',
        'content' => '<p>Test</p>',
        'type' => 'tutorial',
        'status' => 'draft',
        'category_id' => $category->id,
    ]);

    $response = $this->post("/admin/docs/{$article->id}/publish");
    $response->assertStatus(302);

    $article->refresh();
    expect($article->status)->toBe('published');
    expect($article->published_at)->not->toBeNull();
});

test('admin mass delete removes multiple articles', function () {
    $user = getUser();
    Auth::login($user);

    $category = DocCategory::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
        'is_active' => true,
    ]);

    $article1 = DocArticle::create([
        'title' => 'Article 1',
        'slug' => 'article-1',
        'content' => '<p>Test</p>',
        'type' => 'tutorial',
        'category_id' => $category->id,
    ]);

    $article2 = DocArticle::create([
        'title' => 'Article 2',
        'slug' => 'article-2',
        'content' => '<p>Test</p>',
        'type' => 'tutorial',
        'category_id' => $category->id,
    ]);

    $response = $this->post('/admin/docs/mass-destroy', [
        'indices' => [$article1->id, $article2->id],
    ]);
    $response->assertStatus(200);

    $this->assertSoftDeleted('doc_articles', ['id' => $article1->id]);
    $this->assertSoftDeleted('doc_articles', ['id' => $article2->id]);
});

test('admin stats returns json', function () {
    $user = getUser();
    Auth::login($user);

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

    $response = $this->get('/admin/docs/stats');
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'total',
        'published',
        'draft',
    ]);
});

test('admin update validates required fields', function () {
    $user = getUser();
    Auth::login($user);

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
        'category_id' => $category->id,
    ]);

    $response = $this->put("/admin/docs/{$article->id}", [
        'title' => '', // Empty title should fail validation
        'content' => '<p>Test</p>',
    ]);
    $response->assertStatus(302);
    $response->assertSessionHasErrors();
});

test('admin mass update changes status', function () {
    $user = getUser();
    Auth::login($user);

    $category = DocCategory::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
        'is_active' => true,
    ]);

    $article1 = DocArticle::create([
        'title' => 'Article 1',
        'slug' => 'article-1',
        'content' => '<p>Test</p>',
        'type' => 'tutorial',
        'status' => 'draft',
        'category_id' => $category->id,
    ]);

    $article2 = DocArticle::create([
        'title' => 'Article 2',
        'slug' => 'article-2',
        'content' => '<p>Test</p>',
        'type' => 'tutorial',
        'status' => 'draft',
        'category_id' => $category->id,
    ]);

    $response = $this->post('/admin/docs/mass-update', [
        'indices' => [$article1->id, $article2->id],
        'value' => 'published',
    ]);
    $response->assertStatus(200);

    $article1->refresh();
    $article2->refresh();
    expect($article1->status)->toBe('published');
    expect($article2->status)->toBe('published');
});

// Helper function to get/create user
function getUser()
{
    return \Webkul\User\Models\User::firstOrCreate(
        ['email' => 'admin@example.com'],
        [
            'name' => 'Admin User',
            'password' => bcrypt('password'),
            'role_id' => 1,
            'status' => 1,
        ]
    );
}
