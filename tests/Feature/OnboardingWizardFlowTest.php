<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use App\Models\User;
use App\Models\OnboardingProgress;
use App\Services\OnboardingService;

/**
 * End-to-end feature tests for the onboarding wizard flow.
 *
 * Tests complete wizard flow, skip functionality, and resume capability.
 */
class OnboardingWizardFlowTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $onboardingService;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test user
        $this->user = User::factory()->create();

        // Get onboarding service instance
        $this->onboardingService = app(OnboardingService::class);

        // Fake mail for email tests
        Mail::fake();

        // Enable onboarding features
        Config::set('onboarding.enabled', true);
        Config::set('onboarding.allow_skip', true);
        Config::set('onboarding.allow_restart', true);
    }

    // ============================================
    // Complete Wizard Flow Tests
    // ============================================

    /**
     * Test user can complete entire onboarding wizard.
     */
    public function test_user_can_complete_entire_onboarding_wizard()
    {
        // Step 1: Visit onboarding index
        $response = $this->actingAs($this->user, 'user')
            ->get(route('onboarding.index'));

        // Should redirect to first step (company_setup)
        $response->assertRedirect(route('onboarding.step', ['step' => 'company_setup']));

        // Step 2: Complete company_setup
        $response = $this->actingAs($this->user, 'user')
            ->post(route('onboarding.store', ['step' => 'company_setup']), [
                'company_name' => 'Acme Corporation',
                'industry' => 'technology',
                'company_size' => '11-50',
                'address' => '123 Main St, City, State 12345',
                'phone' => '+1-555-0123',
                'website' => 'https://example.com',
            ]);

        $response->assertRedirect(route('onboarding.step', ['step' => 'user_creation']));
        $response->assertSessionHas('success');

        // Verify company_setup was marked as completed
        $progress = OnboardingProgress::where('user_id', $this->user->id)->first();
        $this->assertTrue($progress->isStepCompleted('company_setup'));
        $this->assertEquals('user_creation', $progress->current_step);

        // Step 3: Complete user_creation
        $response = $this->actingAs($this->user, 'user')
            ->post(route('onboarding.store', ['step' => 'user_creation']), [
                'name' => 'John Doe',
                'email' => 'john.doe@example.com',
                'role' => 'sales_rep',
                'send_invitation' => true,
            ]);

        $response->assertRedirect(route('onboarding.step', ['step' => 'pipeline_config']));
        $response->assertSessionHas('success');

        // Verify user_creation was marked as completed
        $progress->refresh();
        $this->assertTrue($progress->isStepCompleted('user_creation'));
        $this->assertEquals('pipeline_config', $progress->current_step);

        // Step 4: Complete pipeline_config
        $response = $this->actingAs($this->user, 'user')
            ->post(route('onboarding.store', ['step' => 'pipeline_config']), [
                'pipeline_name' => 'Sales Pipeline',
                'stages' => [
                    ['name' => 'New', 'probability' => 10],
                    ['name' => 'Qualified', 'probability' => 25],
                    ['name' => 'Proposal', 'probability' => 50],
                    ['name' => 'Won', 'probability' => 100],
                ],
            ]);

        $response->assertRedirect(route('onboarding.step', ['step' => 'email_integration']));
        $response->assertSessionHas('success');

        // Verify pipeline_config was marked as completed
        $progress->refresh();
        $this->assertTrue($progress->isStepCompleted('pipeline_config'));
        $this->assertEquals('email_integration', $progress->current_step);

        // Step 5: Complete email_integration
        $response = $this->actingAs($this->user, 'user')
            ->post(route('onboarding.store', ['step' => 'email_integration']), [
                'email_provider' => 'smtp',
                'smtp_host' => 'smtp.example.com',
                'smtp_port' => 587,
                'smtp_username' => 'user@example.com',
                'smtp_password' => 'password',
                'smtp_encryption' => 'tls',
                'test_connection' => false,
            ]);

        $response->assertRedirect(route('onboarding.step', ['step' => 'sample_data']));
        $response->assertSessionHas('success');

        // Verify email_integration was marked as completed
        $progress->refresh();
        $this->assertTrue($progress->isStepCompleted('email_integration'));
        $this->assertEquals('sample_data', $progress->current_step);

        // Step 6: Complete sample_data (last step)
        $response = $this->actingAs($this->user, 'user')
            ->post(route('onboarding.store', ['step' => 'sample_data']), [
                'import_sample_data' => true,
                'include_companies' => true,
                'include_contacts' => true,
                'include_deals' => true,
            ]);

        // Should redirect to index with completion message
        $response->assertRedirect(route('onboarding.index'));
        $response->assertSessionHas('success');

        // Verify onboarding is marked as completed
        $progress->refresh();
        $this->assertTrue($progress->is_completed);
        $this->assertTrue($progress->isStepCompleted('sample_data'));
        $this->assertNotNull($progress->completed_at);
        $this->assertEquals(5, $progress->getCompletedStepsCount());
    }

    /**
     * Test user can complete wizard with minimal required data.
     */
    public function test_user_can_complete_wizard_with_minimal_data()
    {
        // Complete only required fields for company_setup
        $response = $this->actingAs($this->user, 'user')
            ->post(route('onboarding.store', ['step' => 'company_setup']), [
                'company_name' => 'Minimal Corp',
            ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        // Verify step was completed
        $progress = OnboardingProgress::where('user_id', $this->user->id)->first();
        $this->assertTrue($progress->isStepCompleted('company_setup'));
    }

    /**
     * Test validation errors prevent step completion.
     */
    public function test_validation_errors_prevent_step_completion()
    {
        // Try to submit company_setup without required field
        $response = $this->actingAs($this->user, 'user')
            ->post(route('onboarding.store', ['step' => 'company_setup']), []);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['company_name']);

        // Verify step was not completed
        $progress = OnboardingProgress::where('user_id', $this->user->id)->first();

        if ($progress) {
            $this->assertFalse($progress->isStepCompleted('company_setup'));
        }
    }

    // ============================================
    // Skip Functionality Tests
    // ============================================

    /**
     * Test user can skip skippable steps.
     */
    public function test_user_can_skip_skippable_steps()
    {
        // Start onboarding
        $this->onboardingService->startOnboarding($this->user);

        // Complete company_setup (required step)
        $this->actingAs($this->user, 'user')
            ->post(route('onboarding.store', ['step' => 'company_setup']), [
                'company_name' => 'Test Corp',
            ]);

        // Skip user_creation (skippable step)
        $response = $this->actingAs($this->user, 'user')
            ->post(route('onboarding.skip', ['step' => 'user_creation']));

        $response->assertRedirect(route('onboarding.step', ['step' => 'pipeline_config']));
        $response->assertSessionHas('info');

        // Verify step was marked as skipped
        $progress = OnboardingProgress::where('user_id', $this->user->id)->first();
        $this->assertTrue($progress->isStepSkipped('user_creation'));
        $this->assertFalse($progress->isStepCompleted('user_creation'));
        $this->assertEquals('pipeline_config', $progress->current_step);
    }

    /**
     * Test user cannot skip non-skippable steps.
     */
    public function test_user_cannot_skip_non_skippable_steps()
    {
        // Start onboarding
        $this->onboardingService->startOnboarding($this->user);

        // Try to skip company_setup (not skippable)
        $response = $this->actingAs($this->user, 'user')
            ->post(route('onboarding.skip', ['step' => 'company_setup']));

        $response->assertRedirect();
        $response->assertSessionHas('error');

        // Verify step was not skipped
        $progress = OnboardingProgress::where('user_id', $this->user->id)->first();
        $this->assertFalse($progress->isStepSkipped('company_setup'));
    }

    /**
     * Test skipping is disabled when configuration is set to false.
     */
    public function test_skipping_disabled_when_config_is_false()
    {
        // Disable skipping
        Config::set('onboarding.allow_skip', false);

        // Start onboarding
        $this->onboardingService->startOnboarding($this->user);

        // Try to skip a skippable step
        $response = $this->actingAs($this->user, 'user')
            ->post(route('onboarding.skip', ['step' => 'user_creation']));

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Skipping steps is not allowed.');

        // Verify step was not skipped
        $progress = OnboardingProgress::where('user_id', $this->user->id)->first();
        $this->assertFalse($progress->isStepSkipped('user_creation'));
    }

    /**
     * Test wizard completes when all steps are processed (completed or skipped).
     */
    public function test_wizard_completes_when_all_steps_processed()
    {
        // Start onboarding
        $this->onboardingService->startOnboarding($this->user);

        // Complete company_setup
        $this->actingAs($this->user, 'user')
            ->post(route('onboarding.store', ['step' => 'company_setup']), [
                'company_name' => 'Test Corp',
            ]);

        // Skip all remaining steps
        $this->actingAs($this->user, 'user')
            ->post(route('onboarding.skip', ['step' => 'user_creation']));

        $this->actingAs($this->user, 'user')
            ->post(route('onboarding.skip', ['step' => 'pipeline_config']));

        $this->actingAs($this->user, 'user')
            ->post(route('onboarding.skip', ['step' => 'email_integration']));

        // Last step skip should complete wizard
        $response = $this->actingAs($this->user, 'user')
            ->post(route('onboarding.skip', ['step' => 'sample_data']));

        $response->assertRedirect(route('onboarding.index'));
        $response->assertSessionHas('success');

        // Verify wizard is completed
        $progress = OnboardingProgress::where('user_id', $this->user->id)->first();
        $this->assertTrue($progress->is_completed);
        $this->assertEquals(1, $progress->getCompletedStepsCount());
        $this->assertEquals(4, $progress->getSkippedStepsCount());
    }

    // ============================================
    // Resume Capability Tests
    // ============================================

    /**
     * Test user can resume onboarding from where they left off.
     */
    public function test_user_can_resume_onboarding_from_where_they_left_off()
    {
        // Start onboarding and complete first step
        $this->onboardingService->startOnboarding($this->user);

        $this->actingAs($this->user, 'user')
            ->post(route('onboarding.store', ['step' => 'company_setup']), [
                'company_name' => 'Resume Test Corp',
            ]);

        // Verify current step is user_creation
        $progress = OnboardingProgress::where('user_id', $this->user->id)->first();
        $this->assertEquals('user_creation', $progress->current_step);

        // Simulate user leaving and returning
        // Visit onboarding index
        $response = $this->actingAs($this->user, 'user')
            ->get(route('onboarding.index'));

        // Should redirect to current step (user_creation)
        $response->assertRedirect(route('onboarding.step', ['step' => 'user_creation']));

        // User can continue from user_creation
        $response = $this->actingAs($this->user, 'user')
            ->get(route('onboarding.step', ['step' => 'user_creation']));

        $response->assertOk();
        $response->assertViewIs('onboarding.step');
    }

    /**
     * Test completed onboarding shows completion page.
     */
    public function test_completed_onboarding_shows_completion_page()
    {
        // Complete onboarding
        $this->onboardingService->startOnboarding($this->user);
        $this->onboardingService->completeOnboarding($this->user);

        // Visit onboarding index
        $response = $this->actingAs($this->user, 'user')
            ->get(route('onboarding.index'));

        // Should show completion page
        $response->assertOk();
        $response->assertViewIs('onboarding.completed');
        $response->assertViewHas('progress');
        $response->assertViewHas('summary');
    }

    /**
     * Test progress is persisted across sessions.
     */
    public function test_progress_is_persisted_across_sessions()
    {
        // Complete first two steps
        $this->onboardingService->startOnboarding($this->user);

        $this->actingAs($this->user, 'user')
            ->post(route('onboarding.store', ['step' => 'company_setup']), [
                'company_name' => 'Persist Corp',
            ]);

        $this->actingAs($this->user, 'user')
            ->post(route('onboarding.store', ['step' => 'user_creation']), [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'role' => 'admin',
            ]);

        // Get progress
        $progress = OnboardingProgress::where('user_id', $this->user->id)->first();

        // Verify progress is saved in database
        $this->assertEquals('pipeline_config', $progress->current_step);
        $this->assertEquals(2, $progress->getCompletedStepsCount());
        $this->assertTrue($progress->isStepCompleted('company_setup'));
        $this->assertTrue($progress->isStepCompleted('user_creation'));

        // Refresh progress from database
        $progress->refresh();

        // Verify data persists
        $this->assertEquals('pipeline_config', $progress->current_step);
        $this->assertEquals(2, $progress->getCompletedStepsCount());
    }

    /**
     * Test user can navigate to previously completed steps to view/edit.
     */
    public function test_user_can_navigate_to_previously_completed_steps()
    {
        // Complete first two steps
        $this->onboardingService->startOnboarding($this->user);

        $this->actingAs($this->user, 'user')
            ->post(route('onboarding.store', ['step' => 'company_setup']), [
                'company_name' => 'Nav Test Corp',
            ]);

        $this->actingAs($this->user, 'user')
            ->post(route('onboarding.store', ['step' => 'user_creation']), [
                'name' => 'Nav User',
                'email' => 'nav@example.com',
                'role' => 'admin',
            ]);

        // Navigate back to company_setup
        $response = $this->actingAs($this->user, 'user')
            ->get(route('onboarding.step', ['step' => 'company_setup']));

        $response->assertOk();
        $response->assertViewIs('onboarding.step');

        // Verify current step was updated
        $progress = OnboardingProgress::where('user_id', $this->user->id)->first();
        $this->assertEquals('company_setup', $progress->current_step);

        // Previously completed steps should still be marked as completed
        $this->assertTrue($progress->isStepCompleted('company_setup'));
        $this->assertTrue($progress->isStepCompleted('user_creation'));
    }

    // ============================================
    // Navigation Tests
    // ============================================

    /**
     * Test user can navigate to next step.
     */
    public function test_user_can_navigate_to_next_step()
    {
        // Start onboarding
        $this->onboardingService->startOnboarding($this->user);

        $response = $this->actingAs($this->user, 'user')
            ->post(route('onboarding.next'));

        $response->assertRedirect();

        $progress = OnboardingProgress::where('user_id', $this->user->id)->first();
        $this->assertEquals('user_creation', $progress->current_step);
    }

    /**
     * Test user can navigate to previous step.
     */
    public function test_user_can_navigate_to_previous_step()
    {
        // Start onboarding and move to second step
        $this->onboardingService->startOnboarding($this->user);
        $this->onboardingService->navigateToNextStep($this->user);

        $response = $this->actingAs($this->user, 'user')
            ->post(route('onboarding.previous'));

        $response->assertRedirect();

        $progress = OnboardingProgress::where('user_id', $this->user->id)->first();
        $this->assertEquals('company_setup', $progress->current_step);
    }

    /**
     * Test navigation to invalid step shows error.
     */
    public function test_navigation_to_invalid_step_shows_error()
    {
        $response = $this->actingAs($this->user, 'user')
            ->get(route('onboarding.step', ['step' => 'invalid_step']));

        $response->assertRedirect(route('onboarding.index'));
        $response->assertSessionHas('error', 'Invalid onboarding step.');
    }

    // ============================================
    // Completion Tests
    // ============================================

    /**
     * Test onboarding completion redirects to configured URL.
     */
    public function test_onboarding_completion_redirects_to_configured_url()
    {
        Config::set('onboarding.completion.redirect_to', '/custom-dashboard');
        Config::set('onboarding.completion.completion_message', 'Welcome aboard!');

        // Start and complete onboarding
        $this->onboardingService->startOnboarding($this->user);

        $response = $this->actingAs($this->user, 'user')
            ->post(route('onboarding.complete'));

        $response->assertRedirect('/custom-dashboard');
        $response->assertSessionHas('success', 'Welcome aboard!');
    }

    /**
     * Test progress summary includes all relevant data.
     */
    public function test_progress_summary_includes_all_relevant_data()
    {
        // Complete some steps
        $this->onboardingService->startOnboarding($this->user);

        $this->actingAs($this->user, 'user')
            ->post(route('onboarding.store', ['step' => 'company_setup']), [
                'company_name' => 'Summary Corp',
            ]);

        $this->actingAs($this->user, 'user')
            ->post(route('onboarding.skip', ['step' => 'user_creation']));

        // Get progress summary
        $summary = $this->onboardingService->getProgressSummary($this->user);

        // Verify summary structure
        $this->assertArrayHasKey('current_step', $summary);
        $this->assertArrayHasKey('total_steps', $summary);
        $this->assertArrayHasKey('completed_steps', $summary);
        $this->assertArrayHasKey('skipped_steps', $summary);
        $this->assertArrayHasKey('progress_percentage', $summary);
        $this->assertArrayHasKey('is_completed', $summary);

        // Verify summary data
        $this->assertEquals('pipeline_config', $summary['current_step']);
        $this->assertEquals(5, $summary['total_steps']);
        $this->assertEquals(1, count($summary['completed_steps']));
        $this->assertEquals(1, count($summary['skipped_steps']));
        $this->assertEquals(40, $summary['progress_percentage']); // 2 of 5 steps processed
        $this->assertFalse($summary['is_completed']);
    }

    // ============================================
    // Restart Tests
    // ============================================

    /**
     * Test user can restart onboarding wizard.
     */
    public function test_user_can_restart_onboarding_wizard()
    {
        // Complete onboarding
        $this->onboardingService->startOnboarding($this->user);
        $this->onboardingService->completeOnboarding($this->user);

        // Verify onboarding is completed
        $progress = OnboardingProgress::where('user_id', $this->user->id)->first();
        $this->assertTrue($progress->is_completed);

        // Restart onboarding
        $response = $this->actingAs($this->user, 'user')
            ->post(route('onboarding.restart'));

        $response->assertRedirect(route('onboarding.index'));
        $response->assertSessionHas('success');

        // Verify onboarding was reset
        $progress->refresh();
        $this->assertFalse($progress->is_completed);
        $this->assertEquals('company_setup', $progress->current_step);
        $this->assertEquals(0, $progress->getCompletedStepsCount());
        $this->assertEquals(0, $progress->getSkippedStepsCount());
    }

    /**
     * Test restart is disabled when configuration is set to false.
     */
    public function test_restart_disabled_when_config_is_false()
    {
        // Disable restart
        Config::set('onboarding.allow_restart', false);

        // Complete onboarding
        $this->onboardingService->startOnboarding($this->user);
        $this->onboardingService->completeOnboarding($this->user);

        // Try to restart
        $response = $this->actingAs($this->user, 'user')
            ->post(route('onboarding.restart'));

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Restarting onboarding is not allowed.');

        // Verify onboarding was not reset
        $progress = OnboardingProgress::where('user_id', $this->user->id)->first();
        $this->assertTrue($progress->is_completed);
    }

    // ============================================
    // Authentication Tests
    // ============================================

    /**
     * Test all onboarding routes require authentication.
     */
    public function test_all_onboarding_routes_require_authentication()
    {
        $routes = [
            ['GET', route('onboarding.index')],
            ['GET', route('onboarding.step', ['step' => 'company_setup'])],
            ['POST', route('onboarding.store', ['step' => 'company_setup'])],
            ['POST', route('onboarding.next')],
            ['POST', route('onboarding.previous')],
            ['POST', route('onboarding.skip', ['step' => 'user_creation'])],
            ['POST', route('onboarding.complete')],
            ['POST', route('onboarding.restart')],
            ['GET', route('onboarding.progress')],
            ['GET', route('onboarding.statistics')],
        ];

        foreach ($routes as [$method, $route]) {
            $response = $this->call($method, $route);
            $response->assertRedirect(route('login'));
        }
    }

    // ============================================
    // Configuration Tests
    // ============================================

    /**
     * Test onboarding wizard is disabled when configuration is set to false.
     */
    public function test_wizard_disabled_when_config_is_false()
    {
        // Disable onboarding
        Config::set('onboarding.enabled', false);

        $response = $this->actingAs($this->user, 'user')
            ->get(route('onboarding.index'));

        $response->assertRedirect('/dashboard');
        $response->assertSessionHas('info', 'Onboarding wizard is currently disabled.');
    }

    // ============================================
    // Mixed Flow Tests
    // ============================================

    /**
     * Test complete wizard flow with mixed completed and skipped steps.
     */
    public function test_complete_wizard_with_mixed_completed_and_skipped_steps()
    {
        // Start onboarding
        $this->onboardingService->startOnboarding($this->user);

        // Complete company_setup
        $this->actingAs($this->user, 'user')
            ->post(route('onboarding.store', ['step' => 'company_setup']), [
                'company_name' => 'Mixed Corp',
            ]);

        // Skip user_creation
        $this->actingAs($this->user, 'user')
            ->post(route('onboarding.skip', ['step' => 'user_creation']));

        // Complete pipeline_config
        $this->actingAs($this->user, 'user')
            ->post(route('onboarding.store', ['step' => 'pipeline_config']), [
                'pipeline_name' => 'Mixed Pipeline',
                'stages' => [
                    ['name' => 'New', 'probability' => 10],
                    ['name' => 'Won', 'probability' => 100],
                ],
            ]);

        // Skip email_integration
        $this->actingAs($this->user, 'user')
            ->post(route('onboarding.skip', ['step' => 'email_integration']));

        // Skip sample_data (last step, should complete wizard)
        $response = $this->actingAs($this->user, 'user')
            ->post(route('onboarding.skip', ['step' => 'sample_data']));

        $response->assertRedirect(route('onboarding.index'));
        $response->assertSessionHas('success');

        // Verify wizard completed with mixed steps
        $progress = OnboardingProgress::where('user_id', $this->user->id)->first();
        $this->assertTrue($progress->is_completed);
        $this->assertEquals(2, $progress->getCompletedStepsCount());
        $this->assertEquals(3, $progress->getSkippedStepsCount());
        $this->assertTrue($progress->isStepCompleted('company_setup'));
        $this->assertTrue($progress->isStepSkipped('user_creation'));
        $this->assertTrue($progress->isStepCompleted('pipeline_config'));
        $this->assertTrue($progress->isStepSkipped('email_integration'));
        $this->assertTrue($progress->isStepSkipped('sample_data'));
    }

    /**
     * Test user can update previously completed step.
     */
    public function test_user_can_update_previously_completed_step()
    {
        // Complete company_setup
        $this->onboardingService->startOnboarding($this->user);

        $this->actingAs($this->user, 'user')
            ->post(route('onboarding.store', ['step' => 'company_setup']), [
                'company_name' => 'Original Name',
            ]);

        // Navigate back to company_setup
        $this->actingAs($this->user, 'user')
            ->get(route('onboarding.step', ['step' => 'company_setup']));

        // Update company_setup with new data
        $response = $this->actingAs($this->user, 'user')
            ->post(route('onboarding.store', ['step' => 'company_setup']), [
                'company_name' => 'Updated Name',
            ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        // Verify step is still marked as completed
        $progress = OnboardingProgress::where('user_id', $this->user->id)->first();
        $this->assertTrue($progress->isStepCompleted('company_setup'));
    }
}
