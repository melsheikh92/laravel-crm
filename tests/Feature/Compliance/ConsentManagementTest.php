<?php

use App\Http\Middleware\VerifyConsent;
use App\Models\ConsentRecord;
use App\Models\User;
use App\Services\Compliance\ConsentManager;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    // Enable compliance features for all tests
    Config::set('compliance.enabled', true);
    Config::set('compliance.consent.enabled', true);
    Config::set('compliance.consent.capture_ip', true);
    Config::set('compliance.consent.capture_user_agent', true);

    // Set up consent types
    Config::set('compliance.consent.types', [
        'terms_of_service' => [
            'required' => true,
            'description' => 'Terms of Service',
            'purpose' => 'Legal agreement',
        ],
        'privacy_policy' => [
            'required' => true,
            'description' => 'Privacy Policy',
            'purpose' => 'Data processing',
        ],
        'marketing' => [
            'required' => false,
            'description' => 'Marketing',
            'purpose' => 'Promotional emails',
        ],
        'analytics' => [
            'required' => false,
            'description' => 'Analytics',
            'purpose' => 'Usage tracking',
        ],
    ]);

    // Clear consent records before each test
    ConsentRecord::query()->delete();
});

// ============================================
// ConsentRecord Model Tests
// ============================================

it('creates consent record with required fields', function () {
    $user = User::factory()->create();

    $consent = ConsentRecord::create([
        'user_id' => $user->id,
        'consent_type' => 'marketing',
        'purpose' => 'Email marketing',
    ]);

    expect($consent)->toBeInstanceOf(ConsentRecord::class);
    expect($consent->user_id)->toBe($user->id);
    expect($consent->consent_type)->toBe('marketing');
    expect($consent->purpose)->toBe('Email marketing');
    expect($consent->given_at)->not->toBeNull();
    expect($consent->withdrawn_at)->toBeNull();
});

it('auto-captures IP address and user agent on creation', function () {
    $user = User::factory()->create();

    $consent = ConsentRecord::create([
        'user_id' => $user->id,
        'consent_type' => 'marketing',
        'purpose' => 'Test',
    ]);

    expect($consent->ip_address)->not->toBeNull();
    expect($consent->user_agent)->not->toBeNull();
});

it('filters active consents correctly', function () {
    $user = User::factory()->create();

    ConsentRecord::create([
        'user_id' => $user->id,
        'consent_type' => 'marketing',
        'purpose' => 'Test',
    ]);

    ConsentRecord::create([
        'user_id' => $user->id,
        'consent_type' => 'analytics',
        'purpose' => 'Test',
        'withdrawn_at' => now(),
    ]);

    $activeConsents = ConsentRecord::active()->get();

    expect($activeConsents)->toHaveCount(1);
    expect($activeConsents->first()->consent_type)->toBe('marketing');
});

it('filters withdrawn consents correctly', function () {
    $user = User::factory()->create();

    ConsentRecord::create([
        'user_id' => $user->id,
        'consent_type' => 'marketing',
        'purpose' => 'Test',
    ]);

    ConsentRecord::create([
        'user_id' => $user->id,
        'consent_type' => 'analytics',
        'purpose' => 'Test',
        'withdrawn_at' => now(),
    ]);

    $withdrawnConsents = ConsentRecord::withdrawn()->get();

    expect($withdrawnConsents)->toHaveCount(1);
    expect($withdrawnConsents->first()->consent_type)->toBe('analytics');
});

it('filters consents by type', function () {
    $user = User::factory()->create();

    ConsentRecord::create([
        'user_id' => $user->id,
        'consent_type' => 'marketing',
        'purpose' => 'Test',
    ]);

    ConsentRecord::create([
        'user_id' => $user->id,
        'consent_type' => 'analytics',
        'purpose' => 'Test',
    ]);

    $marketingConsents = ConsentRecord::byType('marketing')->get();

    expect($marketingConsents)->toHaveCount(1);
    expect($marketingConsents->first()->consent_type)->toBe('marketing');
});

it('filters consents by multiple types', function () {
    $user = User::factory()->create();

    ConsentRecord::create([
        'user_id' => $user->id,
        'consent_type' => 'marketing',
        'purpose' => 'Test',
    ]);

    ConsentRecord::create([
        'user_id' => $user->id,
        'consent_type' => 'analytics',
        'purpose' => 'Test',
    ]);

    ConsentRecord::create([
        'user_id' => $user->id,
        'consent_type' => 'terms_of_service',
        'purpose' => 'Test',
    ]);

    $consents = ConsentRecord::byTypes(['marketing', 'analytics'])->get();

    expect($consents)->toHaveCount(2);
});

it('filters consents by user', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    ConsentRecord::create([
        'user_id' => $user1->id,
        'consent_type' => 'marketing',
        'purpose' => 'Test',
    ]);

    ConsentRecord::create([
        'user_id' => $user2->id,
        'consent_type' => 'marketing',
        'purpose' => 'Test',
    ]);

    $user1Consents = ConsentRecord::byUser($user1->id)->get();

    expect($user1Consents)->toHaveCount(1);
    expect($user1Consents->first()->user_id)->toBe($user1->id);
});

it('checks if consent is active', function () {
    $user = User::factory()->create();

    $activeConsent = ConsentRecord::create([
        'user_id' => $user->id,
        'consent_type' => 'marketing',
        'purpose' => 'Test',
    ]);

    $withdrawnConsent = ConsentRecord::create([
        'user_id' => $user->id,
        'consent_type' => 'analytics',
        'purpose' => 'Test',
        'withdrawn_at' => now(),
    ]);

    expect($activeConsent->isActive())->toBeTrue();
    expect($withdrawnConsent->isActive())->toBeFalse();
});

it('checks if consent is withdrawn', function () {
    $user = User::factory()->create();

    $activeConsent = ConsentRecord::create([
        'user_id' => $user->id,
        'consent_type' => 'marketing',
        'purpose' => 'Test',
    ]);

    $withdrawnConsent = ConsentRecord::create([
        'user_id' => $user->id,
        'consent_type' => 'analytics',
        'purpose' => 'Test',
        'withdrawn_at' => now(),
    ]);

    expect($activeConsent->isWithdrawn())->toBeFalse();
    expect($withdrawnConsent->isWithdrawn())->toBeTrue();
});

it('withdraws active consent', function () {
    $user = User::factory()->create();

    $consent = ConsentRecord::create([
        'user_id' => $user->id,
        'consent_type' => 'marketing',
        'purpose' => 'Test',
    ]);

    expect($consent->isActive())->toBeTrue();

    $result = $consent->withdraw();

    expect($result)->toBeTrue();
    expect($consent->fresh()->isWithdrawn())->toBeTrue();
    expect($consent->fresh()->withdrawn_at)->not->toBeNull();
});

it('cannot withdraw already withdrawn consent', function () {
    $user = User::factory()->create();

    $consent = ConsentRecord::create([
        'user_id' => $user->id,
        'consent_type' => 'marketing',
        'purpose' => 'Test',
        'withdrawn_at' => now(),
    ]);

    $result = $consent->withdraw();

    expect($result)->toBeFalse();
});

it('reinstates withdrawn consent', function () {
    $user = User::factory()->create();

    $consent = ConsentRecord::create([
        'user_id' => $user->id,
        'consent_type' => 'marketing',
        'purpose' => 'Test',
        'withdrawn_at' => now(),
    ]);

    expect($consent->isWithdrawn())->toBeTrue();

    $result = $consent->reinstate();

    expect($result)->toBeTrue();
    expect($consent->fresh()->isActive())->toBeTrue();
    expect($consent->fresh()->withdrawn_at)->toBeNull();
});

it('cannot reinstate active consent', function () {
    $user = User::factory()->create();

    $consent = ConsentRecord::create([
        'user_id' => $user->id,
        'consent_type' => 'marketing',
        'purpose' => 'Test',
    ]);

    $result = $consent->reinstate();

    expect($result)->toBeFalse();
});

it('calculates consent duration in days', function () {
    $user = User::factory()->create();

    $consent = ConsentRecord::create([
        'user_id' => $user->id,
        'consent_type' => 'marketing',
        'purpose' => 'Test',
        'given_at' => now()->subDays(10),
    ]);

    $duration = $consent->getDurationInDays();

    expect($duration)->toBeGreaterThanOrEqual(9);
    expect($duration)->toBeLessThanOrEqual(11);
});

it('gets consent type label from config', function () {
    $user = User::factory()->create();

    $consent = ConsentRecord::create([
        'user_id' => $user->id,
        'consent_type' => 'marketing',
        'purpose' => 'Test',
    ]);

    $label = $consent->getTypeLabel();

    expect($label)->toBe('Marketing');
});

it('adds and retrieves metadata', function () {
    $user = User::factory()->create();

    $consent = ConsentRecord::create([
        'user_id' => $user->id,
        'consent_type' => 'marketing',
        'purpose' => 'Test',
    ]);

    $consent->addMetadata('source', 'web_form');

    expect($consent->getMetadata('source'))->toBe('web_form');
    expect($consent->hasMetadata('source'))->toBeTrue();
    expect($consent->hasMetadata('nonexistent'))->toBeFalse();
});

// ============================================
// ConsentManager Service Tests
// ============================================

it('records consent for authenticated user', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $consentManager = app(ConsentManager::class);
    $consent = $consentManager->recordConsent('marketing');

    expect($consent)->toBeInstanceOf(ConsentRecord::class);
    expect($consent->user_id)->toBe($user->id);
    expect($consent->consent_type)->toBe('marketing');
    expect($consent->isActive())->toBeTrue();
});

it('records consent with custom purpose', function () {
    $user = User::factory()->create();

    $consentManager = app(ConsentManager::class);
    $consent = $consentManager->recordConsent(
        'marketing',
        $user,
        'Custom purpose'
    );

    expect($consent->purpose)->toBe('Custom purpose');
});

it('records consent with metadata', function () {
    $user = User::factory()->create();

    $consentManager = app(ConsentManager::class);
    $consent = $consentManager->recordConsent(
        'marketing',
        $user,
        null,
        ['campaign' => 'summer_2023']
    );

    expect($consent->metadata)->toBeArray();
    expect($consent->metadata['campaign'])->toBe('summer_2023');
});

it('withdraws consent for user', function () {
    $user = User::factory()->create();
    $consentManager = app(ConsentManager::class);

    $consent = $consentManager->recordConsent('marketing', $user);

    expect($consent->isActive())->toBeTrue();

    $withdrawn = $consentManager->withdrawConsent('marketing', $user);

    expect($withdrawn)->toBeTrue();
    expect($consent->fresh()->isWithdrawn())->toBeTrue();
});

it('returns false when withdrawing non-existent consent', function () {
    $user = User::factory()->create();
    $consentManager = app(ConsentManager::class);

    $withdrawn = $consentManager->withdrawConsent('marketing', $user);

    expect($withdrawn)->toBeFalse();
});

it('checks if user has active consent', function () {
    $user = User::factory()->create();
    $consentManager = app(ConsentManager::class);

    expect($consentManager->checkConsent('marketing', $user))->toBeFalse();

    $consentManager->recordConsent('marketing', $user);

    expect($consentManager->checkConsent('marketing', $user))->toBeTrue();
});

it('gets consent history for user', function () {
    $user = User::factory()->create();
    $consentManager = app(ConsentManager::class);

    $consentManager->recordConsent('marketing', $user);
    $consentManager->recordConsent('analytics', $user);
    $consentManager->withdrawConsent('marketing', $user);

    $history = $consentManager->getConsentHistory($user);

    expect($history)->toHaveCount(2);
});

it('gets only active consents for user', function () {
    $user = User::factory()->create();
    $consentManager = app(ConsentManager::class);

    $consentManager->recordConsent('marketing', $user);
    $consentManager->recordConsent('analytics', $user);
    $consentManager->withdrawConsent('marketing', $user);

    $active = $consentManager->getActiveConsents($user);

    expect($active)->toHaveCount(1);
    expect($active->first()->consent_type)->toBe('analytics');
});

it('checks if user has all required consents', function () {
    $user = User::factory()->create();
    $consentManager = app(ConsentManager::class);

    expect($consentManager->hasRequiredConsents($user))->toBeFalse();

    $consentManager->recordConsent('terms_of_service', $user);
    expect($consentManager->hasRequiredConsents($user))->toBeFalse();

    $consentManager->recordConsent('privacy_policy', $user);
    expect($consentManager->hasRequiredConsents($user))->toBeTrue();
});

it('gets missing required consents', function () {
    $user = User::factory()->create();
    $consentManager = app(ConsentManager::class);

    $missing = $consentManager->getMissingRequiredConsents($user);

    expect($missing)->toContain('terms_of_service');
    expect($missing)->toContain('privacy_policy');

    $consentManager->recordConsent('terms_of_service', $user);

    $missing = $consentManager->getMissingRequiredConsents($user);

    expect($missing)->not->toContain('terms_of_service');
    expect($missing)->toContain('privacy_policy');
});

it('records multiple consents at once', function () {
    $user = User::factory()->create();
    $consentManager = app(ConsentManager::class);

    $consents = $consentManager->recordMultipleConsents(
        ['marketing', 'analytics', 'terms_of_service'],
        $user
    );

    expect($consents)->toHaveCount(3);
    expect($consentManager->checkConsent('marketing', $user))->toBeTrue();
    expect($consentManager->checkConsent('analytics', $user))->toBeTrue();
    expect($consentManager->checkConsent('terms_of_service', $user))->toBeTrue();
});

it('withdraws all consents for user', function () {
    $user = User::factory()->create();
    $consentManager = app(ConsentManager::class);

    $consentManager->recordConsent('marketing', $user);
    $consentManager->recordConsent('analytics', $user);
    $consentManager->recordConsent('terms_of_service', $user);

    $count = $consentManager->withdrawAllConsents($user);

    expect($count)->toBe(3);
    expect($consentManager->getActiveConsents($user))->toHaveCount(0);
});

it('returns null when consent management is disabled', function () {
    Config::set('compliance.consent.enabled', false);

    $user = User::factory()->create();
    $consentManager = app(ConsentManager::class);

    $consent = $consentManager->recordConsent('marketing', $user);

    expect($consent)->toBeNull();
});

// ============================================
// VerifyConsent Middleware Tests
// ============================================

it('allows access when user has required consent', function () {
    $user = User::factory()->create();
    $consentManager = app(ConsentManager::class);

    // Give required consents
    $consentManager->recordConsent('terms_of_service', $user);
    $consentManager->recordConsent('privacy_policy', $user);

    // Set up test route
    Route::middleware(['web', VerifyConsent::class])->get('/test-consent', function () {
        return response()->json(['success' => true]);
    });

    $this->actingAs($user)
        ->get('/test-consent')
        ->assertStatus(200);
});

it('blocks access when user missing required consent', function () {
    $user = User::factory()->create();

    // Set up test route
    Route::middleware(['web', VerifyConsent::class])->get('/test-consent', function () {
        return response()->json(['success' => true]);
    });

    $this->actingAs($user)
        ->get('/test-consent')
        ->assertRedirect();
});

it('allows guest users through middleware', function () {
    // Set up test route
    Route::middleware(['web', VerifyConsent::class])->get('/test-consent', function () {
        return response()->json(['success' => true]);
    });

    $this->get('/test-consent')
        ->assertStatus(200);
});

it('verifies specific consent type via middleware parameter', function () {
    $user = User::factory()->create();
    $consentManager = app(ConsentManager::class);

    // Give marketing consent
    $consentManager->recordConsent('marketing', $user);

    // Set up test route requiring marketing consent
    Route::middleware(['web', VerifyConsent::class . ':marketing'])->get('/test-marketing', function () {
        return response()->json(['success' => true]);
    });

    $this->actingAs($user)
        ->get('/test-marketing')
        ->assertStatus(200);
});

it('blocks access when missing specific consent type', function () {
    $user = User::factory()->create();

    // Set up test route requiring marketing consent
    Route::middleware(['web', VerifyConsent::class . ':marketing'])->get('/test-marketing', function () {
        return response()->json(['success' => true]);
    });

    $this->actingAs($user)
        ->get('/test-marketing')
        ->assertRedirect();
});

it('returns JSON error for API requests without consent', function () {
    $user = User::factory()->create();

    // Set up test API route
    Route::middleware(['api', VerifyConsent::class])->get('/api/test-consent', function () {
        return response()->json(['success' => true]);
    });

    $this->actingAs($user, 'api')
        ->getJson('/api/test-consent')
        ->assertStatus(403)
        ->assertJson([
            'error' => 'consent_required',
        ]);
});

// ============================================
// API Endpoint Tests
// ============================================

it('lists all consents for authenticated user', function () {
    $user = User::factory()->create();
    $consentManager = app(ConsentManager::class);

    $consentManager->recordConsent('marketing', $user);
    $consentManager->recordConsent('analytics', $user);

    $this->actingAs($user, 'api')
        ->getJson('/api/consent')
        ->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                '*' => ['id', 'user_id', 'consent_type', 'purpose', 'given_at', 'withdrawn_at'],
            ],
        ])
        ->assertJsonCount(2, 'data');
});

it('records new consent via API', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'api')
        ->postJson('/api/consent', [
            'consent_type' => 'marketing',
            'purpose' => 'Email campaigns',
            'metadata' => ['source' => 'api'],
        ])
        ->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'Consent recorded successfully',
        ]);

    expect(ConsentRecord::where('user_id', $user->id)->count())->toBe(1);
});

it('validates consent data when recording via API', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'api')
        ->postJson('/api/consent', [
            // Missing consent_type
            'purpose' => 'Test',
        ])
        ->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'Validation failed',
        ]);
});

it('records multiple consents via API', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'api')
        ->postJson('/api/consent/bulk', [
            'consent_types' => ['marketing', 'analytics', 'terms_of_service'],
            'metadata' => ['source' => 'onboarding'],
        ])
        ->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'Consents recorded successfully',
        ]);

    expect(ConsentRecord::where('user_id', $user->id)->count())->toBe(3);
});

it('withdraws consent via API', function () {
    $user = User::factory()->create();
    $consentManager = app(ConsentManager::class);

    $consentManager->recordConsent('marketing', $user);

    $this->actingAs($user, 'api')
        ->deleteJson('/api/consent/marketing')
        ->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Consent withdrawn successfully',
        ]);

    expect($consentManager->checkConsent('marketing', $user))->toBeFalse();
});

it('returns 404 when withdrawing non-existent consent via API', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'api')
        ->deleteJson('/api/consent/marketing')
        ->assertStatus(404)
        ->assertJson([
            'success' => false,
            'message' => 'No active consent found for this type',
        ]);
});

it('withdraws all consents via API', function () {
    $user = User::factory()->create();
    $consentManager = app(ConsentManager::class);

    $consentManager->recordConsent('marketing', $user);
    $consentManager->recordConsent('analytics', $user);

    $this->actingAs($user, 'api')
        ->deleteJson('/api/consent')
        ->assertStatus(200)
        ->assertJson([
            'success' => true,
            'count' => 2,
        ]);

    expect($consentManager->getActiveConsents($user))->toHaveCount(0);
});

it('gets active consents via API', function () {
    $user = User::factory()->create();
    $consentManager = app(ConsentManager::class);

    $consentManager->recordConsent('marketing', $user);
    $consentManager->recordConsent('analytics', $user);
    $consentManager->withdrawConsent('marketing', $user);

    $this->actingAs($user, 'api')
        ->getJson('/api/consent/active')
        ->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.consent_type', 'analytics');
});

it('checks specific consent via API', function () {
    $user = User::factory()->create();
    $consentManager = app(ConsentManager::class);

    $consentManager->recordConsent('marketing', $user);

    $this->actingAs($user, 'api')
        ->getJson('/api/consent/check/marketing')
        ->assertStatus(200)
        ->assertJson([
            'success' => true,
            'has_consent' => true,
            'consent_type' => 'marketing',
        ]);

    $this->actingAs($user, 'api')
        ->getJson('/api/consent/check/analytics')
        ->assertStatus(200)
        ->assertJson([
            'success' => true,
            'has_consent' => false,
            'consent_type' => 'analytics',
        ]);
});

it('checks required consents via API', function () {
    $user = User::factory()->create();
    $consentManager = app(ConsentManager::class);

    $this->actingAs($user, 'api')
        ->getJson('/api/consent/check-required')
        ->assertStatus(200)
        ->assertJson([
            'success' => true,
            'has_required_consents' => false,
        ])
        ->assertJsonPath('missing_consents', ['terms_of_service', 'privacy_policy']);

    $consentManager->recordConsent('terms_of_service', $user);
    $consentManager->recordConsent('privacy_policy', $user);

    $this->actingAs($user, 'api')
        ->getJson('/api/consent/check-required')
        ->assertStatus(200)
        ->assertJson([
            'success' => true,
            'has_required_consents' => true,
            'missing_consents' => [],
        ]);
});

it('gets available consent types via API', function () {
    $this->getJson('/api/consent/types')
        ->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                '*' => ['type', 'required', 'description', 'purpose'],
            ],
        ])
        ->assertJsonCount(4, 'data');
});

it('requires authentication for consent endpoints', function () {
    $this->getJson('/api/consent')
        ->assertStatus(401);

    $this->postJson('/api/consent', ['consent_type' => 'marketing'])
        ->assertStatus(401);

    $this->deleteJson('/api/consent/marketing')
        ->assertStatus(401);
});

// ============================================
// Integration Tests
// ============================================

it('handles complete consent lifecycle', function () {
    $user = User::factory()->create();
    $consentManager = app(ConsentManager::class);

    // User gives consent
    $consent = $consentManager->recordConsent('marketing', $user, 'Email campaigns');
    expect($consent->isActive())->toBeTrue();

    // Verify consent exists
    expect($consentManager->checkConsent('marketing', $user))->toBeTrue();

    // User withdraws consent
    $withdrawn = $consentManager->withdrawConsent('marketing', $user);
    expect($withdrawn)->toBeTrue();

    // Verify consent is withdrawn
    expect($consentManager->checkConsent('marketing', $user))->toBeFalse();
    expect($consent->fresh()->isWithdrawn())->toBeTrue();

    // Consent record still exists in history
    $history = $consentManager->getConsentHistory($user, 'marketing');
    expect($history)->toHaveCount(1);
});

it('tracks consent across multiple users', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $consentManager = app(ConsentManager::class);

    $consentManager->recordConsent('marketing', $user1);
    $consentManager->recordConsent('analytics', $user1);
    $consentManager->recordConsent('marketing', $user2);

    expect($consentManager->getActiveConsents($user1))->toHaveCount(2);
    expect($consentManager->getActiveConsents($user2))->toHaveCount(1);

    $stats = ConsentRecord::getStatsByType();
    $marketing = $stats->firstWhere('consent_type', 'marketing');

    expect($marketing->total)->toBe(2);
    expect($marketing->active)->toBe(2);
});

it('respects configuration for IP and user agent capture', function () {
    Config::set('compliance.consent.capture_ip', false);
    Config::set('compliance.consent.capture_user_agent', false);

    $user = User::factory()->create();

    $consent = ConsentRecord::create([
        'user_id' => $user->id,
        'consent_type' => 'marketing',
        'purpose' => 'Test',
    ]);

    expect($consent->ip_address)->toBeNull();
    expect($consent->user_agent)->toBeNull();
});

it('maintains consent audit trail with metadata', function () {
    $user = User::factory()->create();
    $consentManager = app(ConsentManager::class);

    // Record consent with metadata
    $consent = $consentManager->recordConsent(
        'marketing',
        $user,
        null,
        ['source' => 'mobile_app', 'version' => '1.2.3']
    );

    // Withdraw with metadata
    $consentManager->withdrawConsent('marketing', $user, ['reason' => 'user_request']);

    $consent = $consent->fresh();

    expect($consent->metadata['source'])->toBe('mobile_app');
    expect($consent->metadata['version'])->toBe('1.2.3');
    expect($consent->metadata['withdrawal_metadata']['reason'])->toBe('user_request');
    expect($consent->metadata)->toHaveKey('withdrawn_ip');
    expect($consent->metadata)->toHaveKey('withdrawn_user_agent');
});

it('handles bulk operations efficiently', function () {
    $user = User::factory()->create();
    $consentManager = app(ConsentManager::class);

    // Record multiple consents
    $consents = $consentManager->recordMultipleConsents(
        ['terms_of_service', 'privacy_policy', 'marketing', 'analytics'],
        $user
    );

    expect($consents)->toHaveCount(4);
    expect(ConsentRecord::where('user_id', $user->id)->count())->toBe(4);

    // Withdraw all at once
    $count = $consentManager->withdrawAllConsents($user);

    expect($count)->toBe(4);
    expect(ConsentRecord::where('user_id', $user->id)->active()->count())->toBe(0);
});
