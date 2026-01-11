<?php

namespace Tests\Unit\Services;

use App\Models\ConsentRecord;
use App\Models\User;
use App\Services\Compliance\ConsentManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class ConsentManagerTest extends TestCase
{
    use RefreshDatabase;

    protected ConsentManager $consentManager;

    protected function setUp(): void
    {
        parent::setUp();

        // Enable compliance features
        Config::set('compliance.enabled', true);
        Config::set('compliance.consent.enabled', true);
        Config::set('compliance.consent.capture_ip', true);
        Config::set('compliance.consent.capture_user_agent', true);

        // Set up consent types
        Config::set('compliance.consent.types', [
            'terms_of_service' => [
                'required' => true,
                'description' => 'Terms of Service',
                'purpose' => 'Legal agreement for platform usage',
            ],
            'privacy_policy' => [
                'required' => true,
                'description' => 'Privacy Policy',
                'purpose' => 'Data processing agreement',
            ],
            'marketing' => [
                'required' => false,
                'description' => 'Marketing Communications',
                'purpose' => 'Promotional emails and newsletters',
            ],
            'analytics' => [
                'required' => false,
                'description' => 'Analytics',
                'purpose' => 'Usage tracking and analytics',
            ],
        ]);

        // Clear consent records
        ConsentRecord::query()->delete();

        // Instantiate the service
        $this->consentManager = new ConsentManager();
    }

    /** @test */
    public function it_records_consent_with_user_instance()
    {
        $user = User::factory()->create();

        $consent = $this->consentManager->recordConsent('marketing', $user);

        $this->assertInstanceOf(ConsentRecord::class, $consent);
        $this->assertEquals('marketing', $consent->consent_type);
        $this->assertEquals($user->id, $consent->user_id);
        $this->assertNotNull($consent->given_at);
        $this->assertNull($consent->withdrawn_at);
    }

    /** @test */
    public function it_records_consent_with_user_id()
    {
        $user = User::factory()->create();

        $consent = $this->consentManager->recordConsent('marketing', $user->id);

        $this->assertInstanceOf(ConsentRecord::class, $consent);
        $this->assertEquals($user->id, $consent->user_id);
    }

    /** @test */
    public function it_records_consent_with_authenticated_user()
    {
        $user = User::factory()->create();
        Auth::login($user);

        $consent = $this->consentManager->recordConsent('marketing');

        $this->assertInstanceOf(ConsentRecord::class, $consent);
        $this->assertEquals($user->id, $consent->user_id);
    }

    /** @test */
    public function it_throws_exception_when_no_user_provided()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('User ID is required to record consent');

        $this->consentManager->recordConsent('marketing');
    }

    /** @test */
    public function it_records_consent_with_custom_purpose()
    {
        $user = User::factory()->create();

        $consent = $this->consentManager->recordConsent(
            'marketing',
            $user,
            'Custom marketing purpose'
        );

        $this->assertEquals('Custom marketing purpose', $consent->purpose);
    }

    /** @test */
    public function it_uses_config_purpose_when_not_provided()
    {
        $user = User::factory()->create();

        $consent = $this->consentManager->recordConsent('marketing', $user);

        $this->assertEquals('Promotional emails and newsletters', $consent->purpose);
    }

    /** @test */
    public function it_generates_default_purpose_for_unknown_type()
    {
        $user = User::factory()->create();

        $consent = $this->consentManager->recordConsent('unknown_type', $user);

        $this->assertEquals('Consent for unknown_type', $consent->purpose);
    }

    /** @test */
    public function it_records_consent_with_metadata()
    {
        $user = User::factory()->create();
        $metadata = [
            'source' => 'mobile_app',
            'version' => '2.1.0',
            'campaign' => 'summer_sale',
        ];

        $consent = $this->consentManager->recordConsent('marketing', $user, null, $metadata);

        $this->assertIsArray($consent->metadata);
        $this->assertEquals('mobile_app', $consent->metadata['source']);
        $this->assertEquals('2.1.0', $consent->metadata['version']);
        $this->assertEquals('summer_sale', $consent->metadata['campaign']);
    }

    /** @test */
    public function it_auto_captures_ip_and_user_agent()
    {
        $user = User::factory()->create();

        $consent = $this->consentManager->recordConsent('marketing', $user);

        $this->assertNotNull($consent->ip_address);
        $this->assertNotNull($consent->user_agent);
    }

    /** @test */
    public function it_uses_provided_ip_and_user_agent()
    {
        $user = User::factory()->create();

        $consent = $this->consentManager->recordConsent(
            'marketing',
            $user,
            null,
            [],
            '192.168.1.100',
            'Custom User Agent'
        );

        $this->assertEquals('192.168.1.100', $consent->ip_address);
        $this->assertEquals('Custom User Agent', $consent->user_agent);
    }

    /** @test */
    public function it_withdraws_active_consent()
    {
        $user = User::factory()->create();

        $this->consentManager->recordConsent('marketing', $user);

        $withdrawn = $this->consentManager->withdrawConsent('marketing', $user);

        $this->assertTrue($withdrawn);

        $consent = ConsentRecord::where('user_id', $user->id)
            ->where('consent_type', 'marketing')
            ->first();

        $this->assertNotNull($consent->withdrawn_at);
    }

    /** @test */
    public function it_returns_false_when_withdrawing_non_existent_consent()
    {
        $user = User::factory()->create();

        $withdrawn = $this->consentManager->withdrawConsent('marketing', $user);

        $this->assertFalse($withdrawn);
    }

    /** @test */
    public function it_withdraws_most_recent_active_consent()
    {
        $user = User::factory()->create();

        // Create old consent
        $oldConsent = ConsentRecord::create([
            'user_id' => $user->id,
            'consent_type' => 'marketing',
            'purpose' => 'Old',
            'given_at' => now()->subDays(10),
        ]);

        // Create new consent
        $newConsent = $this->consentManager->recordConsent('marketing', $user);

        // Withdraw should affect the new consent
        $this->consentManager->withdrawConsent('marketing', $user);

        $this->assertNull($oldConsent->fresh()->withdrawn_at);
        $this->assertNotNull($newConsent->fresh()->withdrawn_at);
    }

    /** @test */
    public function it_adds_withdrawal_metadata()
    {
        $user = User::factory()->create();
        $this->consentManager->recordConsent('marketing', $user);

        $withdrawalMetadata = ['reason' => 'user_request', 'note' => 'Unsubscribe from emails'];

        $this->consentManager->withdrawConsent('marketing', $user, $withdrawalMetadata);

        $consent = ConsentRecord::where('user_id', $user->id)
            ->where('consent_type', 'marketing')
            ->first();

        $this->assertArrayHasKey('withdrawal_metadata', $consent->metadata);
        $this->assertEquals('user_request', $consent->metadata['withdrawal_metadata']['reason']);
        $this->assertEquals('Unsubscribe from emails', $consent->metadata['withdrawal_metadata']['note']);
        $this->assertArrayHasKey('withdrawn_ip', $consent->metadata);
        $this->assertArrayHasKey('withdrawn_user_agent', $consent->metadata);
    }

    /** @test */
    public function it_throws_exception_when_withdrawing_without_user()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('User ID is required to withdraw consent');

        $this->consentManager->withdrawConsent('marketing');
    }

    /** @test */
    public function it_checks_active_consent()
    {
        $user = User::factory()->create();

        $this->assertFalse($this->consentManager->checkConsent('marketing', $user));

        $this->consentManager->recordConsent('marketing', $user);

        $this->assertTrue($this->consentManager->checkConsent('marketing', $user));
    }

    /** @test */
    public function it_returns_false_for_withdrawn_consent()
    {
        $user = User::factory()->create();

        $this->consentManager->recordConsent('marketing', $user);
        $this->consentManager->withdrawConsent('marketing', $user);

        $this->assertFalse($this->consentManager->checkConsent('marketing', $user));
    }

    /** @test */
    public function it_returns_true_when_consent_management_disabled()
    {
        Config::set('compliance.consent.enabled', false);

        $user = User::factory()->create();

        $this->assertTrue($this->consentManager->checkConsent('marketing', $user));
    }

    /** @test */
    public function it_gets_consent_history_for_user()
    {
        $user = User::factory()->create();

        $this->consentManager->recordConsent('marketing', $user);
        $this->consentManager->recordConsent('analytics', $user);
        $this->consentManager->recordConsent('terms_of_service', $user);

        $history = $this->consentManager->getConsentHistory($user);

        $this->assertCount(3, $history);
    }

    /** @test */
    public function it_filters_consent_history_by_type()
    {
        $user = User::factory()->create();

        $this->consentManager->recordConsent('marketing', $user);
        $this->consentManager->recordConsent('analytics', $user);
        $this->consentManager->recordConsent('marketing', $user);

        $history = $this->consentManager->getConsentHistory($user, 'marketing');

        $this->assertCount(2, $history);
        $history->each(function ($consent) {
            $this->assertEquals('marketing', $consent->consent_type);
        });
    }

    /** @test */
    public function it_filters_consent_history_to_active_only()
    {
        $user = User::factory()->create();

        $this->consentManager->recordConsent('marketing', $user);
        $this->consentManager->recordConsent('analytics', $user);
        $this->consentManager->withdrawConsent('marketing', $user);

        $history = $this->consentManager->getConsentHistory($user, null, true);

        $this->assertCount(1, $history);
        $this->assertEquals('analytics', $history->first()->consent_type);
    }

    /** @test */
    public function it_gets_active_consents()
    {
        $user = User::factory()->create();

        $this->consentManager->recordConsent('marketing', $user);
        $this->consentManager->recordConsent('analytics', $user);
        $this->consentManager->recordConsent('terms_of_service', $user);
        $this->consentManager->withdrawConsent('marketing', $user);

        $active = $this->consentManager->getActiveConsents($user);

        $this->assertCount(2, $active);
        $this->assertTrue($active->contains('consent_type', 'analytics'));
        $this->assertTrue($active->contains('consent_type', 'terms_of_service'));
        $this->assertFalse($active->contains('consent_type', 'marketing'));
    }

    /** @test */
    public function it_checks_if_user_has_all_required_consents()
    {
        $user = User::factory()->create();

        $this->assertFalse($this->consentManager->hasRequiredConsents($user));

        $this->consentManager->recordConsent('terms_of_service', $user);

        $this->assertFalse($this->consentManager->hasRequiredConsents($user));

        $this->consentManager->recordConsent('privacy_policy', $user);

        $this->assertTrue($this->consentManager->hasRequiredConsents($user));
    }

    /** @test */
    public function it_returns_true_for_required_consents_when_disabled()
    {
        Config::set('compliance.consent.enabled', false);

        $user = User::factory()->create();

        $this->assertTrue($this->consentManager->hasRequiredConsents($user));
    }

    /** @test */
    public function it_gets_missing_required_consents()
    {
        $user = User::factory()->create();

        $missing = $this->consentManager->getMissingRequiredConsents($user);

        $this->assertCount(2, $missing);
        $this->assertContains('terms_of_service', $missing);
        $this->assertContains('privacy_policy', $missing);

        $this->consentManager->recordConsent('terms_of_service', $user);

        $missing = $this->consentManager->getMissingRequiredConsents($user);

        $this->assertCount(1, $missing);
        $this->assertContains('privacy_policy', $missing);
        $this->assertNotContains('terms_of_service', $missing);
    }

    /** @test */
    public function it_records_multiple_consents_at_once()
    {
        $user = User::factory()->create();

        $consents = $this->consentManager->recordMultipleConsents(
            ['marketing', 'analytics', 'terms_of_service'],
            $user
        );

        $this->assertCount(3, $consents);
        $this->assertEquals(3, ConsentRecord::where('user_id', $user->id)->count());
    }

    /** @test */
    public function it_records_multiple_consents_with_shared_metadata()
    {
        $user = User::factory()->create();
        $metadata = ['source' => 'onboarding', 'batch' => 'initial'];

        $consents = $this->consentManager->recordMultipleConsents(
            ['marketing', 'analytics'],
            $user,
            $metadata
        );

        $consents->each(function ($consent) use ($metadata) {
            $this->assertEquals('onboarding', $consent->metadata['source']);
            $this->assertEquals('initial', $consent->metadata['batch']);
        });
    }

    /** @test */
    public function it_withdraws_all_consents_for_user()
    {
        $user = User::factory()->create();

        $this->consentManager->recordConsent('marketing', $user);
        $this->consentManager->recordConsent('analytics', $user);
        $this->consentManager->recordConsent('terms_of_service', $user);

        $count = $this->consentManager->withdrawAllConsents($user);

        $this->assertEquals(3, $count);

        $activeCount = ConsentRecord::where('user_id', $user->id)
            ->whereNull('withdrawn_at')
            ->count();

        $this->assertEquals(0, $activeCount);
    }

    /** @test */
    public function it_adds_metadata_to_all_withdrawn_consents()
    {
        $user = User::factory()->create();

        $this->consentManager->recordConsent('marketing', $user);
        $this->consentManager->recordConsent('analytics', $user);

        $metadata = ['reason' => 'account_deletion'];

        $this->consentManager->withdrawAllConsents($user, $metadata);

        $consents = ConsentRecord::where('user_id', $user->id)->get();

        $consents->each(function ($consent) {
            $this->assertArrayHasKey('withdrawal_metadata', $consent->metadata);
            $this->assertEquals('account_deletion', $consent->metadata['withdrawal_metadata']['reason']);
        });
    }

    /** @test */
    public function it_returns_zero_when_no_consents_to_withdraw()
    {
        $user = User::factory()->create();

        $count = $this->consentManager->withdrawAllConsents($user);

        $this->assertEquals(0, $count);
    }

    /** @test */
    public function it_returns_null_when_consent_management_disabled()
    {
        Config::set('compliance.consent.enabled', false);

        $user = User::factory()->create();

        $consent = $this->consentManager->recordConsent('marketing', $user);

        $this->assertNull($consent);
    }

    /** @test */
    public function it_returns_false_when_withdrawing_with_disabled_consent_management()
    {
        Config::set('compliance.consent.enabled', false);

        $user = User::factory()->create();

        $withdrawn = $this->consentManager->withdrawConsent('marketing', $user);

        $this->assertFalse($withdrawn);
    }

    /** @test */
    public function it_returns_empty_collection_for_history_when_disabled()
    {
        Config::set('compliance.consent.enabled', false);

        $user = User::factory()->create();

        $history = $this->consentManager->getConsentHistory($user);

        $this->assertCount(0, $history);
    }

    /** @test */
    public function it_returns_zero_when_withdrawing_all_with_disabled_consent_management()
    {
        Config::set('compliance.consent.enabled', false);

        $user = User::factory()->create();

        $count = $this->consentManager->withdrawAllConsents($user);

        $this->assertEquals(0, $count);
    }

    /** @test */
    public function it_handles_null_user_gracefully_in_check_consent()
    {
        Auth::logout();

        $hasConsent = $this->consentManager->checkConsent('marketing');

        $this->assertFalse($hasConsent);
    }

    /** @test */
    public function it_handles_null_user_gracefully_in_get_consent_history()
    {
        Auth::logout();

        $history = $this->consentManager->getConsentHistory();

        $this->assertCount(0, $history);
    }

    /** @test */
    public function it_handles_null_user_gracefully_in_has_required_consents()
    {
        Auth::logout();

        $hasRequired = $this->consentManager->hasRequiredConsents();

        $this->assertFalse($hasRequired);
    }

    /** @test */
    public function it_returns_all_required_types_for_null_user_in_get_missing()
    {
        Auth::logout();

        $missing = $this->consentManager->getMissingRequiredConsents();

        $this->assertContains('terms_of_service', $missing);
        $this->assertContains('privacy_policy', $missing);
    }

    /** @test */
    public function it_handles_empty_consent_types_array()
    {
        Config::set('compliance.consent.types', []);

        $user = User::factory()->create();

        $hasRequired = $this->consentManager->hasRequiredConsents($user);
        $missing = $this->consentManager->getMissingRequiredConsents($user);

        $this->assertTrue($hasRequired);
        $this->assertEmpty($missing);
    }

    /** @test */
    public function it_handles_consent_types_without_required_flag()
    {
        Config::set('compliance.consent.types', [
            'optional_type' => [
                'description' => 'Optional',
                'purpose' => 'Test',
            ],
        ]);

        $user = User::factory()->create();

        $hasRequired = $this->consentManager->hasRequiredConsents($user);
        $missing = $this->consentManager->getMissingRequiredConsents($user);

        $this->assertTrue($hasRequired);
        $this->assertEmpty($missing);
    }

    /** @test */
    public function it_respects_compliance_enabled_flag()
    {
        Config::set('compliance.enabled', false);

        $user = User::factory()->create();

        $consent = $this->consentManager->recordConsent('marketing', $user);

        $this->assertNull($consent);
    }

    /** @test */
    public function it_handles_multiple_consent_withdrawals_gracefully()
    {
        $user = User::factory()->create();

        $this->consentManager->recordConsent('marketing', $user);

        $firstWithdrawal = $this->consentManager->withdrawConsent('marketing', $user);
        $secondWithdrawal = $this->consentManager->withdrawConsent('marketing', $user);

        $this->assertTrue($firstWithdrawal);
        $this->assertFalse($secondWithdrawal);
    }

    /** @test */
    public function it_maintains_consent_history_across_record_and_withdrawal_cycles()
    {
        $user = User::factory()->create();

        // First cycle
        $this->consentManager->recordConsent('marketing', $user);
        $this->consentManager->withdrawConsent('marketing', $user);

        // Second cycle
        $this->consentManager->recordConsent('marketing', $user);

        $history = $this->consentManager->getConsentHistory($user, 'marketing');

        $this->assertCount(2, $history);

        $active = $this->consentManager->getConsentHistory($user, 'marketing', true);

        $this->assertCount(1, $active);
    }

    /** @test */
    public function it_handles_different_user_types_correctly()
    {
        $user = User::factory()->create();

        // Test with User instance
        $consent1 = $this->consentManager->recordConsent('marketing', $user);
        $this->assertEquals($user->id, $consent1->user_id);

        // Test with user ID
        $consent2 = $this->consentManager->recordConsent('analytics', $user->id);
        $this->assertEquals($user->id, $consent2->user_id);

        // Test with authenticated user
        Auth::login($user);
        $consent3 = $this->consentManager->recordConsent('terms_of_service');
        $this->assertEquals($user->id, $consent3->user_id);
    }

    /** @test */
    public function it_preserves_existing_metadata_when_adding_withdrawal_metadata()
    {
        $user = User::factory()->create();

        $consent = $this->consentManager->recordConsent(
            'marketing',
            $user,
            null,
            ['original_key' => 'original_value']
        );

        $this->consentManager->withdrawConsent('marketing', $user, ['withdrawal_reason' => 'test']);

        $consent = $consent->fresh();

        $this->assertEquals('original_value', $consent->metadata['original_key']);
        $this->assertArrayHasKey('withdrawal_metadata', $consent->metadata);
    }
}
