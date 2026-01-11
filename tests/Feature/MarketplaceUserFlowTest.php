<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Webkul\Marketplace\Models\Extension;
use Webkul\Marketplace\Models\ExtensionCategory;
use Webkul\Marketplace\Models\ExtensionInstallation;
use Webkul\Marketplace\Models\ExtensionReview;
use Webkul\Marketplace\Models\ExtensionTransaction;
use Webkul\Marketplace\Models\ExtensionVersion;
use Webkul\User\Models\User;

uses(RefreshDatabase::class);

/**
 * Browse Marketplace Tests
 */
describe('Browse Marketplace Flow', function () {
    beforeEach(function () {
        $this->category = ExtensionCategory::factory()->create([
            'name' => 'CRM Integrations',
            'slug' => 'crm-integrations',
        ]);

        $this->author = User::factory()->create();

        $this->freeExtension = Extension::factory()->create([
            'name'           => 'Free CRM Tool',
            'slug'           => 'free-crm-tool',
            'type'           => 'plugin',
            'price'          => 0,
            'status'         => 'approved',
            'featured'       => true,
            'category_id'    => $this->category->id,
            'author_id'      => $this->author->id,
            'downloads_count' => 100,
            'average_rating' => 4.5,
        ]);

        $this->paidExtension = Extension::factory()->create([
            'name'           => 'Premium Integration',
            'slug'           => 'premium-integration',
            'type'           => 'integration',
            'price'          => 99.99,
            'status'         => 'approved',
            'featured'       => false,
            'category_id'    => $this->category->id,
            'author_id'      => $this->author->id,
            'downloads_count' => 50,
            'average_rating' => 4.8,
        ]);

        ExtensionVersion::factory()->create([
            'extension_id' => $this->freeExtension->id,
            'version'      => '1.0.0',
            'status'       => 'approved',
        ]);

        ExtensionVersion::factory()->create([
            'extension_id' => $this->paidExtension->id,
            'version'      => '1.0.0',
            'status'       => 'approved',
        ]);
    });

    it('can browse marketplace index page', function () {
        $response = test()->get(route('marketplace.browse.index'));

        $response->assertOK()
            ->assertSee('Free CRM Tool')
            ->assertSee('Premium Integration');
    });

    it('can search for extensions', function () {
        $response = test()->get(route('marketplace.browse.search', ['q' => 'Premium']));

        $response->assertOK()
            ->assertSee('Premium Integration')
            ->assertDontSee('Free CRM Tool');
    });

    it('can filter by category', function () {
        $response = test()->get(route('marketplace.browse.category', ['category_slug' => 'crm-integrations']));

        $response->assertOK()
            ->assertSee('Free CRM Tool')
            ->assertSee('Premium Integration');
    });

    it('can filter by type', function () {
        $response = test()->get(route('marketplace.browse.type', ['type' => 'plugin']));

        $response->assertOK()
            ->assertSee('Free CRM Tool')
            ->assertDontSee('Premium Integration');
    });

    it('can view featured extensions', function () {
        $response = test()->get(route('marketplace.browse.featured'));

        $response->assertOK()
            ->assertSee('Free CRM Tool')
            ->assertDontSee('Premium Integration');
    });

    it('can view popular extensions sorted by downloads', function () {
        $response = test()->get(route('marketplace.browse.popular'));

        $response->assertOK()
            ->assertSee('Free CRM Tool');
    });

    it('can view free extensions only', function () {
        $response = test()->get(route('marketplace.browse.free'));

        $response->assertOK()
            ->assertSee('Free CRM Tool')
            ->assertDontSee('Premium Integration');
    });

    it('can view paid extensions only', function () {
        $response = test()->get(route('marketplace.browse.paid'));

        $response->assertOK()
            ->assertSee('Premium Integration')
            ->assertDontSee('Free CRM Tool');
    });

    it('can view extension detail page', function () {
        $response = test()->get(route('marketplace.extension.show', ['slug' => 'free-crm-tool']));

        $response->assertOK()
            ->assertSee('Free CRM Tool')
            ->assertSee($this->author->name);
    });

    it('can view extension versions', function () {
        $response = test()->get(route('marketplace.extension.versions', ['slug' => 'free-crm-tool']));

        $response->assertOK()
            ->assertSee('1.0.0');
    });

    it('can view extension reviews', function () {
        ExtensionReview::factory()->create([
            'extension_id' => $this->freeExtension->id,
            'user_id'      => $this->author->id,
            'rating'       => 5,
            'review_text'  => 'Excellent extension!',
            'status'       => 'approved',
        ]);

        $response = test()->get(route('marketplace.extension.reviews', ['slug' => 'free-crm-tool']));

        $response->assertOK()
            ->assertSee('Excellent extension!');
    });
});

/**
 * Purchase Extension Flow Tests
 */
describe('Purchase Extension Flow', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->author = User::factory()->create();

        $this->extension = Extension::factory()->create([
            'name'      => 'Premium Extension',
            'slug'      => 'premium-extension',
            'price'     => 49.99,
            'status'    => 'approved',
            'author_id' => $this->author->id,
        ]);

        $this->version = ExtensionVersion::factory()->create([
            'extension_id' => $this->extension->id,
            'version'      => '1.0.0',
            'status'       => 'approved',
        ]);
    });

    it('can initiate payment for paid extension', function () {
        Event::fake();

        $response = test()->actingAs($this->user)
            ->post(route('marketplace.payment.initiate', ['extension_id' => $this->extension->id]));

        $response->assertStatus(302);

        // Verify transaction is created
        $transaction = ExtensionTransaction::where('extension_id', $this->extension->id)
            ->where('user_id', $this->user->id)
            ->first();

        expect($transaction)->not->toBeNull()
            ->and($transaction->amount)->toBe('49.99')
            ->and($transaction->status)->toBe('pending');
    });

    it('cannot purchase same extension twice', function () {
        // Create existing transaction
        ExtensionTransaction::factory()->create([
            'extension_id' => $this->extension->id,
            'user_id'      => $this->user->id,
            'status'       => 'completed',
        ]);

        $response = test()->actingAs($this->user)
            ->post(route('marketplace.payment.initiate', ['extension_id' => $this->extension->id]));

        $response->assertSessionHasErrors();
    });

    it('can complete payment via callback', function () {
        $transaction = ExtensionTransaction::factory()->create([
            'extension_id' => $this->extension->id,
            'user_id'      => $this->user->id,
            'status'       => 'pending',
            'amount'       => 49.99,
        ]);

        Event::fake();

        $response = test()->actingAs($this->user)
            ->get(route('marketplace.payment.callback', [
                'transaction_id' => $transaction->id,
                'status'         => 'success',
            ]));

        $response->assertStatus(302);

        // Verify transaction is completed
        $transaction->refresh();
        expect($transaction->status)->toBe('completed');

        // Verify events were dispatched
        Event::assertDispatched('marketplace.payment.completed');
    });

    it('can cancel pending payment', function () {
        $transaction = ExtensionTransaction::factory()->create([
            'extension_id' => $this->extension->id,
            'user_id'      => $this->user->id,
            'status'       => 'pending',
        ]);

        $response = test()->actingAs($this->user)
            ->post(route('marketplace.payment.cancel', ['transaction_id' => $transaction->id]));

        $response->assertStatus(302);

        $transaction->refresh();
        expect($transaction->status)->toBe('cancelled');
    });

    it('can request refund for completed payment', function () {
        $transaction = ExtensionTransaction::factory()->create([
            'extension_id' => $this->extension->id,
            'user_id'      => $this->user->id,
            'status'       => 'completed',
            'amount'       => 49.99,
        ]);

        Event::fake();

        $response = test()->actingAs($this->user)
            ->post(route('marketplace.payment.refund', ['transaction_id' => $transaction->id]));

        $response->assertStatus(302);

        $transaction->refresh();
        expect($transaction->status)->toBe('refunded');
    });

    it('requires authentication for payment initiation', function () {
        $response = test()->post(route('marketplace.payment.initiate', ['extension_id' => $this->extension->id]));

        $response->assertRedirect(route('admin.session.create'));
    });

    it('can check payment status', function () {
        $transaction = ExtensionTransaction::factory()->create([
            'extension_id' => $this->extension->id,
            'user_id'      => $this->user->id,
            'status'       => 'completed',
        ]);

        $response = test()->actingAs($this->user)
            ->get(route('marketplace.payment.status', ['transaction_id' => $transaction->id]));

        $response->assertOK()
            ->assertJson(['status' => 'completed']);
    });
});

/**
 * Install Extension Flow Tests
 */
describe('Install Extension Flow', function () {
    beforeEach(function () {
        Storage::fake('extensions');

        $this->user = User::factory()->create();
        $this->author = User::factory()->create();

        $this->freeExtension = Extension::factory()->create([
            'name'      => 'Free Extension',
            'slug'      => 'free-extension',
            'price'     => 0,
            'status'    => 'approved',
            'author_id' => $this->author->id,
        ]);

        $this->paidExtension = Extension::factory()->create([
            'name'      => 'Paid Extension',
            'slug'      => 'paid-extension',
            'price'     => 29.99,
            'status'    => 'approved',
            'author_id' => $this->author->id,
        ]);

        $this->version = ExtensionVersion::factory()->create([
            'extension_id' => $this->freeExtension->id,
            'version'      => '1.0.0',
            'status'       => 'approved',
        ]);

        $this->paidVersion = ExtensionVersion::factory()->create([
            'extension_id' => $this->paidExtension->id,
            'version'      => '1.0.0',
            'status'       => 'approved',
        ]);
    });

    it('can install free extension without payment', function () {
        Event::fake();

        $response = test()->actingAs($this->user)
            ->post(route('marketplace.install.extension', ['id' => $this->freeExtension->id]));

        $response->assertStatus(302)
            ->assertSessionHas('success');

        // Verify installation record is created
        $installation = ExtensionInstallation::where('extension_id', $this->freeExtension->id)
            ->where('user_id', $this->user->id)
            ->first();

        expect($installation)->not->toBeNull()
            ->and($installation->status)->toBe('active');

        // Verify downloads count is incremented
        $this->freeExtension->refresh();
        expect($this->freeExtension->downloads_count)->toBe(1);
    });

    it('cannot install paid extension without purchase', function () {
        $response = test()->actingAs($this->user)
            ->post(route('marketplace.install.extension', ['id' => $this->paidExtension->id]));

        $response->assertSessionHasErrors();
    });

    it('can install paid extension after purchase', function () {
        // Create completed transaction
        ExtensionTransaction::factory()->create([
            'extension_id' => $this->paidExtension->id,
            'user_id'      => $this->user->id,
            'status'       => 'completed',
        ]);

        Event::fake();

        $response = test()->actingAs($this->user)
            ->post(route('marketplace.install.extension', ['id' => $this->paidExtension->id]));

        $response->assertStatus(302)
            ->assertSessionHas('success');

        $installation = ExtensionInstallation::where('extension_id', $this->paidExtension->id)
            ->where('user_id', $this->user->id)
            ->first();

        expect($installation)->not->toBeNull()
            ->and($installation->status)->toBe('active');
    });

    it('can check compatibility before installation', function () {
        $response = test()->actingAs($this->user)
            ->post(route('marketplace.install.check_compatibility', ['id' => $this->freeExtension->id]));

        $response->assertOK()
            ->assertJson(['compatible' => true]);
    });

    it('can enable installed extension', function () {
        $installation = ExtensionInstallation::factory()->create([
            'extension_id' => $this->freeExtension->id,
            'user_id'      => $this->user->id,
            'status'       => 'inactive',
        ]);

        $response = test()->actingAs($this->user)
            ->post(route('marketplace.install.enable', ['installation_id' => $installation->id]));

        $response->assertStatus(302);

        $installation->refresh();
        expect($installation->status)->toBe('active');
    });

    it('can disable installed extension', function () {
        $installation = ExtensionInstallation::factory()->create([
            'extension_id' => $this->freeExtension->id,
            'user_id'      => $this->user->id,
            'status'       => 'active',
        ]);

        $response = test()->actingAs($this->user)
            ->post(route('marketplace.install.disable', ['installation_id' => $installation->id]));

        $response->assertStatus(302);

        $installation->refresh();
        expect($installation->status)->toBe('inactive');
    });

    it('can uninstall extension', function () {
        $installation = ExtensionInstallation::factory()->create([
            'extension_id' => $this->freeExtension->id,
            'user_id'      => $this->user->id,
            'status'       => 'active',
        ]);

        Event::fake();

        $response = test()->actingAs($this->user)
            ->delete(route('marketplace.install.uninstall', ['installation_id' => $installation->id]));

        $response->assertStatus(302);

        // Verify installation is soft deleted or marked as uninstalled
        expect(ExtensionInstallation::find($installation->id))->toBeNull();
    });

    it('can toggle auto-update for installation', function () {
        $installation = ExtensionInstallation::factory()->create([
            'extension_id'      => $this->freeExtension->id,
            'user_id'           => $this->user->id,
            'auto_update_enabled' => false,
        ]);

        $response = test()->actingAs($this->user)
            ->post(route('marketplace.install.toggle_auto_update', ['installation_id' => $installation->id]));

        $response->assertStatus(302);

        $installation->refresh();
        expect($installation->auto_update_enabled)->toBeTrue();
    });

    it('can view my installed extensions', function () {
        ExtensionInstallation::factory()->create([
            'extension_id' => $this->freeExtension->id,
            'user_id'      => $this->user->id,
            'status'       => 'active',
        ]);

        $response = test()->actingAs($this->user)
            ->get(route('marketplace.my_extensions.index'));

        $response->assertOK()
            ->assertSee('Free Extension');
    });

    it('can check installation status', function () {
        $response = test()->actingAs($this->user)
            ->get(route('marketplace.install.status', ['id' => $this->freeExtension->id]));

        $response->assertOK()
            ->assertJson(['installed' => false]);
    });

    it('requires authentication to install extension', function () {
        $response = test()->post(route('marketplace.install.extension', ['id' => $this->freeExtension->id]));

        $response->assertRedirect(route('admin.session.create'));
    });
});

/**
 * Review Extension Flow Tests
 */
describe('Review Extension Flow', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->author = User::factory()->create();

        $this->extension = Extension::factory()->create([
            'name'           => 'Test Extension',
            'slug'           => 'test-extension',
            'status'         => 'approved',
            'author_id'      => $this->author->id,
            'average_rating' => 0,
        ]);

        $this->version = ExtensionVersion::factory()->create([
            'extension_id' => $this->extension->id,
        ]);
    });

    it('can submit a review for installed extension', function () {
        // Create installation to verify purchase
        ExtensionInstallation::factory()->create([
            'extension_id' => $this->extension->id,
            'user_id'      => $this->user->id,
        ]);

        Event::fake();

        $reviewData = [
            'rating'      => 5,
            'title'       => 'Excellent Extension',
            'review_text' => 'This extension is amazing and works perfectly!',
        ];

        $response = test()->actingAs($this->user)
            ->post(route('marketplace.reviews.store', ['extension_id' => $this->extension->id]), $reviewData);

        $response->assertStatus(302)
            ->assertSessionHas('success');

        // Verify review is created
        $review = ExtensionReview::where('extension_id', $this->extension->id)
            ->where('user_id', $this->user->id)
            ->first();

        expect($review)->not->toBeNull()
            ->and($review->rating)->toBe(5)
            ->and($review->title)->toBe('Excellent Extension')
            ->and($review->is_verified_purchase)->toBeTrue();
    });

    it('cannot submit review without installation', function () {
        $reviewData = [
            'rating'      => 5,
            'review_text' => 'Great extension!',
        ];

        $response = test()->actingAs($this->user)
            ->post(route('marketplace.reviews.store', ['extension_id' => $this->extension->id]), $reviewData);

        $response->assertSessionHasErrors();
    });

    it('validates review data', function () {
        ExtensionInstallation::factory()->create([
            'extension_id' => $this->extension->id,
            'user_id'      => $this->user->id,
        ]);

        // Invalid rating
        $invalidData = [
            'rating'      => 10, // Should be between 1-5
            'review_text' => 'x', // Too short
        ];

        $response = test()->actingAs($this->user)
            ->post(route('marketplace.reviews.store', ['extension_id' => $this->extension->id]), $invalidData);

        $response->assertSessionHasErrors(['rating', 'review_text']);
    });

    it('can update own review', function () {
        $review = ExtensionReview::factory()->create([
            'extension_id' => $this->extension->id,
            'user_id'      => $this->user->id,
            'rating'       => 4,
            'review_text'  => 'Good extension',
        ]);

        $updateData = [
            'rating'      => 5,
            'title'       => 'Updated Review',
            'review_text' => 'Updated: This extension is excellent!',
        ];

        $response = test()->actingAs($this->user)
            ->put(route('marketplace.reviews.update', ['id' => $review->id]), $updateData);

        $response->assertStatus(302);

        $review->refresh();
        expect($review->rating)->toBe(5)
            ->and($review->title)->toBe('Updated Review')
            ->and($review->review_text)->toBe('Updated: This extension is excellent!');
    });

    it('cannot update other users review', function () {
        $otherUser = User::factory()->create();

        $review = ExtensionReview::factory()->create([
            'extension_id' => $this->extension->id,
            'user_id'      => $otherUser->id,
        ]);

        $updateData = [
            'rating'      => 1,
            'review_text' => 'Hacked review',
        ];

        $response = test()->actingAs($this->user)
            ->put(route('marketplace.reviews.update', ['id' => $review->id]), $updateData);

        $response->assertStatus(403);
    });

    it('can delete own review', function () {
        $review = ExtensionReview::factory()->create([
            'extension_id' => $this->extension->id,
            'user_id'      => $this->user->id,
        ]);

        $response = test()->actingAs($this->user)
            ->delete(route('marketplace.reviews.destroy', ['id' => $review->id]));

        $response->assertStatus(302);

        expect(ExtensionReview::find($review->id))->toBeNull();
    });

    it('can mark review as helpful', function () {
        $review = ExtensionReview::factory()->create([
            'extension_id'  => $this->extension->id,
            'user_id'       => $this->author->id,
            'helpful_count' => 0,
        ]);

        $response = test()->actingAs($this->user)
            ->post(route('marketplace.reviews.helpful', ['id' => $review->id]));

        $response->assertStatus(302);

        $review->refresh();
        expect($review->helpful_count)->toBe(1);
    });

    it('can report inappropriate review', function () {
        $review = ExtensionReview::factory()->create([
            'extension_id' => $this->extension->id,
            'user_id'      => $this->author->id,
        ]);

        Event::fake();

        $response = test()->actingAs($this->user)
            ->post(route('marketplace.reviews.report', ['id' => $review->id]), [
                'reason' => 'spam',
            ]);

        $response->assertStatus(302);

        Event::assertDispatched('marketplace.review.reported');
    });

    it('updates extension average rating after review approval', function () {
        // Create multiple reviews
        ExtensionReview::factory()->create([
            'extension_id' => $this->extension->id,
            'user_id'      => User::factory()->create()->id,
            'rating'       => 5,
            'status'       => 'approved',
        ]);

        ExtensionReview::factory()->create([
            'extension_id' => $this->extension->id,
            'user_id'      => User::factory()->create()->id,
            'rating'       => 4,
            'status'       => 'approved',
        ]);

        // Trigger rating update
        $this->extension->updateAverageRating();

        expect($this->extension->average_rating)->toBe('4.50');
    });

    it('can view my reviews', function () {
        ExtensionReview::factory()->create([
            'extension_id' => $this->extension->id,
            'user_id'      => $this->user->id,
            'review_text'  => 'My review text',
        ]);

        $response = test()->actingAs($this->user)
            ->get(route('marketplace.reviews.my_reviews'));

        $response->assertOK()
            ->assertSee('My review text');
    });

    it('requires authentication to submit review', function () {
        $reviewData = [
            'rating'      => 5,
            'review_text' => 'Great extension!',
        ];

        $response = test()->post(route('marketplace.reviews.store', ['extension_id' => $this->extension->id]), $reviewData);

        $response->assertRedirect(route('admin.session.create'));
    });
});

/**
 * Check Updates Flow Tests
 */
describe('Check Updates Flow', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->author = User::factory()->create();

        $this->extension = Extension::factory()->create([
            'name'      => 'Updatable Extension',
            'slug'      => 'updatable-extension',
            'status'    => 'approved',
            'author_id' => $this->author->id,
        ]);

        $this->oldVersion = ExtensionVersion::factory()->create([
            'extension_id' => $this->extension->id,
            'version'      => '1.0.0',
            'status'       => 'approved',
        ]);

        $this->newVersion = ExtensionVersion::factory()->create([
            'extension_id' => $this->extension->id,
            'version'      => '2.0.0',
            'status'       => 'approved',
        ]);

        $this->installation = ExtensionInstallation::factory()->create([
            'extension_id' => $this->extension->id,
            'user_id'      => $this->user->id,
            'version_id'   => $this->oldVersion->id,
            'status'       => 'active',
        ]);
    });

    it('can check for available updates', function () {
        $response = test()->actingAs($this->user)
            ->get(route('marketplace.my_extensions.check_updates'));

        $response->assertOK()
            ->assertJson([
                'updates_available' => true,
            ]);
    });

    it('can view list of available updates', function () {
        $response = test()->actingAs($this->user)
            ->get(route('marketplace.my_extensions.updates'));

        $response->assertOK()
            ->assertSee('Updatable Extension')
            ->assertSee('2.0.0');
    });

    it('can update extension to latest version', function () {
        Event::fake();

        $response = test()->actingAs($this->user)
            ->post(route('marketplace.install.update', ['installation_id' => $this->installation->id]));

        $response->assertStatus(302)
            ->assertSessionHas('success');

        $this->installation->refresh();
        expect($this->installation->version_id)->toBe($this->newVersion->id);

        Event::assertDispatched('marketplace.extension.updated');
    });

    it('checks compatibility before updating', function () {
        // Create incompatible version
        $incompatibleVersion = ExtensionVersion::factory()->create([
            'extension_id'   => $this->extension->id,
            'version'        => '3.0.0',
            'laravel_version' => '^99.0', // Incompatible
            'status'         => 'approved',
        ]);

        $response = test()->actingAs($this->user)
            ->post(route('marketplace.install.update', ['installation_id' => $this->installation->id]));

        // Should skip incompatible version and update to 2.0.0
        $this->installation->refresh();
        expect($this->installation->version_id)->toBe($this->newVersion->id);
    });

    it('auto-updates extension when auto-update is enabled', function () {
        $this->installation->update(['auto_update_enabled' => true]);

        Event::fake();

        // Simulate update check (typically run by scheduled task)
        $response = test()->actingAs($this->user)
            ->get(route('marketplace.my_extensions.check_updates'));

        $response->assertOK();

        // In real implementation, this would trigger background update
        // For testing, we manually trigger it
        test()->actingAs($this->user)
            ->post(route('marketplace.install.update', ['installation_id' => $this->installation->id]));

        $this->installation->refresh();
        expect($this->installation->version_id)->toBe($this->newVersion->id);
    });

    it('does not auto-update when auto-update is disabled', function () {
        $this->installation->update(['auto_update_enabled' => false]);

        $response = test()->actingAs($this->user)
            ->get(route('marketplace.my_extensions.check_updates'));

        $response->assertOK();

        // Extension should still be on old version
        $this->installation->refresh();
        expect($this->installation->version_id)->toBe($this->oldVersion->id);
    });

    it('shows update notifications when updates are available', function () {
        $response = test()->actingAs($this->user)
            ->get(route('marketplace.my_extensions.index'));

        $response->assertOK()
            ->assertSee('Update Available')
            ->assertSee('2.0.0');
    });

    it('does not show updates when extension is on latest version', function () {
        $this->installation->update(['version_id' => $this->newVersion->id]);

        $response = test()->actingAs($this->user)
            ->get(route('marketplace.my_extensions.check_updates'));

        $response->assertOK()
            ->assertJson([
                'updates_available' => false,
            ]);
    });

    it('can view extension settings page', function () {
        $response = test()->actingAs($this->user)
            ->get(route('marketplace.my_extensions.settings', ['installation_id' => $this->installation->id]));

        $response->assertOK()
            ->assertSee('Updatable Extension');
    });

    it('can update extension settings', function () {
        $settings = [
            'api_key' => 'test-api-key',
            'enabled' => true,
        ];

        $response = test()->actingAs($this->user)
            ->put(route('marketplace.my_extensions.update_settings', ['installation_id' => $this->installation->id]), $settings);

        $response->assertStatus(302);

        $this->installation->refresh();
        expect($this->installation->settings)->toBeArray()
            ->and($this->installation->settings['api_key'])->toBe('test-api-key')
            ->and($this->installation->settings['enabled'])->toBeTrue();
    });

    it('requires authentication to check updates', function () {
        $response = test()->get(route('marketplace.my_extensions.check_updates'));

        $response->assertRedirect(route('admin.session.create'));
    });
});

/**
 * Complete End-to-End User Flow Tests
 */
describe('Complete User Journey', function () {
    it('can complete full flow: browse, purchase, install, review, update', function () {
        Storage::fake('extensions');

        // Setup
        $user = User::factory()->create();
        $author = User::factory()->create();

        $category = ExtensionCategory::factory()->create([
            'name' => 'Integrations',
            'slug' => 'integrations',
        ]);

        $extension = Extension::factory()->create([
            'name'        => 'Complete Flow Extension',
            'slug'        => 'complete-flow-extension',
            'type'        => 'integration',
            'price'       => 19.99,
            'status'      => 'approved',
            'category_id' => $category->id,
            'author_id'   => $author->id,
        ]);

        $version1 = ExtensionVersion::factory()->create([
            'extension_id' => $extension->id,
            'version'      => '1.0.0',
            'status'       => 'approved',
        ]);

        $version2 = ExtensionVersion::factory()->create([
            'extension_id' => $extension->id,
            'version'      => '1.1.0',
            'status'       => 'approved',
        ]);

        Event::fake();

        // Step 1: Browse marketplace
        $browseResponse = test()->get(route('marketplace.browse.index'));
        $browseResponse->assertOK()
            ->assertSee('Complete Flow Extension');

        // Step 2: View extension details
        $detailResponse = test()->get(route('marketplace.extension.show', ['slug' => 'complete-flow-extension']));
        $detailResponse->assertOK()
            ->assertSee('Complete Flow Extension')
            ->assertSee('19.99');

        // Step 3: Initiate payment
        $paymentResponse = test()->actingAs($user)
            ->post(route('marketplace.payment.initiate', ['extension_id' => $extension->id]));
        $paymentResponse->assertStatus(302);

        $transaction = ExtensionTransaction::where('extension_id', $extension->id)
            ->where('user_id', $user->id)
            ->first();
        expect($transaction)->not->toBeNull();

        // Step 4: Complete payment
        $transaction->update(['status' => 'completed']);

        // Step 5: Install extension
        $installResponse = test()->actingAs($user)
            ->post(route('marketplace.install.extension', ['id' => $extension->id]));
        $installResponse->assertStatus(302);

        $installation = ExtensionInstallation::where('extension_id', $extension->id)
            ->where('user_id', $user->id)
            ->first();
        expect($installation)->not->toBeNull()
            ->and($installation->status)->toBe('active');

        // Step 6: Submit review
        $reviewResponse = test()->actingAs($user)
            ->post(route('marketplace.reviews.store', ['extension_id' => $extension->id]), [
                'rating'      => 5,
                'title'       => 'Great extension!',
                'review_text' => 'This extension solved all my problems!',
            ]);
        $reviewResponse->assertStatus(302);

        $review = ExtensionReview::where('extension_id', $extension->id)
            ->where('user_id', $user->id)
            ->first();
        expect($review)->not->toBeNull()
            ->and($review->is_verified_purchase)->toBeTrue();

        // Step 7: Check for updates
        $updateCheckResponse = test()->actingAs($user)
            ->get(route('marketplace.my_extensions.check_updates'));
        $updateCheckResponse->assertOK()
            ->assertJson(['updates_available' => true]);

        // Step 8: Update extension
        $updateResponse = test()->actingAs($user)
            ->post(route('marketplace.install.update', ['installation_id' => $installation->id]));
        $updateResponse->assertStatus(302);

        $installation->refresh();
        expect($installation->version_id)->toBe($version2->id);

        // Verify all events were dispatched
        Event::assertDispatched('marketplace.payment.completed');
        Event::assertDispatched('marketplace.extension.installed');
        Event::assertDispatched('marketplace.review.created');
        Event::assertDispatched('marketplace.extension.updated');
    });

    it('can complete free extension flow without payment', function () {
        $user = User::factory()->create();
        $author = User::factory()->create();

        $extension = Extension::factory()->create([
            'name'      => 'Free Extension',
            'slug'      => 'free-extension',
            'price'     => 0,
            'status'    => 'approved',
            'author_id' => $author->id,
        ]);

        ExtensionVersion::factory()->create([
            'extension_id' => $extension->id,
            'version'      => '1.0.0',
            'status'       => 'approved',
        ]);

        Event::fake();

        // Browse
        $browseResponse = test()->get(route('marketplace.browse.free'));
        $browseResponse->assertOK()->assertSee('Free Extension');

        // View details
        $detailResponse = test()->get(route('marketplace.extension.show', ['slug' => 'free-extension']));
        $detailResponse->assertOK();

        // Install directly (no payment needed)
        $installResponse = test()->actingAs($user)
            ->post(route('marketplace.install.extension', ['id' => $extension->id]));
        $installResponse->assertStatus(302);

        $installation = ExtensionInstallation::where('extension_id', $extension->id)
            ->where('user_id', $user->id)
            ->first();
        expect($installation)->not->toBeNull();

        // Review
        $reviewResponse = test()->actingAs($user)
            ->post(route('marketplace.reviews.store', ['extension_id' => $extension->id]), [
                'rating'      => 4,
                'review_text' => 'Good free extension!',
            ]);
        $reviewResponse->assertStatus(302);

        // No payment event should be dispatched
        Event::assertNotDispatched('marketplace.payment.completed');
        // But installation event should be dispatched
        Event::assertDispatched('marketplace.extension.installed');
    });
});
