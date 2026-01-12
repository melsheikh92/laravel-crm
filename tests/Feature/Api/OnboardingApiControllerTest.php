<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\OnboardingProgress;
use App\Services\OnboardingService;

class OnboardingApiControllerTest extends TestCase
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
    }

    /**
     * Test get progress endpoint returns user's onboarding progress.
     */
    public function test_progress_endpoint_returns_user_progress()
    {
        // Start onboarding for user
        $this->onboardingService->startOnboarding($this->user);

        // Make API request
        $response = $this->actingAs($this->user, 'user')
            ->getJson('/api/onboarding/progress');

        // Assert response structure
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'current_step',
                    'total_steps',
                    'completed_steps',
                    'skipped_steps',
                    'progress_percentage',
                ],
            ]);
    }

    /**
     * Test progress endpoint requires authentication.
     */
    public function test_progress_endpoint_requires_authentication()
    {
        $response = $this->getJson('/api/onboarding/progress');

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthenticated',
            ]);
    }

    /**
     * Test statistics endpoint returns completion statistics.
     */
    public function test_statistics_endpoint_returns_completion_stats()
    {
        // Start and complete onboarding for user
        $this->onboardingService->startOnboarding($this->user);
        $this->onboardingService->completeOnboarding($this->user);

        // Make API request
        $response = $this->actingAs($this->user, 'user')
            ->getJson('/api/onboarding/statistics');

        // Assert response structure
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total_users',
                    'completed_users',
                    'completion_rate',
                ],
            ]);
    }

    /**
     * Test validate step endpoint validates data correctly.
     */
    public function test_validate_step_endpoint_validates_data()
    {
        // Test with valid data
        $response = $this->actingAs($this->user, 'user')
            ->postJson('/api/onboarding/step/company_setup/validate', [
                'company_name' => 'Acme Corp',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    /**
     * Test validate step endpoint returns validation errors.
     */
    public function test_validate_step_endpoint_returns_validation_errors()
    {
        // Test with invalid data (missing required company_name)
        $response = $this->actingAs($this->user, 'user')
            ->postJson('/api/onboarding/step/company_setup/validate', []);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Validation failed',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'errors',
            ]);
    }

    /**
     * Test validate step endpoint rejects invalid step.
     */
    public function test_validate_step_endpoint_rejects_invalid_step()
    {
        $response = $this->actingAs($this->user, 'user')
            ->postJson('/api/onboarding/step/invalid_step/validate', []);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid step',
            ]);
    }

    /**
     * Test update step endpoint completes step with valid data.
     */
    public function test_update_step_endpoint_completes_step()
    {
        // Start onboarding
        $this->onboardingService->startOnboarding($this->user);

        // Submit company setup step
        $response = $this->actingAs($this->user, 'user')
            ->postJson('/api/onboarding/step/company_setup', [
                'company_name' => 'Acme Corporation',
                'industry' => 'Technology',
                'company_size' => '10-50',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Step completed successfully',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'progress',
                    'next_step',
                    'is_completed',
                ],
            ]);

        // Verify step was marked as completed
        $progress = OnboardingProgress::where('user_id', $this->user->id)->first();
        $this->assertTrue($progress->isStepCompleted('company_setup'));
    }

    /**
     * Test update step endpoint returns validation errors.
     */
    public function test_update_step_endpoint_validates_data()
    {
        // Start onboarding
        $this->onboardingService->startOnboarding($this->user);

        // Submit invalid data
        $response = $this->actingAs($this->user, 'user')
            ->postJson('/api/onboarding/step/company_setup', []);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Validation failed',
            ]);
    }

    /**
     * Test skip step endpoint skips step successfully.
     */
    public function test_skip_step_endpoint_skips_step()
    {
        // Start onboarding
        $this->onboardingService->startOnboarding($this->user);

        // Skip user creation step (skippable)
        $response = $this->actingAs($this->user, 'user')
            ->postJson('/api/onboarding/step/user_creation/skip');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Step skipped successfully',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'progress',
                    'next_step',
                    'is_completed',
                ],
            ]);

        // Verify step was marked as skipped
        $progress = OnboardingProgress::where('user_id', $this->user->id)->first();
        $this->assertTrue($progress->isStepSkipped('user_creation'));
    }

    /**
     * Test skip step endpoint rejects non-skippable step.
     */
    public function test_skip_step_endpoint_rejects_non_skippable_step()
    {
        // Start onboarding
        $this->onboardingService->startOnboarding($this->user);

        // Try to skip company_setup (not skippable)
        $response = $this->actingAs($this->user, 'user')
            ->postJson('/api/onboarding/step/company_setup/skip');

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'This step cannot be skipped',
            ]);
    }

    /**
     * Test next endpoint navigates to next step.
     */
    public function test_next_endpoint_navigates_to_next_step()
    {
        // Start onboarding
        $progress = $this->onboardingService->startOnboarding($this->user);
        $currentStep = $progress->current_step;

        // Navigate to next step
        $response = $this->actingAs($this->user, 'user')
            ->postJson('/api/onboarding/next');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Navigated to next step',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'current_step',
                    'progress',
                ],
            ]);

        // Verify current step changed
        $progress->refresh();
        $this->assertNotEquals($currentStep, $progress->current_step);
    }

    /**
     * Test previous endpoint navigates to previous step.
     */
    public function test_previous_endpoint_navigates_to_previous_step()
    {
        // Start onboarding and navigate to second step
        $this->onboardingService->startOnboarding($this->user);
        $this->onboardingService->navigateToNextStep($this->user);

        $progress = OnboardingProgress::where('user_id', $this->user->id)->first();
        $currentStep = $progress->current_step;

        // Navigate to previous step
        $response = $this->actingAs($this->user, 'user')
            ->postJson('/api/onboarding/previous');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Navigated to previous step',
            ]);

        // Verify current step changed
        $progress->refresh();
        $this->assertNotEquals($currentStep, $progress->current_step);
    }

    /**
     * Test complete endpoint marks onboarding as complete.
     */
    public function test_complete_endpoint_marks_onboarding_complete()
    {
        // Start onboarding
        $this->onboardingService->startOnboarding($this->user);

        // Complete endpoint
        $response = $this->actingAs($this->user, 'user')
            ->postJson('/api/onboarding/complete');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'redirect_to',
                    'progress',
                ],
            ]);

        // Verify onboarding is marked as complete
        $progress = OnboardingProgress::where('user_id', $this->user->id)->first();
        $this->assertTrue($progress->is_completed);
    }

    /**
     * Test restart endpoint resets onboarding progress.
     */
    public function test_restart_endpoint_resets_onboarding()
    {
        // Start and complete onboarding
        $this->onboardingService->startOnboarding($this->user);
        $this->onboardingService->completeOnboarding($this->user);

        // Restart endpoint
        $response = $this->actingAs($this->user, 'user')
            ->postJson('/api/onboarding/restart');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Onboarding wizard has been restarted',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'current_step',
                    'progress',
                ],
            ]);

        // Verify onboarding was reset
        $progress = OnboardingProgress::where('user_id', $this->user->id)->first();
        $this->assertFalse($progress->is_completed);
        $this->assertEquals('company_setup', $progress->current_step);
    }

    /**
     * Test all endpoints require authentication.
     */
    public function test_all_endpoints_require_authentication()
    {
        $endpoints = [
            ['GET', '/api/onboarding/progress'],
            ['GET', '/api/onboarding/statistics'],
            ['POST', '/api/onboarding/step/company_setup/validate'],
            ['POST', '/api/onboarding/step/company_setup'],
            ['POST', '/api/onboarding/step/user_creation/skip'],
            ['POST', '/api/onboarding/next'],
            ['POST', '/api/onboarding/previous'],
            ['POST', '/api/onboarding/complete'],
            ['POST', '/api/onboarding/restart'],
        ];

        foreach ($endpoints as [$method, $uri]) {
            $response = $this->json($method, $uri);
            $response->assertStatus(401)
                ->assertJson([
                    'success' => false,
                    'message' => 'Unauthenticated',
                ]);
        }
    }
}
