<?php

use App\Models\DocSection;
use App\Models\DocArticle;
use App\Models\DocCategory;

test('section belongs to an article', function () {
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

    $section = DocSection::create([
        'article_id' => $article->id,
        'title' => 'Test Section',
        'slug' => 'test-section',
        'content' => '<p>Section content</p>',
        'level' => 1,
        'sort_order' => 1,
    ]);

    expect($section->article)->toBeInstanceOf(DocArticle::class)
        ->and($section->article->id)->toBe($article->id)
        ->and($section->article->title)->toBe('Test Article');
});

test('section can have a parent section', function () {
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

    $parentSection = DocSection::create([
        'article_id' => $article->id,
        'title' => 'Parent Section',
        'slug' => 'parent-section',
        'content' => '<p>Parent content</p>',
        'level' => 1,
        'sort_order' => 1,
    ]);

    $childSection = DocSection::create([
        'article_id' => $article->id,
        'parent_id' => $parentSection->id,
        'title' => 'Child Section',
        'slug' => 'child-section',
        'content' => '<p>Child content</p>',
        'level' => 2,
        'sort_order' => 1,
    ]);

    expect($childSection->parent)->toBeInstanceOf(DocSection::class)
        ->and($childSection->parent->id)->toBe($parentSection->id)
        ->and($childSection->parent->title)->toBe('Parent Section');
});

test('section can have multiple children', function () {
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

    $parent = DocSection::create([
        'article_id' => $article->id,
        'title' => 'Parent Section',
        'slug' => 'parent-section',
        'content' => '<p>Parent content</p>',
        'level' => 1,
        'sort_order' => 1,
    ]);

    $child1 = DocSection::create([
        'article_id' => $article->id,
        'parent_id' => $parent->id,
        'title' => 'Child 1',
        'slug' => 'child-1',
        'content' => '<p>Content 1</p>',
        'level' => 2,
        'sort_order' => 1,
    ]);

    $child2 = DocSection::create([
        'article_id' => $article->id,
        'parent_id' => $parent->id,
        'title' => 'Child 2',
        'slug' => 'child-2',
        'content' => '<p>Content 2</p>',
        'level' => 2,
        'sort_order' => 2,
    ]);

    $parent->load('children');

    expect($parent->children)->toHaveCount(2)
        ->and($parent->children->pluck('id')->toArray())->toContain($child1->id, $child2->id);
});

test('slug is auto-generated from title on create', function () {
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

    $section = DocSection::create([
        'article_id' => $article->id,
        'title' => 'Test Section Title',
        'content' => '<p>Content</p>',
        'level' => 1,
        'sort_order' => 1,
    ]);

    expect($section->slug)->toBe('test-section-title');
});

test('is_root returns true for root level sections', function () {
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

    $rootSection = DocSection::create([
        'article_id' => $article->id,
        'title' => 'Root Section',
        'slug' => 'root-section',
        'content' => '<p>Content</p>',
        'level' => 1,
        'sort_order' => 1,
    ]);

    $childSection = DocSection::create([
        'article_id' => $article->id,
        'parent_id' => $rootSection->id,
        'title' => 'Child Section',
        'slug' => 'child-section',
        'content' => '<p>Content</p>',
        'level' => 2,
        'sort_order' => 1,
    ]);

    expect($rootSection->isRoot())->toBeTrue()
        ->and($childSection->isRoot())->toBeFalse();
});

test('has_children returns true for sections with children', function () {
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

    $parent = DocSection::create([
        'article_id' => $article->id,
        'title' => 'Parent Section',
        'slug' => 'parent-section',
        'content' => '<p>Parent content</p>',
        'level' => 1,
        'sort_order' => 1,
    ]);

    $child = DocSection::create([
        'article_id' => $article->id,
        'parent_id' => $parent->id,
        'title' => 'Child Section',
        'slug' => 'child-section',
        'content' => '<p>Child content</p>',
        'level' => 2,
        'sort_order' => 1,
    ]);

    expect($parent->hasChildren())->toBeTrue()
        ->and($child->hasChildren())->toBeFalse();
});

test('get_anchor_id returns the slug', function () {
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

    $section = DocSection::create([
        'article_id' => $article->id,
        'title' => 'Test Section',
        'slug' => 'test-section',
        'content' => '<p>Content</p>',
        'level' => 1,
        'sort_order' => 1,
    ]);

    expect($section->getAnchorIdAttribute())->toBe('test-section');
});

test('level and sort_order are cast to integers', function () {
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

    $section = DocSection::create([
        'article_id' => $article->id,
        'title' => 'Test Section',
        'slug' => 'test-section',
        'content' => '<p>Content</p>',
        'level' => '2',
        'sort_order' => '5',
    ]);

    expect($section->level)->toBeInt()
        ->and($section->level)->toBe(2)
        ->and($section->sort_order)->toBeInt()
        ->and($section->sort_order)->toBe(5);
});

test('scope root level filters level 1 sections', function () {
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

    $root1 = DocSection::create([
        'article_id' => $article->id,
        'title' => 'Root 1',
        'slug' => 'root-1',
        'content' => '<p>Content</p>',
        'level' => 1,
        'sort_order' => 1,
    ]);

    $root2 = DocSection::create([
        'article_id' => $article->id,
        'title' => 'Root 2',
        'slug' => 'root-2',
        'content' => '<p>Content</p>',
        'level' => 1,
        'sort_order' => 2,
    ]);

    DocSection::create([
        'article_id' => $article->id,
        'parent_id' => $root1->id,
        'title' => 'Child',
        'slug' => 'child',
        'content' => '<p>Content</p>',
        'level' => 2,
        'sort_order' => 1,
    ]);

    $rootSections = DocSection::rootLevel()->get();

    expect($rootSections)->toHaveCount(2)
        ->and($rootSections->pluck('id')->toArray())->toContain($root1->id, $root2->id);
});

test('scope by level filters sections by level', function () {
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

    DocSection::create([
        'article_id' => $article->id,
        'title' => 'Level 1',
        'slug' => 'level-1',
        'content' => '<p>Content</p>',
        'level' => 1,
        'sort_order' => 1,
    ]);

    DocSection::create([
        'article_id' => $article->id,
        'title' => 'Level 2',
        'slug' => 'level-2',
        'content' => '<p>Content</p>',
        'level' => 2,
        'sort_order' => 1,
    ]);

    $level1Sections = DocSection::byLevel(1)->get();
    $level2Sections = DocSection::byLevel(2)->get();

    expect($level1Sections)->toHaveCount(1)
        ->and($level1Sections->first()->level)->toBe(1)
        ->and($level2Sections)->toHaveCount(1)
        ->and($level2Sections->first()->level)->toBe(2);
});

test('scope ordered sorts by sort_order', function () {
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

    $section2 = DocSection::create([
        'article_id' => $article->id,
        'title' => 'Section 2',
        'slug' => 'section-2',
        'content' => '<p>Content</p>',
        'level' => 1,
        'sort_order' => 2,
    ]);

    $section1 = DocSection::create([
        'article_id' => $article->id,
        'title' => 'Section 1',
        'slug' => 'section-1',
        'content' => '<p>Content</p>',
        'level' => 1,
        'sort_order' => 1,
    ]);

    $orderedSections = DocSection::ordered()->get();

    expect($orderedSections->first()->id)->toBe($section1->id)
        ->and($orderedSections->last()->id)->toBe($section2->id);
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

    $section = DocSection::create([
        'article_id' => $article->id,
        'title' => 'Test Section',
        'slug' => 'test-section',
        'content' => '<p>Content</p>',
        'level' => 1,
        'sort_order' => 1,
    ]);

    $sectionId = $section->id;
    $section->delete();

    expect(DocSection::find($sectionId))->toBeNull()
        ->and(DocSection::withTrashed()->find($sectionId))->not->toBeNull();
});
