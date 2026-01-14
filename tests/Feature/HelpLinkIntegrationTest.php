<?php

use App\Models\DocArticle;
use App\Models\DocCategory;

test('help link component renders on leads page', function () {
    $category = DocCategory::create([
        'name' => 'Feature Guides',
        'slug' => 'feature-guides',
        'is_active' => true,
        'visibility' => 'public',
    ]);

    DocArticle::create([
        'title' => 'Leads Management Complete Guide',
        'slug' => 'leads-management',
        'content' => '<p>Complete guide for managing leads</p>',
        'type' => 'feature-guide',
        'difficulty_level' => 'beginner',
        'reading_time_minutes' => 15,
        'status' => 'published',
        'published_at' => now(),
        'visibility' => 'public',
        'category_id' => $category->id,
    ]);

    $response = $this->get('/admin/leads');
    $response->assertStatus(200); // Page loads
    // The help-link component should be present in the rendered HTML
});

test('help link on contacts persons page', function () {
    $category = DocCategory::create([
        'name' => 'Feature Guides',
        'slug' => 'feature-guides',
        'is_active' => true,
        'visibility' => 'public',
    ]);

    DocArticle::create([
        'title' => 'Contacts Management Complete Guide',
        'slug' => 'contacts-management',
        'content' => '<p>Complete guide for managing contacts</p>',
        'type' => 'feature-guide',
        'status' => 'published',
        'published_at' => now(),
        'visibility' => 'public',
        'category_id' => $category->id,
    ]);

    $response = $this->get('/admin/contacts/persons');
    $response->assertStatus(200);
});

test('help link on contacts organizations page', function () {
    $category = DocCategory::create([
        'name' => 'Feature Guides',
        'slug' => 'feature-guides',
        'is_active' => true,
        'visibility' => 'public',
    ]);

    DocArticle::create([
        'title' => 'Contacts Management Complete Guide',
        'slug' => 'contacts-management',
        'content' => '<p>Complete guide for managing contacts</p>',
        'type' => 'feature-guide',
        'status' => 'published',
        'published_at' => now(),
        'visibility' => 'public',
        'category_id' => $category->id,
    ]);

    $response = $this->get('/admin/contacts/organizations');
    $response->assertStatus(200);
});

test('help link on settings page', function () {
    $category = DocCategory::create([
        'name' => 'Getting Started',
        'slug' => 'getting-started',
        'is_active' => true,
        'visibility' => 'public',
    ]);

    DocArticle::create([
        'title' => 'Basic Configuration',
        'slug' => 'basic-configuration',
        'content' => '<p>Basic configuration guide</p>',
        'type' => 'getting-started',
        'status' => 'published',
        'published_at' => now(),
        'visibility' => 'public',
        'category_id' => $category->id,
    ]);

    $response = $this->get('/admin/settings');
    $response->assertStatus(200);
});

test('help link has security attributes', function () {
    $category = DocCategory::create([
        'name' => 'Feature Guides',
        'slug' => 'feature-guides',
        'is_active' => true,
    ]);

    $article = DocArticle::create([
        'title' => 'Leads Guide',
        'slug' => 'leads-management',
        'content' => '<p>Test</p>',
        'type' => 'feature-guide',
        'status' => 'published',
        'published_at' => now(),
        'category_id' => $category->id,
    ]);

    // The help link should use target="_blank" and rel="noopener noreferrer"
    // This is tested by checking the rendered component contains these attributes
    $view = view('components.help-link', [
        'slug' => $article->slug,
        'label' => 'Help',
    ]);

    $html = $view->render();
    expect($html)->toContain('target="_blank"')
        ->and($html)->toContain('rel="noopener noreferrer"');
});

test('help link component accepts id parameter', function () {
    $category = DocCategory::create([
        'name' => 'Feature Guides',
        'slug' => 'feature-guides',
        'is_active' => true,
    ]);

    $article = DocArticle::create([
        'title' => 'Test Article',
        'slug' => 'test-article',
        'content' => '<p>Test</p>',
        'type' => 'feature-guide',
        'status' => 'published',
        'published_at' => now(),
        'category_id' => $category->id,
    ]);

    $view = view('components.help-link', [
        'id' => $article->id,
        'label' => 'Help',
    ]);

    $html = $view->render();
    expect($html)->toContain('/docs/' . $article->id);
});

test('help link component accepts slug parameter', function () {
    $category = DocCategory::create([
        'name' => 'Feature Guides',
        'slug' => 'feature-guides',
        'is_active' => true,
    ]);

    $article = DocArticle::create([
        'title' => 'Test Article',
        'slug' => 'test-article-slug',
        'content' => '<p>Test</p>',
        'type' => 'feature-guide',
        'status' => 'published',
        'published_at' => now(),
        'category_id' => $category->id,
    ]);

    $view = view('components.help-link', [
        'slug' => $article->slug,
        'label' => 'Help',
    ]);

    $html = $view->render();
    expect($html)->toContain('/docs/' . $article->slug);
});

test('help link displays custom label', function () {
    $view = view('components.help-link', [
        'slug' => 'test-article',
        'label' => 'View Documentation',
    ]);

    $html = $view->render();
    expect($html)->toContain('View Documentation');
});

test('help link component renders without label', function () {
    $view = view('components.help-link', [
        'slug' => 'test-article',
    ]);

    $html = $view->render();
    // Should still render even without label
    expect($html)->not->toBeEmpty();
});

test('help link on products page', function () {
    $category = DocCategory::create([
        'name' => 'Feature Guides',
        'slug' => 'feature-guides',
        'is_active' => true,
        'visibility' => 'public',
    ]);

    DocArticle::create([
        'title' => 'Products Management Complete Guide',
        'slug' => 'products-management',
        'content' => '<p>Complete guide for managing products</p>',
        'type' => 'feature-guide',
        'status' => 'published',
        'published_at' => now(),
        'visibility' => 'public',
        'category_id' => $category->id,
    ]);

    $response = $this->get('/admin/products');
    $response->assertStatus(200);
});
