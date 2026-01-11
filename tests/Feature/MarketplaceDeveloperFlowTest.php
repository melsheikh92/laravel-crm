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
 * Developer Registration Flow Tests
 */
describe('Developer Registration Flow', function () {
    beforeEach(function () {
        $this->user = User::factory()->create([
            'is_developer' => false,
        ]);
    });

    it('can view developer registration form', function () {
        $response = test()->actingAs($this->user)
            ->get(route('marketplace.developer-registration.create'));

        $response->assertOK()
            ->assertSee('Developer Registration');
    });

    it('can register as developer with valid data', function () {
        Event::fake();

        $registrationData = [
            'bio' => 'I am a passionate developer with over 10 years of experience in building Laravel applications.',
            'company' => 'Tech Solutions Inc',
            'website' => 'https://example.com',
            'support_email' => 'support@example.com',
            'github_url' => 'https://github.com/developer',
            'twitter_url' => 'https://twitter.com/developer',
            'linkedin_url' => 'https://linkedin.com/in/developer',
            'terms_accepted' => true,
        ];

        $response = test()->actingAs($this->user)
            ->post(route('marketplace.developer-registration.store'), $registrationData);

        $response->assertStatus(302)
            ->assertSessionHas('success');

        // Verify user is marked as developer
        $this->user->refresh();
        expect($this->user->is_developer)->toBeTrue()
            ->and($this->user->developer_status)->toBe('pending')
            ->and($this->user->developer_bio)->toBe($registrationData['bio'])
            ->and($this->user->developer_company)->toBe($registrationData['company'])
            ->and($this->user->developer_support_email)->toBe($registrationData['support_email']);
    });

    it('validates developer registration data', function () {
        $invalidData = [
            'bio' => 'Too short', // Minimum 50 characters
            'support_email' => 'invalid-email',
            'website' => 'not-a-url',
        ];

        $response = test()->actingAs($this->user)
            ->post(route('marketplace.developer-registration.store'), $invalidData);

        $response->assertSessionHasErrors(['bio', 'support_email', 'website', 'terms_accepted']);
    });

    it('prevents duplicate developer registration', function () {
        // Register user as developer
        $this->user->update([
            'is_developer' => true,
            'developer_status' => 'approved',
        ]);

        $registrationData = [
            'bio' => 'I am a passionate developer with over 10 years of experience in building Laravel applications.',
            'support_email' => 'support@example.com',
            'terms_accepted' => true,
        ];

        $response = test()->actingAs($this->user)
            ->post(route('marketplace.developer-registration.store'), $registrationData);

        $response->assertStatus(302)
            ->assertSessionHas('info');
    });

    it('redirects already approved developers to dashboard', function () {
        $this->user->update([
            'is_developer' => true,
            'developer_status' => 'approved',
        ]);

        $response = test()->actingAs($this->user)
            ->get(route('marketplace.developer-registration.create'));

        $response->assertRedirect(route('developer.marketplace.dashboard'));
    });

    it('shows pending message for pending applications', function () {
        $this->user->update([
            'is_developer' => true,
            'developer_status' => 'pending',
        ]);

        $response = test()->actingAs($this->user)
            ->get(route('marketplace.developer-registration.create'));

        $response->assertRedirect()
            ->assertSessionHas('info');
    });

    it('can view registration status', function () {
        $this->user->update([
            'is_developer' => true,
            'developer_status' => 'pending',
        ]);

        $response = test()->actingAs($this->user)
            ->get(route('marketplace.developer-registration.status'));

        $response->assertOK()
            ->assertJson([
                'success' => true,
                'data' => [
                    'is_developer' => true,
                    'developer_status' => 'pending',
                    'is_approved' => false,
                    'is_pending' => true,
                    'is_rejected' => false,
                ],
            ]);
    });

    it('can update developer profile', function () {
        $this->user->update([
            'is_developer' => true,
            'developer_status' => 'approved',
        ]);

        $updateData = [
            'bio' => 'Updated bio with sufficient length to meet the minimum requirement for developer profile.',
            'company' => 'Updated Company',
            'website' => 'https://updated-example.com',
        ];

        $response = test()->actingAs($this->user)
            ->put(route('marketplace.developer-registration.update'), $updateData);

        $response->assertStatus(302)
            ->assertSessionHas('success');

        $this->user->refresh();
        expect($this->user->developer_bio)->toBe($updateData['bio'])
            ->and($this->user->developer_company)->toBe($updateData['company'])
            ->and($this->user->developer_website)->toBe($updateData['website']);
    });

    it('requires authentication for developer registration', function () {
        $response = test()->get(route('marketplace.developer-registration.create'));

        $response->assertRedirect(route('admin.session.create'));
    });
});

/**
 * Developer Dashboard Tests
 */
describe('Developer Dashboard', function () {
    beforeEach(function () {
        $this->developer = User::factory()->create([
            'is_developer' => true,
            'developer_status' => 'approved',
        ]);
    });

    it('can access developer dashboard', function () {
        $response = test()->actingAs($this->developer)
            ->get(route('developer.marketplace.dashboard'));

        $response->assertOK()
            ->assertSee('Dashboard');
    });

    it('can view developer statistics', function () {
        // Create some extensions
        Extension::factory()->count(3)->create([
            'author_id' => $this->developer->id,
            'status' => 'approved',
        ]);

        $response = test()->actingAs($this->developer)
            ->get(route('developer.marketplace.dashboard.statistics'));

        $response->assertOK()
            ->assertJsonStructure([
                'total_extensions',
                'approved_extensions',
                'total_downloads',
                'average_rating',
            ]);
    });

    it('prevents non-developers from accessing dashboard', function () {
        $regularUser = User::factory()->create([
            'is_developer' => false,
        ]);

        $response = test()->actingAs($regularUser)
            ->get(route('developer.marketplace.dashboard'));

        $response->assertStatus(403);
    });
});

/**
 * Submit Extension Flow Tests
 */
describe('Submit Extension Flow', function () {
    beforeEach(function () {
        Storage::fake('extensions');

        $this->developer = User::factory()->create([
            'is_developer' => true,
            'developer_status' => 'approved',
        ]);

        $this->category = ExtensionCategory::factory()->create([
            'name' => 'Integrations',
            'slug' => 'integrations',
        ]);
    });

    it('can view extension creation form', function () {
        $response = test()->actingAs($this->developer)
            ->get(route('developer.marketplace.extensions.create'));

        $response->assertOK()
            ->assertSee('Create Extension');
    });

    it('can create new extension', function () {
        Event::fake();

        $extensionData = [
            'name' => 'My Awesome Extension',
            'slug' => 'my-awesome-extension',
            'description' => 'This is a comprehensive description of my awesome extension.',
            'short_description' => 'Short description',
            'type' => 'plugin',
            'category_id' => $this->category->id,
            'price' => 49.99,
            'tags' => ['crm', 'integration'],
            'features' => ['Feature 1', 'Feature 2'],
        ];

        $response = test()->actingAs($this->developer)
            ->post(route('developer.marketplace.extensions.store'), $extensionData);

        $response->assertStatus(302)
            ->assertSessionHas('success');

        // Verify extension is created
        $extension = Extension::where('slug', 'my-awesome-extension')->first();
        expect($extension)->not->toBeNull()
            ->and($extension->author_id)->toBe($this->developer->id)
            ->and($extension->name)->toBe($extensionData['name'])
            ->and($extension->status)->toBe('draft')
            ->and($extension->price)->toBe('49.99');
    });

    it('validates extension data', function () {
        $invalidData = [
            'name' => '', // Required
            'type' => 'invalid-type',
            'price' => -10, // Must be positive
        ];

        $response = test()->actingAs($this->developer)
            ->post(route('developer.marketplace.extensions.store'), $invalidData);

        $response->assertSessionHasErrors(['name', 'type', 'price']);
    });

    it('can view extension details', function () {
        $extension = Extension::factory()->create([
            'author_id' => $this->developer->id,
            'name' => 'Test Extension',
        ]);

        $response = test()->actingAs($this->developer)
            ->get(route('developer.marketplace.extensions.show', ['id' => $extension->id]));

        $response->assertOK()
            ->assertSee('Test Extension');
    });

    it('can update own extension', function () {
        $extension = Extension::factory()->create([
            'author_id' => $this->developer->id,
            'name' => 'Original Name',
        ]);

        $updateData = [
            'name' => 'Updated Name',
            'description' => 'Updated description',
            'type' => $extension->type,
            'category_id' => $extension->category_id,
        ];

        $response = test()->actingAs($this->developer)
            ->put(route('developer.marketplace.extensions.update', ['id' => $extension->id]), $updateData);

        $response->assertStatus(302);

        $extension->refresh();
        expect($extension->name)->toBe('Updated Name');
    });

    it('cannot update other developers extension', function () {
        $otherDeveloper = User::factory()->create([
            'is_developer' => true,
            'developer_status' => 'approved',
        ]);

        $extension = Extension::factory()->create([
            'author_id' => $otherDeveloper->id,
        ]);

        $updateData = [
            'name' => 'Hacked Name',
        ];

        $response = test()->actingAs($this->developer)
            ->put(route('developer.marketplace.extensions.update', ['id' => $extension->id]), $updateData);

        $response->assertStatus(403);
    });

    it('can delete own extension', function () {
        $extension = Extension::factory()->create([
            'author_id' => $this->developer->id,
            'status' => 'draft',
        ]);

        $response = test()->actingAs($this->developer)
            ->delete(route('developer.marketplace.extensions.destroy', ['id' => $extension->id]));

        $response->assertStatus(302);

        expect(Extension::find($extension->id))->toBeNull();
    });

    it('can view list of own extensions', function () {
        Extension::factory()->count(3)->create([
            'author_id' => $this->developer->id,
        ]);

        // Create extension by another developer (should not be visible)
        Extension::factory()->create([
            'author_id' => User::factory()->create([
                'is_developer' => true,
                'developer_status' => 'approved',
            ])->id,
        ]);

        $response = test()->actingAs($this->developer)
            ->get(route('developer.marketplace.extensions.index'));

        $response->assertOK();
        // Should only see own extensions (3)
        expect($this->developer->developedExtensions()->count())->toBe(3);
    });

    it('can view extension analytics', function () {
        $extension = Extension::factory()->create([
            'author_id' => $this->developer->id,
            'downloads_count' => 100,
        ]);

        $response = test()->actingAs($this->developer)
            ->get(route('developer.marketplace.extensions.analytics', ['id' => $extension->id]));

        $response->assertOK()
            ->assertJsonStructure([
                'downloads',
                'revenue',
                'reviews_count',
            ]);
    });
});

/**
 * Manage Versions Flow Tests
 */
describe('Manage Versions Flow', function () {
    beforeEach(function () {
        Storage::fake('extensions');

        $this->developer = User::factory()->create([
            'is_developer' => true,
            'developer_status' => 'approved',
        ]);

        $this->extension = Extension::factory()->create([
            'author_id' => $this->developer->id,
            'status' => 'draft',
        ]);
    });

    it('can view versions list for extension', function () {
        ExtensionVersion::factory()->count(2)->create([
            'extension_id' => $this->extension->id,
        ]);

        $response = test()->actingAs($this->developer)
            ->get(route('developer.marketplace.versions.index', ['extension_id' => $this->extension->id]));

        $response->assertOK()
            ->assertSee('Versions');
    });

    it('can create new version', function () {
        Event::fake();

        $versionData = [
            'version' => '1.0.0',
            'release_notes' => 'Initial release with amazing features.',
            'laravel_version' => '^10.0',
            'php_version' => '^8.1',
            'requirements' => ['ext-json', 'ext-mbstring'],
        ];

        $response = test()->actingAs($this->developer)
            ->post(route('developer.marketplace.versions.store', ['extension_id' => $this->extension->id]), $versionData);

        $response->assertStatus(302)
            ->assertSessionHas('success');

        // Verify version is created
        $version = ExtensionVersion::where('extension_id', $this->extension->id)
            ->where('version', '1.0.0')
            ->first();

        expect($version)->not->toBeNull()
            ->and($version->status)->toBe('draft')
            ->and($version->release_notes)->toBe($versionData['release_notes']);
    });

    it('validates version data', function () {
        $invalidData = [
            'version' => 'invalid-version',
            'release_notes' => '', // Required
        ];

        $response = test()->actingAs($this->developer)
            ->post(route('developer.marketplace.versions.store', ['extension_id' => $this->extension->id]), $invalidData);

        $response->assertSessionHasErrors(['version', 'release_notes']);
    });

    it('can update version details', function () {
        $version = ExtensionVersion::factory()->create([
            'extension_id' => $this->extension->id,
            'version' => '1.0.0',
            'status' => 'draft',
        ]);

        $updateData = [
            'release_notes' => 'Updated release notes with more details.',
            'laravel_version' => '^10.0',
            'php_version' => '^8.2',
        ];

        $response = test()->actingAs($this->developer)
            ->put(route('developer.marketplace.versions.update', ['id' => $version->id]), $updateData);

        $response->assertStatus(302);

        $version->refresh();
        expect($version->release_notes)->toBe($updateData['release_notes'])
            ->and($version->php_version)->toBe($updateData['php_version']);
    });

    it('cannot update version of other developers extension', function () {
        $otherExtension = Extension::factory()->create([
            'author_id' => User::factory()->create([
                'is_developer' => true,
                'developer_status' => 'approved',
            ])->id,
        ]);

        $version = ExtensionVersion::factory()->create([
            'extension_id' => $otherExtension->id,
        ]);

        $updateData = [
            'release_notes' => 'Hacked notes',
        ];

        $response = test()->actingAs($this->developer)
            ->put(route('developer.marketplace.versions.update', ['id' => $version->id]), $updateData);

        $response->assertStatus(403);
    });

    it('can delete draft version', function () {
        $version = ExtensionVersion::factory()->create([
            'extension_id' => $this->extension->id,
            'status' => 'draft',
        ]);

        $response = test()->actingAs($this->developer)
            ->delete(route('developer.marketplace.versions.destroy', ['id' => $version->id]));

        $response->assertStatus(302);

        expect(ExtensionVersion::find($version->id))->toBeNull();
    });

    it('can submit version for review', function () {
        $version = ExtensionVersion::factory()->create([
            'extension_id' => $this->extension->id,
            'version' => '1.0.0',
            'status' => 'draft',
        ]);

        Event::fake();

        $response = test()->actingAs($this->developer)
            ->post(route('developer.marketplace.submissions.submit', [
                'extension_id' => $this->extension->id,
                'version_id' => $version->id,
            ]));

        $response->assertStatus(302)
            ->assertSessionHas('success');

        // Verify submission is created
        $submission = ExtensionSubmission::where('extension_id', $this->extension->id)
            ->where('version_id', $version->id)
            ->first();

        expect($submission)->not->toBeNull()
            ->and($submission->status)->toBe('pending')
            ->and($submission->submitted_by)->toBe($this->developer->id);
    });

    it('can view version details', function () {
        $version = ExtensionVersion::factory()->create([
            'extension_id' => $this->extension->id,
        ]);

        $response = test()->actingAs($this->developer)
            ->get(route('developer.marketplace.versions.show', ['id' => $version->id]));

        $response->assertOK();
    });
});

/**
 * View Earnings Flow Tests
 */
describe('View Earnings Flow', function () {
    beforeEach(function () {
        $this->developer = User::factory()->create([
            'is_developer' => true,
            'developer_status' => 'approved',
        ]);

        $this->extension = Extension::factory()->create([
            'author_id' => $this->developer->id,
            'price' => 99.99,
            'status' => 'approved',
        ]);

        $this->buyer = User::factory()->create();
    });

    it('can view earnings dashboard', function () {
        $response = test()->actingAs($this->developer)
            ->get(route('developer.marketplace.earnings.index'));

        $response->assertOK()
            ->assertSee('Earnings');
    });

    it('can view earnings statistics', function () {
        // Create some transactions
        ExtensionTransaction::factory()->count(3)->create([
            'extension_id' => $this->extension->id,
            'user_id' => $this->buyer->id,
            'status' => 'completed',
            'amount' => 99.99,
            'developer_share' => 69.99,
        ]);

        $response = test()->actingAs($this->developer)
            ->get(route('developer.marketplace.earnings.statistics'));

        $response->assertOK()
            ->assertJsonStructure([
                'total_revenue',
                'developer_share',
                'platform_fee',
                'pending_payout',
            ]);
    });

    it('can view transactions list', function () {
        ExtensionTransaction::factory()->count(5)->create([
            'extension_id' => $this->extension->id,
            'user_id' => $this->buyer->id,
            'status' => 'completed',
        ]);

        $response = test()->actingAs($this->developer)
            ->get(route('developer.marketplace.earnings.transactions'));

        $response->assertOK()
            ->assertSee('Transactions');
    });

    it('can view individual transaction details', function () {
        $transaction = ExtensionTransaction::factory()->create([
            'extension_id' => $this->extension->id,
            'user_id' => $this->buyer->id,
            'status' => 'completed',
            'amount' => 99.99,
        ]);

        $response = test()->actingAs($this->developer)
            ->get(route('developer.marketplace.earnings.transactions.show', ['id' => $transaction->id]));

        $response->assertOK()
            ->assertSee('99.99');
    });

    it('cannot view other developers transactions', function () {
        $otherDeveloper = User::factory()->create([
            'is_developer' => true,
            'developer_status' => 'approved',
        ]);

        $otherExtension = Extension::factory()->create([
            'author_id' => $otherDeveloper->id,
        ]);

        $transaction = ExtensionTransaction::factory()->create([
            'extension_id' => $otherExtension->id,
        ]);

        $response = test()->actingAs($this->developer)
            ->get(route('developer.marketplace.earnings.transactions.show', ['id' => $transaction->id]));

        $response->assertStatus(403);
    });

    it('can view earnings by extension', function () {
        ExtensionTransaction::factory()->count(3)->create([
            'extension_id' => $this->extension->id,
            'status' => 'completed',
            'amount' => 99.99,
        ]);

        $response = test()->actingAs($this->developer)
            ->get(route('developer.marketplace.earnings.by_extension', ['extension_id' => $this->extension->id]));

        $response->assertOK()
            ->assertJsonStructure([
                'extension',
                'total_sales',
                'total_revenue',
                'developer_share',
            ]);
    });

    it('can view earnings reports', function () {
        $response = test()->actingAs($this->developer)
            ->get(route('developer.marketplace.earnings.reports'));

        $response->assertOK()
            ->assertSee('Reports');
    });

    it('can view payout history', function () {
        $response = test()->actingAs($this->developer)
            ->get(route('developer.marketplace.earnings.payout_history'));

        $response->assertOK()
            ->assertSee('Payout History');
    });

    it('can request payout when threshold is met', function () {
        // Create enough transactions to meet payout threshold
        ExtensionTransaction::factory()->count(10)->create([
            'extension_id' => $this->extension->id,
            'status' => 'completed',
            'amount' => 99.99,
            'developer_share' => 69.99,
        ]);

        Event::fake();

        $response = test()->actingAs($this->developer)
            ->post(route('developer.marketplace.earnings.request_payout'), [
                'amount' => 699.90,
            ]);

        $response->assertStatus(302)
            ->assertSessionHas('success');
    });

    it('shows correct revenue share calculation', function () {
        $transaction = ExtensionTransaction::factory()->create([
            'extension_id' => $this->extension->id,
            'user_id' => $this->buyer->id,
            'status' => 'completed',
            'amount' => 100.00,
            'platform_fee' => 30.00,
            'developer_share' => 70.00,
        ]);

        expect($transaction->developer_share)->toBe('70.00')
            ->and($transaction->platform_fee)->toBe('30.00');
    });
});

/**
 * Developer Submission Management Tests
 */
describe('Developer Submission Management', function () {
    beforeEach(function () {
        $this->developer = User::factory()->create([
            'is_developer' => true,
            'developer_status' => 'approved',
        ]);

        $this->extension = Extension::factory()->create([
            'author_id' => $this->developer->id,
        ]);

        $this->version = ExtensionVersion::factory()->create([
            'extension_id' => $this->extension->id,
            'status' => 'draft',
        ]);
    });

    it('can view submissions list', function () {
        $response = test()->actingAs($this->developer)
            ->get(route('developer.marketplace.submissions.index'));

        $response->assertOK()
            ->assertSee('Submissions');
    });

    it('can view submission details', function () {
        $submission = ExtensionSubmission::create([
            'extension_id' => $this->extension->id,
            'version_id' => $this->version->id,
            'submitted_by' => $this->developer->id,
            'status' => 'pending',
            'submitted_at' => now(),
        ]);

        $response = test()->actingAs($this->developer)
            ->get(route('developer.marketplace.submissions.show', ['id' => $submission->id]));

        $response->assertOK()
            ->assertSee('Submission');
    });

    it('can cancel pending submission', function () {
        $submission = ExtensionSubmission::create([
            'extension_id' => $this->extension->id,
            'version_id' => $this->version->id,
            'submitted_by' => $this->developer->id,
            'status' => 'pending',
            'submitted_at' => now(),
        ]);

        $response = test()->actingAs($this->developer)
            ->delete(route('developer.marketplace.submissions.cancel', ['id' => $submission->id]));

        $response->assertStatus(302);

        $submission->refresh();
        expect($submission->status)->toBe('cancelled');
    });

    it('can resubmit rejected submission', function () {
        $submission = ExtensionSubmission::create([
            'extension_id' => $this->extension->id,
            'version_id' => $this->version->id,
            'submitted_by' => $this->developer->id,
            'status' => 'rejected',
            'review_notes' => 'Please fix the security issues',
            'submitted_at' => now(),
        ]);

        Event::fake();

        $response = test()->actingAs($this->developer)
            ->post(route('developer.marketplace.submissions.resubmit', ['id' => $submission->id]));

        $response->assertStatus(302)
            ->assertSessionHas('success');

        $submission->refresh();
        expect($submission->status)->toBe('pending');
    });

    it('can view submissions by extension', function () {
        ExtensionSubmission::create([
            'extension_id' => $this->extension->id,
            'version_id' => $this->version->id,
            'submitted_by' => $this->developer->id,
            'status' => 'pending',
            'submitted_at' => now(),
        ]);

        $response = test()->actingAs($this->developer)
            ->get(route('developer.marketplace.submissions.by_extension', ['extension_id' => $this->extension->id]));

        $response->assertOK();
    });

    it('can get pending submissions count', function () {
        ExtensionSubmission::create([
            'extension_id' => $this->extension->id,
            'version_id' => $this->version->id,
            'submitted_by' => $this->developer->id,
            'status' => 'pending',
            'submitted_at' => now(),
        ]);

        $response = test()->actingAs($this->developer)
            ->get(route('developer.marketplace.submissions.pending_count'));

        $response->assertOK()
            ->assertJson(['count' => 1]);
    });
});

/**
 * Complete Developer Journey Tests
 */
describe('Complete Developer Journey', function () {
    it('can complete full developer lifecycle: register, create, submit, earn', function () {
        Storage::fake('extensions');
        Event::fake();

        // Step 1: Register as developer
        $user = User::factory()->create();

        $registrationData = [
            'bio' => 'I am a passionate Laravel developer with extensive experience in building marketplace extensions.',
            'company' => 'Developer Studios',
            'website' => 'https://devstudios.com',
            'support_email' => 'support@devstudios.com',
            'github_url' => 'https://github.com/devstudios',
            'terms_accepted' => true,
        ];

        $registrationResponse = test()->actingAs($user)
            ->post(route('marketplace.developer-registration.store'), $registrationData);

        $registrationResponse->assertStatus(302);

        $user->refresh();
        expect($user->is_developer)->toBeTrue()
            ->and($user->developer_status)->toBe('pending');

        // Admin approves developer
        $user->approveDeveloper();
        expect($user->isDeveloper())->toBeTrue();

        // Step 2: Create extension
        $category = ExtensionCategory::factory()->create();

        $extensionData = [
            'name' => 'Complete Journey Extension',
            'slug' => 'complete-journey-extension',
            'description' => 'A comprehensive extension for testing the complete developer journey.',
            'short_description' => 'Journey test extension',
            'type' => 'plugin',
            'category_id' => $category->id,
            'price' => 79.99,
        ];

        $createResponse = test()->actingAs($user)
            ->post(route('developer.marketplace.extensions.store'), $extensionData);

        $createResponse->assertStatus(302);

        $extension = Extension::where('slug', 'complete-journey-extension')->first();
        expect($extension)->not->toBeNull()
            ->and($extension->author_id)->toBe($user->id);

        // Step 3: Create version
        $versionData = [
            'version' => '1.0.0',
            'release_notes' => 'Initial release with comprehensive features and documentation.',
            'laravel_version' => '^10.0',
            'php_version' => '^8.1',
        ];

        $versionResponse = test()->actingAs($user)
            ->post(route('developer.marketplace.versions.store', ['extension_id' => $extension->id]), $versionData);

        $versionResponse->assertStatus(302);

        $version = ExtensionVersion::where('extension_id', $extension->id)->first();
        expect($version)->not->toBeNull();

        // Step 4: Submit for review
        $submitResponse = test()->actingAs($user)
            ->post(route('developer.marketplace.submissions.submit', [
                'extension_id' => $extension->id,
                'version_id' => $version->id,
            ]));

        $submitResponse->assertStatus(302);

        $submission = ExtensionSubmission::where('extension_id', $extension->id)->first();
        expect($submission)->not->toBeNull()
            ->and($submission->status)->toBe('pending');

        // Admin approves extension
        $extension->update(['status' => 'approved']);
        $version->update(['status' => 'approved']);

        // Step 5: User purchases extension
        $buyer = User::factory()->create();

        $transaction = ExtensionTransaction::factory()->create([
            'extension_id' => $extension->id,
            'user_id' => $buyer->id,
            'status' => 'completed',
            'amount' => 79.99,
            'platform_fee' => 23.997,
            'developer_share' => 55.993,
        ]);

        // Step 6: Developer views earnings
        $earningsResponse = test()->actingAs($user)
            ->get(route('developer.marketplace.earnings.by_extension', ['extension_id' => $extension->id]));

        $earningsResponse->assertOK();

        // Verify events were dispatched
        Event::assertDispatched('marketplace.extension.created');
        Event::assertDispatched('marketplace.version.created');
        Event::assertDispatched('marketplace.submission.created');
    });
});
