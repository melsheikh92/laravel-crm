<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Webkul\Marketplace\Models\Extension;
use Webkul\Marketplace\Models\ExtensionCategory;
use Webkul\Marketplace\Models\ExtensionSubmission;
use Webkul\Marketplace\Models\ExtensionTransaction;
use Webkul\Marketplace\Models\ExtensionVersion;
use Webkul\User\Models\User;

uses(RefreshDatabase::class);

/**
 * Admin Submission Review Tests
 */
describe('Admin Submission Review Flow', function () {
    beforeEach(function () {
        $this->admin = getDefaultAdmin();

        $this->developer = User::factory()->create([
            'is_developer' => true,
            'developer_status' => 'approved',
        ]);

        $this->extension = Extension::factory()->create([
            'author_id' => $this->developer->id,
            'status' => 'draft',
        ]);

        $this->version = ExtensionVersion::factory()->create([
            'extension_id' => $this->extension->id,
            'status' => 'draft',
        ]);

        $this->submission = ExtensionSubmission::create([
            'extension_id' => $this->extension->id,
            'version_id' => $this->version->id,
            'submitted_by' => $this->developer->id,
            'status' => 'pending',
            'submitted_at' => now(),
        ]);
    });

    it('can view submissions index page', function () {
        $response = test()->actingAs($this->admin)
            ->get(route('admin.marketplace.submissions.index'));

        $response->assertOK()
            ->assertSee('Submissions');
    });

    it('can view submission details', function () {
        $response = test()->actingAs($this->admin)
            ->get(route('admin.marketplace.submissions.show', ['id' => $this->submission->id]));

        $response->assertOK();
    });

    it('can view submission review form', function () {
        $response = test()->actingAs($this->admin)
            ->get(route('admin.marketplace.submissions.review', ['id' => $this->submission->id]));

        $response->assertOK();
    });

    it('can approve a pending submission', function () {
        Event::fake();

        $response = test()->actingAs($this->admin)
            ->post(route('admin.marketplace.submissions.approve', ['id' => $this->submission->id]), [
                'review_notes' => 'Extension looks good, approved!',
            ]);

        $response->assertOK();

        $this->submission->refresh();
        expect($this->submission->status)->toBe('approved')
            ->and($this->submission->reviewed_by)->toBe($this->admin->id)
            ->and($this->submission->review_notes)->toBe('Extension looks good, approved!');

        Event::assertDispatched('marketplace.submission.approve.before');
        Event::assertDispatched('marketplace.submission.approve.after');
    });

    it('can reject a pending submission with reason', function () {
        Event::fake();

        $response = test()->actingAs($this->admin)
            ->post(route('admin.marketplace.submissions.reject', ['id' => $this->submission->id]), [
                'review_notes' => 'Please fix security vulnerabilities before resubmission.',
            ]);

        $response->assertOK();

        $this->submission->refresh();
        expect($this->submission->status)->toBe('rejected')
            ->and($this->submission->reviewed_by)->toBe($this->admin->id)
            ->and($this->submission->review_notes)->toBe('Please fix security vulnerabilities before resubmission.');

        Event::assertDispatched('marketplace.submission.reject.before');
        Event::assertDispatched('marketplace.submission.reject.after');
    });

    it('validates review notes are required for rejection', function () {
        $response = test()->actingAs($this->admin)
            ->post(route('admin.marketplace.submissions.reject', ['id' => $this->submission->id]), [
                'review_notes' => '',
            ]);

        $response->assertSessionHasErrors(['review_notes']);
    });

    it('prevents reviewing already reviewed submission', function () {
        $this->submission->update([
            'status' => 'approved',
            'reviewed_by' => $this->admin->id,
            'reviewed_at' => now(),
        ]);

        $response = test()->actingAs($this->admin)
            ->post(route('admin.marketplace.submissions.approve', ['id' => $this->submission->id]), [
                'review_notes' => 'Trying to approve again',
            ]);

        $response->assertStatus(400);
    });

    it('can run security scan on submission', function () {
        Storage::fake('extensions');

        Event::fake();

        $response = test()->actingAs($this->admin)
            ->post(route('admin.marketplace.submissions.security_scan', ['id' => $this->submission->id]));

        $response->assertOK();

        Event::assertDispatched('marketplace.submission.security_scan.before');
        Event::assertDispatched('marketplace.submission.security_scan.after');
    });

    it('can get security scan results', function () {
        $this->submission->update([
            'security_scan_results' => [
                'passed' => true,
                'issues' => [],
                'scanned_at' => now()->toDateTimeString(),
            ],
        ]);

        $response = test()->actingAs($this->admin)
            ->get(route('admin.marketplace.submissions.security_scan_results', ['id' => $this->submission->id]));

        $response->assertOK()
            ->assertJson([
                'data' => [
                    'passed' => true,
                    'issues' => [],
                ],
            ]);
    });

    it('can get pending submissions count', function () {
        // Create additional pending submissions
        ExtensionSubmission::factory()->count(3)->create([
            'submitted_by' => $this->developer->id,
            'status' => 'pending',
        ]);

        $response = test()->actingAs($this->admin)
            ->get(route('admin.marketplace.submissions.pending_count'));

        $response->assertOK()
            ->assertJson([
                'data' => [
                    'count' => 4, // 1 from beforeEach + 3 new
                ],
            ]);
    });

    it('can mass approve multiple submissions', function () {
        Event::fake();

        $submission2 = ExtensionSubmission::factory()->create([
            'submitted_by' => $this->developer->id,
            'status' => 'pending',
        ]);

        $submission3 = ExtensionSubmission::factory()->create([
            'submitted_by' => $this->developer->id,
            'status' => 'pending',
        ]);

        $response = test()->actingAs($this->admin)
            ->post(route('admin.marketplace.submissions.mass_approve'), [
                'indices' => [$this->submission->id, $submission2->id, $submission3->id],
            ]);

        $response->assertOK();

        $this->submission->refresh();
        $submission2->refresh();
        $submission3->refresh();

        expect($this->submission->status)->toBe('approved')
            ->and($submission2->status)->toBe('approved')
            ->and($submission3->status)->toBe('approved');
    });

    it('can mass reject multiple submissions', function () {
        Event::fake();

        $submission2 = ExtensionSubmission::factory()->create([
            'submitted_by' => $this->developer->id,
            'status' => 'pending',
        ]);

        $submission3 = ExtensionSubmission::factory()->create([
            'submitted_by' => $this->developer->id,
            'status' => 'pending',
        ]);

        $response = test()->actingAs($this->admin)
            ->post(route('admin.marketplace.submissions.mass_reject'), [
                'indices' => [$this->submission->id, $submission2->id, $submission3->id],
                'review_notes' => 'Mass rejection for quality issues',
            ]);

        $response->assertOK();

        $this->submission->refresh();
        $submission2->refresh();
        $submission3->refresh();

        expect($this->submission->status)->toBe('rejected')
            ->and($submission2->status)->toBe('rejected')
            ->and($submission3->status)->toBe('rejected');
    });

    it('requires authentication to access admin submissions', function () {
        $response = test()->get(route('admin.marketplace.submissions.index'));

        $response->assertRedirect(route('admin.session.create'));
    });

    it('skips already reviewed submissions in mass operations', function () {
        // Create one already approved submission
        $approvedSubmission = ExtensionSubmission::factory()->create([
            'submitted_by' => $this->developer->id,
            'status' => 'approved',
            'reviewed_by' => $this->admin->id,
            'reviewed_at' => now(),
        ]);

        $pendingSubmission = ExtensionSubmission::factory()->create([
            'submitted_by' => $this->developer->id,
            'status' => 'pending',
        ]);

        $response = test()->actingAs($this->admin)
            ->post(route('admin.marketplace.submissions.mass_approve'), [
                'indices' => [$approvedSubmission->id, $pendingSubmission->id],
            ]);

        $response->assertOK();

        // Only pending submission should be processed
        $pendingSubmission->refresh();
        expect($pendingSubmission->status)->toBe('approved');
    });
});

/**
 * Admin Category Management Tests
 */
describe('Admin Category Management Flow', function () {
    beforeEach(function () {
        $this->admin = getDefaultAdmin();
    });

    it('can view categories index page', function () {
        $response = test()->actingAs($this->admin)
            ->get(route('admin.marketplace.categories.index'));

        $response->assertOK();
    });

    it('can view category create form', function () {
        $response = test()->actingAs($this->admin)
            ->get(route('admin.marketplace.categories.create'));

        $response->assertOK();
    });

    it('can create a new category', function () {
        Event::fake();

        $categoryData = [
            'name' => 'CRM Integrations',
            'slug' => 'crm-integrations',
            'description' => 'Integration extensions for popular CRM platforms',
            'icon' => 'icon-integration',
            'sort_order' => 1,
        ];

        $response = test()->actingAs($this->admin)
            ->post(route('admin.marketplace.categories.store'), $categoryData);

        $response->assertOK();

        $category = ExtensionCategory::where('slug', 'crm-integrations')->first();
        expect($category)->not->toBeNull()
            ->and($category->name)->toBe('CRM Integrations')
            ->and($category->description)->toBe('Integration extensions for popular CRM platforms');

        Event::assertDispatched('marketplace.category.create.before');
        Event::assertDispatched('marketplace.category.create.after');
    });

    it('validates category data on creation', function () {
        $invalidData = [
            'name' => '', // Required
            'slug' => 'invalid slug with spaces',
        ];

        $response = test()->actingAs($this->admin)
            ->post(route('admin.marketplace.categories.store'), $invalidData);

        $response->assertSessionHasErrors(['name']);
    });

    it('prevents duplicate category slug', function () {
        ExtensionCategory::factory()->create([
            'slug' => 'existing-category',
        ]);

        $categoryData = [
            'name' => 'Another Category',
            'slug' => 'existing-category',
        ];

        $response = test()->actingAs($this->admin)
            ->post(route('admin.marketplace.categories.store'), $categoryData);

        $response->assertSessionHasErrors(['slug']);
    });

    it('can view category details', function () {
        $category = ExtensionCategory::factory()->create([
            'name' => 'Test Category',
        ]);

        $response = test()->actingAs($this->admin)
            ->get(route('admin.marketplace.categories.show', ['id' => $category->id]));

        $response->assertOK()
            ->assertSee('Test Category');
    });

    it('can view category edit form', function () {
        $category = ExtensionCategory::factory()->create();

        $response = test()->actingAs($this->admin)
            ->get(route('admin.marketplace.categories.edit', ['id' => $category->id]));

        $response->assertOK();
    });

    it('can update category', function () {
        Event::fake();

        $category = ExtensionCategory::factory()->create([
            'name' => 'Original Name',
            'slug' => 'original-slug',
        ]);

        $updateData = [
            'name' => 'Updated Name',
            'slug' => 'updated-slug',
            'description' => 'Updated description',
        ];

        $response = test()->actingAs($this->admin)
            ->put(route('admin.marketplace.categories.update', ['id' => $category->id]), $updateData);

        $response->assertOK();

        $category->refresh();
        expect($category->name)->toBe('Updated Name')
            ->and($category->slug)->toBe('updated-slug')
            ->and($category->description)->toBe('Updated description');

        Event::assertDispatched('marketplace.category.update.before');
        Event::assertDispatched('marketplace.category.update.after');
    });

    it('can create nested categories', function () {
        $parentCategory = ExtensionCategory::factory()->create([
            'name' => 'Parent Category',
        ]);

        $childData = [
            'name' => 'Child Category',
            'slug' => 'child-category',
            'parent_id' => $parentCategory->id,
        ];

        $response = test()->actingAs($this->admin)
            ->post(route('admin.marketplace.categories.store'), $childData);

        $response->assertOK();

        $childCategory = ExtensionCategory::where('slug', 'child-category')->first();
        expect($childCategory->parent_id)->toBe($parentCategory->id);
    });

    it('prevents circular parent reference', function () {
        $category = ExtensionCategory::factory()->create();

        $updateData = [
            'name' => $category->name,
            'slug' => $category->slug,
            'parent_id' => $category->id, // Setting self as parent
        ];

        $response = test()->actingAs($this->admin)
            ->put(route('admin.marketplace.categories.update', ['id' => $category->id]), $updateData);

        $response->assertStatus(422);
    });

    it('can delete category without extensions', function () {
        Event::fake();

        $category = ExtensionCategory::factory()->create();

        $response = test()->actingAs($this->admin)
            ->delete(route('admin.marketplace.categories.destroy', ['id' => $category->id]));

        $response->assertOK();

        expect(ExtensionCategory::find($category->id))->toBeNull();

        Event::assertDispatched('marketplace.category.delete.before');
        Event::assertDispatched('marketplace.category.delete.after');
    });

    it('prevents deleting category with extensions', function () {
        $category = ExtensionCategory::factory()->create();

        Extension::factory()->create([
            'category_id' => $category->id,
        ]);

        $response = test()->actingAs($this->admin)
            ->delete(route('admin.marketplace.categories.destroy', ['id' => $category->id]));

        $response->assertStatus(422);

        expect(ExtensionCategory::find($category->id))->not->toBeNull();
    });

    it('prevents deleting category with children', function () {
        $parentCategory = ExtensionCategory::factory()->create();

        ExtensionCategory::factory()->create([
            'parent_id' => $parentCategory->id,
        ]);

        $response = test()->actingAs($this->admin)
            ->delete(route('admin.marketplace.categories.destroy', ['id' => $parentCategory->id]));

        $response->assertStatus(422);
    });

    it('can reorder categories', function () {
        Event::fake();

        $category1 = ExtensionCategory::factory()->create(['sort_order' => 1]);
        $category2 = ExtensionCategory::factory()->create(['sort_order' => 2]);
        $category3 = ExtensionCategory::factory()->create(['sort_order' => 3]);

        $reorderData = [
            'categories' => [
                ['id' => $category3->id, 'sort_order' => 1, 'parent_id' => null],
                ['id' => $category1->id, 'sort_order' => 2, 'parent_id' => null],
                ['id' => $category2->id, 'sort_order' => 3, 'parent_id' => null],
            ],
        ];

        $response = test()->actingAs($this->admin)
            ->post(route('admin.marketplace.categories.reorder'), $reorderData);

        $response->assertOK();

        $category1->refresh();
        $category2->refresh();
        $category3->refresh();

        expect($category3->sort_order)->toBe(1)
            ->and($category1->sort_order)->toBe(2)
            ->and($category2->sort_order)->toBe(3);

        Event::assertDispatched('marketplace.category.reorder.before');
        Event::assertDispatched('marketplace.category.reorder.after');
    });

    it('can get category tree data', function () {
        $parentCategory = ExtensionCategory::factory()->create([
            'name' => 'Parent',
        ]);

        ExtensionCategory::factory()->create([
            'name' => 'Child',
            'parent_id' => $parentCategory->id,
        ]);

        $response = test()->actingAs($this->admin)
            ->get(route('admin.marketplace.categories.tree_data'));

        $response->assertOK()
            ->assertJsonStructure(['data']);
    });

    it('can mass delete categories', function () {
        $category1 = ExtensionCategory::factory()->create();
        $category2 = ExtensionCategory::factory()->create();
        $category3 = ExtensionCategory::factory()->create();

        $response = test()->actingAs($this->admin)
            ->post(route('admin.marketplace.categories.mass_destroy'), [
                'indices' => [$category1->id, $category2->id, $category3->id],
            ]);

        $response->assertOK();

        expect(ExtensionCategory::find($category1->id))->toBeNull()
            ->and(ExtensionCategory::find($category2->id))->toBeNull()
            ->and(ExtensionCategory::find($category3->id))->toBeNull();
    });

    it('skips categories with extensions in mass delete', function () {
        $categoryWithExtension = ExtensionCategory::factory()->create();
        Extension::factory()->create(['category_id' => $categoryWithExtension->id]);

        $emptyCategory = ExtensionCategory::factory()->create();

        $response = test()->actingAs($this->admin)
            ->post(route('admin.marketplace.categories.mass_destroy'), [
                'indices' => [$categoryWithExtension->id, $emptyCategory->id],
            ]);

        $response->assertOK();

        // Category with extension should still exist
        expect(ExtensionCategory::find($categoryWithExtension->id))->not->toBeNull();
        // Empty category should be deleted
        expect(ExtensionCategory::find($emptyCategory->id))->toBeNull();
    });

    it('requires authentication to manage categories', function () {
        $response = test()->get(route('admin.marketplace.categories.index'));

        $response->assertRedirect(route('admin.session.create'));
    });
});

/**
 * Admin Revenue Management Tests
 */
describe('Admin Revenue Management Flow', function () {
    beforeEach(function () {
        $this->admin = getDefaultAdmin();

        $this->seller = User::factory()->create([
            'is_developer' => true,
            'developer_status' => 'approved',
        ]);

        $this->buyer = User::factory()->create();

        $this->extension = Extension::factory()->create([
            'author_id' => $this->seller->id,
            'price' => 99.99,
            'status' => 'approved',
        ]);
    });

    it('can view revenue dashboard', function () {
        $response = test()->actingAs($this->admin)
            ->get(route('admin.marketplace.revenue.index'));

        $response->assertOK();
    });

    it('can view revenue statistics', function () {
        ExtensionTransaction::factory()->count(3)->create([
            'extension_id' => $this->extension->id,
            'user_id' => $this->buyer->id,
            'status' => 'completed',
            'amount' => 99.99,
            'platform_fee' => 29.997,
            'developer_share' => 69.993,
        ]);

        $response = test()->actingAs($this->admin)
            ->get(route('admin.marketplace.revenue.statistics'));

        $response->assertOK()
            ->assertJsonStructure([
                'data' => [
                    'total_revenue',
                    'platform_revenue',
                    'seller_revenue',
                    'total_transactions',
                ],
            ]);
    });

    it('can filter statistics by date range', function () {
        $response = test()->actingAs($this->admin)
            ->get(route('admin.marketplace.revenue.statistics', [
                'start_date' => now()->subDays(30)->format('Y-m-d'),
                'end_date' => now()->format('Y-m-d'),
            ]));

        $response->assertOK();
    });

    it('can view transactions list', function () {
        ExtensionTransaction::factory()->count(5)->create([
            'extension_id' => $this->extension->id,
            'user_id' => $this->buyer->id,
            'status' => 'completed',
        ]);

        $response = test()->actingAs($this->admin)
            ->get(route('admin.marketplace.revenue.transactions'));

        $response->assertOK();
    });

    it('can view individual transaction details', function () {
        $transaction = ExtensionTransaction::factory()->create([
            'extension_id' => $this->extension->id,
            'user_id' => $this->buyer->id,
            'status' => 'completed',
            'amount' => 99.99,
        ]);

        $response = test()->actingAs($this->admin)
            ->get(route('admin.marketplace.revenue.transactions.show', ['id' => $transaction->id]));

        $response->assertOK();
    });

    it('can generate platform revenue report', function () {
        ExtensionTransaction::factory()->count(3)->create([
            'extension_id' => $this->extension->id,
            'status' => 'completed',
            'amount' => 99.99,
        ]);

        $response = test()->actingAs($this->admin)
            ->get(route('admin.marketplace.revenue.reports.platform'));

        $response->assertOK()
            ->assertJsonStructure(['data', 'message']);
    });

    it('can generate seller-specific revenue report', function () {
        ExtensionTransaction::factory()->count(3)->create([
            'extension_id' => $this->extension->id,
            'status' => 'completed',
        ]);

        $response = test()->actingAs($this->admin)
            ->get(route('admin.marketplace.revenue.reports.seller', ['seller_id' => $this->seller->id]));

        $response->assertOK()
            ->assertJsonStructure(['data', 'message']);
    });

    it('can generate extension-specific revenue report', function () {
        ExtensionTransaction::factory()->count(3)->create([
            'extension_id' => $this->extension->id,
            'status' => 'completed',
        ]);

        $response = test()->actingAs($this->admin)
            ->get(route('admin.marketplace.revenue.reports.extension', ['extension_id' => $this->extension->id]));

        $response->assertOK()
            ->assertJsonStructure(['data', 'message']);
    });

    it('can process refund for transaction', function () {
        Event::fake();

        $transaction = ExtensionTransaction::factory()->create([
            'extension_id' => $this->extension->id,
            'user_id' => $this->buyer->id,
            'status' => 'completed',
            'amount' => 99.99,
        ]);

        $response = test()->actingAs($this->admin)
            ->post(route('admin.marketplace.revenue.transactions.refund', ['id' => $transaction->id]), [
                'reason' => 'Customer requested refund',
            ]);

        $response->assertOK();

        Event::assertDispatched('marketplace.transaction.refund.before');
        Event::assertDispatched('marketplace.transaction.refund.after');
    });

    it('can get top sellers', function () {
        ExtensionTransaction::factory()->count(5)->create([
            'extension_id' => $this->extension->id,
            'status' => 'completed',
            'amount' => 99.99,
        ]);

        $response = test()->actingAs($this->admin)
            ->get(route('admin.marketplace.revenue.top_sellers', ['limit' => 10]));

        $response->assertOK()
            ->assertJsonStructure(['data']);
    });

    it('can get top extensions by revenue', function () {
        ExtensionTransaction::factory()->count(5)->create([
            'extension_id' => $this->extension->id,
            'status' => 'completed',
            'amount' => 99.99,
        ]);

        $response = test()->actingAs($this->admin)
            ->get(route('admin.marketplace.revenue.top_extensions', ['limit' => 10]));

        $response->assertOK()
            ->assertJsonStructure(['data']);
    });

    it('can update revenue sharing settings', function () {
        Event::fake();

        $response = test()->actingAs($this->admin)
            ->put(route('admin.marketplace.revenue.settings.update'), [
                'platform_fee_percentage' => 25,
            ]);

        $response->assertOK();

        Event::assertDispatched('marketplace.revenue.settings.update.before');
        Event::assertDispatched('marketplace.revenue.settings.update.after');
    });

    it('validates platform fee percentage', function () {
        $response = test()->actingAs($this->admin)
            ->put(route('admin.marketplace.revenue.settings.update'), [
                'platform_fee_percentage' => 150, // Invalid: over 100
            ]);

        $response->assertSessionHasErrors(['platform_fee_percentage']);
    });

    it('validates date range in reports', function () {
        $response = test()->actingAs($this->admin)
            ->get(route('admin.marketplace.revenue.reports.platform', [
                'start_date' => '2024-12-31',
                'end_date' => '2024-01-01', // End date before start date
            ]));

        $response->assertSessionHasErrors(['end_date']);
    });

    it('requires authentication to access revenue management', function () {
        $response = test()->get(route('admin.marketplace.revenue.index'));

        $response->assertRedirect(route('admin.session.create'));
    });

    it('shows zero revenue when no transactions exist', function () {
        $response = test()->actingAs($this->admin)
            ->get(route('admin.marketplace.revenue.statistics'));

        $response->assertOK()
            ->assertJson([
                'data' => [
                    'total_revenue' => 0,
                ],
            ]);
    });

    it('can filter revenue by date range', function () {
        // Create old transaction
        ExtensionTransaction::factory()->create([
            'extension_id' => $this->extension->id,
            'status' => 'completed',
            'amount' => 50.00,
            'created_at' => now()->subMonths(2),
        ]);

        // Create recent transaction
        ExtensionTransaction::factory()->create([
            'extension_id' => $this->extension->id,
            'status' => 'completed',
            'amount' => 100.00,
            'created_at' => now()->subDays(5),
        ]);

        $response = test()->actingAs($this->admin)
            ->get(route('admin.marketplace.revenue.statistics', [
                'start_date' => now()->subDays(7)->format('Y-m-d'),
                'end_date' => now()->format('Y-m-d'),
            ]));

        $response->assertOK();
    });
});

/**
 * Complete Admin Journey Tests
 */
describe('Complete Admin Journey', function () {
    it('can complete full admin workflow: review submission, manage category, view revenue', function () {
        Storage::fake('extensions');
        Event::fake();

        $admin = getDefaultAdmin();

        // Setup
        $developer = User::factory()->create([
            'is_developer' => true,
            'developer_status' => 'approved',
        ]);

        $buyer = User::factory()->create();

        // Step 1: Create and manage category
        $categoryData = [
            'name' => 'Payment Integrations',
            'slug' => 'payment-integrations',
            'description' => 'Extensions for payment processing',
        ];

        $categoryResponse = test()->actingAs($admin)
            ->post(route('admin.marketplace.categories.store'), $categoryData);

        $categoryResponse->assertOK();

        $category = ExtensionCategory::where('slug', 'payment-integrations')->first();
        expect($category)->not->toBeNull();

        // Step 2: Developer submits extension
        $extension = Extension::factory()->create([
            'author_id' => $developer->id,
            'category_id' => $category->id,
            'status' => 'draft',
            'price' => 149.99,
        ]);

        $version = ExtensionVersion::factory()->create([
            'extension_id' => $extension->id,
            'status' => 'draft',
        ]);

        $submission = ExtensionSubmission::create([
            'extension_id' => $extension->id,
            'version_id' => $version->id,
            'submitted_by' => $developer->id,
            'status' => 'pending',
            'submitted_at' => now(),
        ]);

        // Step 3: Admin reviews submissions
        $submissionsResponse = test()->actingAs($admin)
            ->get(route('admin.marketplace.submissions.index'));

        $submissionsResponse->assertOK();

        // Step 4: Admin approves submission
        $approveResponse = test()->actingAs($admin)
            ->post(route('admin.marketplace.submissions.approve', ['id' => $submission->id]), [
                'review_notes' => 'Excellent payment integration extension',
            ]);

        $approveResponse->assertOK();

        $submission->refresh();
        expect($submission->status)->toBe('approved');

        // Step 5: Mark extension and version as approved
        $extension->update(['status' => 'approved']);
        $version->update(['status' => 'approved']);

        // Step 6: User purchases extension
        $transaction = ExtensionTransaction::factory()->create([
            'extension_id' => $extension->id,
            'user_id' => $buyer->id,
            'status' => 'completed',
            'amount' => 149.99,
            'platform_fee' => 44.997,
            'developer_share' => 104.993,
        ]);

        // Step 7: Admin views revenue dashboard
        $revenueResponse = test()->actingAs($admin)
            ->get(route('admin.marketplace.revenue.index'));

        $revenueResponse->assertOK();

        // Step 8: Admin checks statistics
        $statsResponse = test()->actingAs($admin)
            ->get(route('admin.marketplace.revenue.statistics'));

        $statsResponse->assertOK()
            ->assertJsonStructure([
                'data' => [
                    'total_revenue',
                    'platform_revenue',
                    'seller_revenue',
                ],
            ]);

        // Verify all events were dispatched
        Event::assertDispatched('marketplace.category.create.after');
        Event::assertDispatched('marketplace.submission.approve.after');
    });
});
