<?php

use App\Models\DocCategory;
use App\Models\DocArticle;

test('category has parent relationship', function () {
    $parent = DocCategory::create([
        'name' => 'Parent Category',
        'slug' => 'parent-category',
        'is_active' => true,
        'visibility' => 'public',
    ]);

    $child = DocCategory::create([
        'name' => 'Child Category',
        'slug' => 'child-category',
        'parent_id' => $parent->id,
        'is_active' => true,
        'visibility' => 'public',
    ]);

    expect($child->parent)->toBeInstanceOf(DocCategory::class)
        ->and($child->parent->id)->toBe($parent->id)
        ->and($child->parent->name)->toBe('Parent Category');
});

test('category has children relationship', function () {
    $parent = DocCategory::create([
        'name' => 'Parent Category',
        'slug' => 'parent-category',
        'is_active' => true,
        'visibility' => 'public',
    ]);

    $child1 = DocCategory::create([
        'name' => 'Child Category 1',
        'slug' => 'child-category-1',
        'parent_id' => $parent->id,
        'is_active' => true,
        'visibility' => 'public',
    ]);

    $child2 = DocCategory::create([
        'name' => 'Child Category 2',
        'slug' => 'child-category-2',
        'parent_id' => $parent->id,
        'is_active' => true,
        'visibility' => 'public',
    ]);

    $parent->load('children');

    expect($parent->children)->toHaveCount(2)
        ->and($parent->children->pluck('id')->toArray())->toContain($child1->id, $child2->id);
});

test('category has many articles', function () {
    $category = DocCategory::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
        'is_active' => true,
        'visibility' => 'public',
    ]);

    DocArticle::create([
        'title' => 'Article 1',
        'slug' => 'article-1',
        'content' => '<p>Test</p>',
        'type' => 'tutorial',
        'category_id' => $category->id,
    ]);

    DocArticle::create([
        'title' => 'Article 2',
        'slug' => 'article-2',
        'content' => '<p>Test</p>',
        'type' => 'tutorial',
        'category_id' => $category->id,
    ]);

    $category->load('articles');

    expect($category->articles)->toHaveCount(2)
        ->and($category->articles->pluck('title')->toArray())->toContain('Article 1', 'Article 2');
});

test('slug is auto-generated from name on create', function () {
    $category = DocCategory::create([
        'name' => 'Test Category Name',
        'is_active' => true,
        'visibility' => 'public',
    ]);

    expect($category->slug)->toBe('test-category-name');
});

test('active scope filters active categories', function () {
    DocCategory::create([
        'name' => 'Active Category',
        'slug' => 'active-category',
        'is_active' => true,
        'visibility' => 'public',
    ]);

    DocCategory::create([
        'name' => 'Inactive Category',
        'slug' => 'inactive-category',
        'is_active' => false,
        'visibility' => 'public',
    ]);

    $activeCategories = DocCategory::active()->get();

    expect($activeCategories)->toHaveCount(1)
        ->and($activeCategories->first()->name)->toBe('Active Category');
});

test('public scope filters public categories', function () {
    DocCategory::create([
        'name' => 'Public Category',
        'slug' => 'public-category',
        'is_active' => true,
        'visibility' => 'public',
    ]);

    DocCategory::create([
        'name' => 'Internal Category',
        'slug' => 'internal-category',
        'is_active' => true,
        'visibility' => 'internal',
    ]);

    $publicCategories = DocCategory::public()->get();

    expect($publicCategories)->toHaveCount(1)
        ->and($publicCategories->first()->name)->toBe('Public Category');
});

test('root categories scope returns top level categories', function () {
    $parent = DocCategory::create([
        'name' => 'Parent Category',
        'slug' => 'parent-category',
        'is_active' => true,
        'visibility' => 'public',
        'sort_order' => 1,
    ]);

    DocCategory::create([
        'name' => 'Child Category',
        'slug' => 'child-category',
        'parent_id' => $parent->id,
        'is_active' => true,
        'visibility' => 'public',
    ]);

    DocCategory::create([
        'name' => 'Another Root Category',
        'slug' => 'another-root-category',
        'is_active' => true,
        'visibility' => 'public',
        'sort_order' => 2,
    ]);

    $rootCategories = DocCategory::rootCategories()->get();

    expect($rootCategories)->toHaveCount(2)
        ->and($rootCategories->pluck('id')->toArray())->toContain($parent->id);
});

test('customer portal scope includes public and customer portal categories', function () {
    DocCategory::create([
        'name' => 'Public Category',
        'slug' => 'public-category',
        'is_active' => true,
        'visibility' => 'public',
    ]);

    DocCategory::create([
        'name' => 'Customer Portal Category',
        'slug' => 'customer-portal-category',
        'is_active' => true,
        'visibility' => 'customer_portal',
    ]);

    DocCategory::create([
        'name' => 'Internal Category',
        'slug' => 'internal-category',
        'is_active' => true,
        'visibility' => 'internal',
    ]);

    $customerPortalCategories = DocCategory::customerPortal()->get();

    expect($customerPortalCategories)->toHaveCount(2)
        ->and($customerPortalCategories->pluck('visibility')->toArray())
            ->toContain('public', 'customer_portal')
        ->and($customerPortalCategories->pluck('visibility')->toArray())
            ->not()->toContain('internal');
});

test('is_active is cast to boolean', function () {
    $category = DocCategory::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
        'is_active' => true,
        'visibility' => 'public',
    ]);

    expect($category->is_active)->toBeBool()
        ->and($category->is_active)->toBeTrue();
});

test('sort_order is cast to integer', function () {
    $category = DocCategory::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
        'is_active' => true,
        'visibility' => 'public',
        'sort_order' => '5',
    ]);

    expect($category->sort_order)->toBeInt()
        ->and($category->sort_order)->toBe(5);
});

test('nested categories work correctly', function () {
    $root = DocCategory::create([
        'name' => 'Root Category',
        'slug' => 'root-category',
        'is_active' => true,
        'visibility' => 'public',
    ]);

    $child1 = DocCategory::create([
        'name' => 'Child 1',
        'slug' => 'child-1',
        'parent_id' => $root->id,
        'is_active' => true,
        'visibility' => 'public',
    ]);

    $child2 = DocCategory::create([
        'name' => 'Child 2',
        'slug' => 'child-2',
        'parent_id' => $root->id,
        'is_active' => true,
        'visibility' => 'public',
    ]);

    $grandchild = DocCategory::create([
        'name' => 'Grandchild',
        'slug' => 'grandchild',
        'parent_id' => $child1->id,
        'is_active' => true,
        'visibility' => 'public',
    ]);

    $root->load('children');

    expect($root->children)->toHaveCount(2)
        ->and($root->children->first()->children)->toHaveCount(1)
        ->and($root->children->first()->children->first()->name)->toBe('Grandchild');
});

test('category can be created with all fields', function () {
    $category = DocCategory::create([
        'name' => 'Complete Category',
        'slug' => 'complete-category',
        'description' => 'A complete category description',
        'icon' => 'heroicon-o-book',
        'sort_order' => 10,
        'is_active' => true,
        'visibility' => 'public',
    ]);

    expect($category->name)->toBe('Complete Category')
        ->and($category->slug)->toBe('complete-category')
        ->and($category->description)->toBe('A complete category description')
        ->and($category->icon)->toBe('heroicon-o-book')
        ->and($category->sort_order)->toBe(10)
        ->and($category->is_active)->toBeTrue()
        ->and($category->visibility)->toBe('public');
});
