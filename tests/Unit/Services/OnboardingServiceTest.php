<?php

namespace Tests\Unit\Services;

use App\Models\OnboardingProgress;
use App\Models\User;
use App\Notifications\OnboardingComplete;
use App\Services\OnboardingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class OnboardingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected OnboardingService $onboardingService;
    protected User $testUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->onboardingService = new OnboardingService();
        $this->testUser = User::factory()->create();
    }

    // ======================
    // Step Management Tests
    // ======================

    /** @test */
    public function it_returns_all_wizard_steps()
    {
        $steps = $this->onboardingService->getSteps();

        $this->assertIsArray($steps);
        $this->assertCount(5, $steps);
        $this->assertEquals([
            'company_setup',
            'user_creation',
            'pipeline_config',
            'email_integration',
            'sample_data',
        ], $steps);
    }

    /** @test */
    public function it_returns_total_step_count()
    {
        $totalSteps = $this->onboardingService->getTotalSteps();

        $this->assertEquals(5, $totalSteps);
    }

    /** @test */
    public function it_validates_step_identifiers()
    {
        $this->assertTrue($this->onboardingService->isValidStep('company_setup'));
        $this->assertTrue($this->onboardingService->isValidStep('user_creation'));
        $this->assertTrue($this->onboardingService->isValidStep('pipeline_config'));
        $this->assertTrue($this->onboardingService->isValidStep('email_integration'));
        $this->assertTrue($this->onboardingService->isValidStep('sample_data'));

        $this->assertFalse($this->onboardingService->isValidStep('invalid_step'));
        $this->assertFalse($this->onboardingService->isValidStep(''));
    }

    /** @test */
    public function it_identifies_first_step()
    {
        $this->assertTrue($this->onboardingService->isFirstStep('company_setup'));
        $this->assertFalse($this->onboardingService->isFirstStep('user_creation'));
        $this->assertFalse($this->onboardingService->isFirstStep('sample_data'));
        $this->assertFalse($this->onboardingService->isFirstStep('invalid_step'));
    }

    /** @test */
    public function it_identifies_last_step()
    {
        $this->assertTrue($this->onboardingService->isLastStep('sample_data'));
        $this->assertFalse($this->onboardingService->isLastStep('company_setup'));
        $this->assertFalse($this->onboardingService->isLastStep('user_creation'));
        $this->assertFalse($this->onboardingService->isLastStep('invalid_step'));
    }

    /** @test */
    public function it_returns_step_details()
    {
        $details = $this->onboardingService->getStepDetails('company_setup');

        $this->assertIsArray($details);
        $this->assertEquals('company_setup', $details['id']);
        $this->assertEquals(0, $details['index']);
        $this->assertEquals(1, $details['number']);
        $this->assertEquals(5, $details['total']);
        $this->assertTrue($details['is_first']);
        $this->assertFalse($details['is_last']);
    }

    /** @test */
    public function it_returns_step_details_for_middle_step()
    {
        $details = $this->onboardingService->getStepDetails('pipeline_config');

        $this->assertEquals('pipeline_config', $details['id']);
        $this->assertEquals(2, $details['index']);
        $this->assertEquals(3, $details['number']);
        $this->assertFalse($details['is_first']);
        $this->assertFalse($details['is_last']);
    }

    /** @test */
    public function it_returns_step_details_for_last_step()
    {
        $details = $this->onboardingService->getStepDetails('sample_data');

        $this->assertEquals('sample_data', $details['id']);
        $this->assertEquals(4, $details['index']);
        $this->assertEquals(5, $details['number']);
        $this->assertFalse($details['is_first']);
        $this->assertTrue($details['is_last']);
    }

    /** @test */
    public function it_returns_null_for_invalid_step_details()
    {
        $details = $this->onboardingService->getStepDetails('invalid_step');

        $this->assertNull($details);
    }

    // ======================
    // Progress Tracking Tests
    // ======================

    /** @test */
    public function it_creates_progress_for_new_user()
    {
        $progress = $this->onboardingService->getOrCreateProgress($this->testUser);

        $this->assertInstanceOf(OnboardingProgress::class, $progress);
        $this->assertEquals($this->testUser->id, $progress->user_id);
        $this->assertFalse($progress->is_completed);
        $this->assertDatabaseHas('onboarding_progress', [
            'user_id' => $this->testUser->id,
        ]);
    }

    /** @test */
    public function it_creates_progress_using_user_id()
    {
        $progress = $this->onboardingService->getOrCreateProgress($this->testUser->id);

        $this->assertInstanceOf(OnboardingProgress::class, $progress);
        $this->assertEquals($this->testUser->id, $progress->user_id);
    }

    /** @test */
    public function it_returns_existing_progress()
    {
        // Create initial progress
        $initialProgress = $this->onboardingService->getOrCreateProgress($this->testUser);
        $initialProgress->current_step = 'user_creation';
        $initialProgress->save();

        // Get progress again
        $progress = $this->onboardingService->getOrCreateProgress($this->testUser);

        $this->assertEquals($initialProgress->id, $progress->id);
        $this->assertEquals('user_creation', $progress->current_step);
    }

    /** @test */
    public function it_gets_progress_for_existing_user()
    {
        OnboardingProgress::factory()->create([
            'user_id' => $this->testUser->id,
            'current_step' => 'pipeline_config',
        ]);

        $progress = $this->onboardingService->getProgress($this->testUser);

        $this->assertInstanceOf(OnboardingProgress::class, $progress);
        $this->assertEquals('pipeline_config', $progress->current_step);
    }

    /** @test */
    public function it_returns_null_for_user_without_progress()
    {
        $progress = $this->onboardingService->getProgress($this->testUser);

        $this->assertNull($progress);
    }

    /** @test */
    public function it_starts_onboarding_for_user()
    {
        Log::shouldReceive('info')
            ->once()
            ->with('Onboarding started', \Mockery::on(function ($arg) {
                return $arg['user_id'] === $this->testUser->id
                    && $arg['current_step'] === 'company_setup';
            }));

        $progress = $this->onboardingService->startOnboarding($this->testUser);

        $this->assertInstanceOf(OnboardingProgress::class, $progress);
        $this->assertEquals('company_setup', $progress->current_step);
        $this->assertNotNull($progress->started_at);
        $this->assertFalse($progress->is_completed);
    }

    /** @test */
    public function it_does_not_restart_completed_onboarding()
    {
        $existingProgress = OnboardingProgress::factory()->create([
            'user_id' => $this->testUser->id,
            'is_completed' => true,
            'current_step' => 'sample_data',
            'started_at' => now()->subHours(2),
        ]);

        $progress = $this->onboardingService->startOnboarding($this->testUser);

        $this->assertEquals($existingProgress->id, $progress->id);
        $this->assertTrue($progress->is_completed);
        $this->assertEquals('sample_data', $progress->current_step);
    }

    /** @test */
    public function it_does_not_restart_in_progress_onboarding()
    {
        $existingProgress = OnboardingProgress::factory()->create([
            'user_id' => $this->testUser->id,
            'current_step' => 'user_creation',
            'started_at' => now()->subHours(1),
        ]);

        $progress = $this->onboardingService->startOnboarding($this->testUser);

        $this->assertEquals($existingProgress->id, $progress->id);
        $this->assertEquals('user_creation', $progress->current_step);
    }

    /** @test */
    public function it_returns_progress_summary_for_new_user()
    {
        $summary = $this->onboardingService->getProgressSummary($this->testUser);

        $this->assertIsArray($summary);
        $this->assertFalse($summary['started']);
        $this->assertFalse($summary['completed']);
        $this->assertNull($summary['current_step']);
        $this->assertEquals([], $summary['completed_steps']);
        $this->assertEquals([], $summary['skipped_steps']);
        $this->assertEquals(0, $summary['progress_percentage']);
        $this->assertEquals(5, $summary['total_steps']);
    }

    /** @test */
    public function it_returns_progress_summary_for_in_progress_user()
    {
        $progress = OnboardingProgress::factory()->create([
            'user_id' => $this->testUser->id,
            'current_step' => 'pipeline_config',
            'completed_steps' => ['company_setup', 'user_creation'],
            'skipped_steps' => [],
            'started_at' => now()->subHours(1),
        ]);

        $summary = $this->onboardingService->getProgressSummary($this->testUser);

        $this->assertTrue($summary['started']);
        $this->assertFalse($summary['completed']);
        $this->assertEquals('pipeline_config', $summary['current_step']);
        $this->assertEquals(['company_setup', 'user_creation'], $summary['completed_steps']);
        $this->assertEquals(2, $summary['completed_count']);
        $this->assertEquals(0, $summary['skipped_count']);
        $this->assertEquals(40, $summary['progress_percentage']); // 2/5 = 40%
    }

    /** @test */
    public function it_returns_progress_summary_for_completed_user()
    {
        $progress = OnboardingProgress::factory()->create([
            'user_id' => $this->testUser->id,
            'is_completed' => true,
            'completed_steps' => ['company_setup', 'user_creation', 'pipeline_config', 'email_integration', 'sample_data'],
            'skipped_steps' => [],
            'started_at' => now()->subHours(2),
            'completed_at' => now()->subHour(),
        ]);

        $summary = $this->onboardingService->getProgressSummary($this->testUser);

        $this->assertTrue($summary['started']);
        $this->assertTrue($summary['completed']);
        $this->assertEquals(5, $summary['completed_count']);
        $this->assertEquals(100, $summary['progress_percentage']);
    }

    /** @test */
    public function it_determines_if_onboarding_should_show()
    {
        // New user should see onboarding
        $this->assertTrue($this->onboardingService->shouldShowOnboarding($this->testUser));

        // User with progress should see onboarding
        OnboardingProgress::factory()->create([
            'user_id' => $this->testUser->id,
            'is_completed' => false,
        ]);
        $this->assertTrue($this->onboardingService->shouldShowOnboarding($this->testUser));

        // User who completed should not see onboarding
        $progress = OnboardingProgress::getForUser($this->testUser->id);
        $progress->is_completed = true;
        $progress->save();
        $this->assertFalse($this->onboardingService->shouldShowOnboarding($this->testUser));
    }

    // ======================
    // Step Navigation Tests
    // ======================

    /** @test */
    public function it_gets_next_step()
    {
        $this->assertEquals('user_creation', $this->onboardingService->getNextStep('company_setup'));
        $this->assertEquals('pipeline_config', $this->onboardingService->getNextStep('user_creation'));
        $this->assertEquals('email_integration', $this->onboardingService->getNextStep('pipeline_config'));
        $this->assertEquals('sample_data', $this->onboardingService->getNextStep('email_integration'));
        $this->assertNull($this->onboardingService->getNextStep('sample_data')); // Last step
    }

    /** @test */
    public function it_returns_null_for_next_step_on_invalid_step()
    {
        $this->assertNull($this->onboardingService->getNextStep('invalid_step'));
    }

    /** @test */
    public function it_gets_previous_step()
    {
        $this->assertNull($this->onboardingService->getPreviousStep('company_setup')); // First step
        $this->assertEquals('company_setup', $this->onboardingService->getPreviousStep('user_creation'));
        $this->assertEquals('user_creation', $this->onboardingService->getPreviousStep('pipeline_config'));
        $this->assertEquals('pipeline_config', $this->onboardingService->getPreviousStep('email_integration'));
        $this->assertEquals('email_integration', $this->onboardingService->getPreviousStep('sample_data'));
    }

    /** @test */
    public function it_returns_null_for_previous_step_on_invalid_step()
    {
        $this->assertNull($this->onboardingService->getPreviousStep('invalid_step'));
    }

    /** @test */
    public function it_navigates_to_next_step()
    {
        $progress = OnboardingProgress::factory()->create([
            'user_id' => $this->testUser->id,
            'current_step' => 'company_setup',
            'started_at' => now(),
        ]);

        Log::shouldReceive('info')
            ->once()
            ->with('Navigated to next step', \Mockery::on(function ($arg) {
                return $arg['user_id'] === $this->testUser->id
                    && $arg['current_step'] === 'user_creation';
            }));

        $updatedProgress = $this->onboardingService->navigateToNextStep($this->testUser);

        $this->assertEquals('user_creation', $updatedProgress->current_step);
    }

    /** @test */
    public function it_throws_exception_navigating_next_without_progress()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Onboarding progress not found');

        $this->onboardingService->navigateToNextStep($this->testUser);
    }

    /** @test */
    public function it_throws_exception_navigating_next_when_completed()
    {
        OnboardingProgress::factory()->create([
            'user_id' => $this->testUser->id,
            'is_completed' => true,
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Onboarding is already completed');

        $this->onboardingService->navigateToNextStep($this->testUser);
    }

    /** @test */
    public function it_throws_exception_navigating_next_from_last_step()
    {
        OnboardingProgress::factory()->create([
            'user_id' => $this->testUser->id,
            'current_step' => 'sample_data',
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Already on the last step');

        $this->onboardingService->navigateToNextStep($this->testUser);
    }

    /** @test */
    public function it_navigates_to_previous_step()
    {
        $progress = OnboardingProgress::factory()->create([
            'user_id' => $this->testUser->id,
            'current_step' => 'pipeline_config',
            'started_at' => now(),
        ]);

        Log::shouldReceive('info')
            ->once()
            ->with('Navigated to previous step', \Mockery::on(function ($arg) {
                return $arg['user_id'] === $this->testUser->id
                    && $arg['current_step'] === 'user_creation';
            }));

        $updatedProgress = $this->onboardingService->navigateToPreviousStep($this->testUser);

        $this->assertEquals('user_creation', $updatedProgress->current_step);
    }

    /** @test */
    public function it_throws_exception_navigating_previous_without_progress()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Onboarding progress not found');

        $this->onboardingService->navigateToPreviousStep($this->testUser);
    }

    /** @test */
    public function it_throws_exception_navigating_previous_from_first_step()
    {
        OnboardingProgress::factory()->create([
            'user_id' => $this->testUser->id,
            'current_step' => 'company_setup',
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Already on the first step');

        $this->onboardingService->navigateToPreviousStep($this->testUser);
    }

    /** @test */
    public function it_navigates_to_specific_step()
    {
        Log::shouldReceive('info')
            ->once()
            ->with('Navigated to specific step', \Mockery::on(function ($arg) {
                return $arg['user_id'] === $this->testUser->id
                    && $arg['current_step'] === 'email_integration';
            }));

        $progress = $this->onboardingService->navigateToStep($this->testUser, 'email_integration');

        $this->assertEquals('email_integration', $progress->current_step);
    }

    /** @test */
    public function it_throws_exception_navigating_to_invalid_step()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid step: invalid_step');

        $this->onboardingService->navigateToStep($this->testUser, 'invalid_step');
    }

    // ======================
    // Step Completion Tests
    // ======================

    /** @test */
    public function it_completes_a_step()
    {
        $progress = OnboardingProgress::factory()->create([
            'user_id' => $this->testUser->id,
            'current_step' => 'company_setup',
            'started_at' => now(),
        ]);

        Log::shouldReceive('info')
            ->once()
            ->with('Step completed', \Mockery::on(function ($arg) {
                return $arg['user_id'] === $this->testUser->id
                    && $arg['step'] === 'company_setup';
            }));

        $data = [
            'company_name' => 'Test Company',
            'industry' => 'technology',
        ];

        $updatedProgress = $this->onboardingService->completeStep($this->testUser, 'company_setup', $data);

        $this->assertContains('company_setup', $updatedProgress->completed_steps);
        $this->assertEquals('user_creation', $updatedProgress->current_step);
    }

    /** @test */
    public function it_completes_step_and_moves_to_next()
    {
        $progress = OnboardingProgress::factory()->create([
            'user_id' => $this->testUser->id,
            'current_step' => 'user_creation',
            'completed_steps' => ['company_setup'],
            'started_at' => now(),
        ]);

        Log::shouldReceive('info')->once();

        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ];

        $updatedProgress = $this->onboardingService->completeStep($this->testUser, 'user_creation', $data);

        $this->assertContains('user_creation', $updatedProgress->completed_steps);
        $this->assertEquals('pipeline_config', $updatedProgress->current_step);
    }

    /** @test */
    public function it_completes_onboarding_when_last_step_completed()
    {
        Mail::fake();

        $progress = OnboardingProgress::factory()->create([
            'user_id' => $this->testUser->id,
            'current_step' => 'sample_data',
            'completed_steps' => ['company_setup', 'user_creation', 'pipeline_config', 'email_integration'],
            'started_at' => now()->subHour(),
        ]);

        Log::shouldReceive('info')->times(2); // Once for step completion, once for onboarding completion

        $data = [
            'import_sample_data' => true,
        ];

        $updatedProgress = $this->onboardingService->completeStep($this->testUser, 'sample_data', $data);

        $this->assertTrue($updatedProgress->is_completed);
        $this->assertNotNull($updatedProgress->completed_at);
        $this->assertCount(5, $updatedProgress->completed_steps);

        Mail::assertSent(OnboardingComplete::class);
    }

    /** @test */
    public function it_validates_step_data_before_completion()
    {
        OnboardingProgress::factory()->create([
            'user_id' => $this->testUser->id,
            'current_step' => 'company_setup',
        ]);

        $this->expectException(ValidationException::class);

        // Missing required company_name
        $this->onboardingService->completeStep($this->testUser, 'company_setup', [
            'industry' => 'technology',
        ]);
    }

    /** @test */
    public function it_throws_exception_completing_invalid_step()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid step: invalid_step');

        $this->onboardingService->completeStep($this->testUser, 'invalid_step');
    }

    // ======================
    // Step Skipping Tests
    // ======================

    /** @test */
    public function it_skips_a_step()
    {
        $progress = OnboardingProgress::factory()->create([
            'user_id' => $this->testUser->id,
            'current_step' => 'user_creation',
            'completed_steps' => ['company_setup'],
            'started_at' => now(),
        ]);

        Log::shouldReceive('info')
            ->once()
            ->with('Step skipped', \Mockery::on(function ($arg) {
                return $arg['user_id'] === $this->testUser->id
                    && $arg['step'] === 'user_creation';
            }));

        $updatedProgress = $this->onboardingService->skipStep($this->testUser, 'user_creation');

        $this->assertContains('user_creation', $updatedProgress->skipped_steps);
        $this->assertEquals('pipeline_config', $updatedProgress->current_step);
    }

    /** @test */
    public function it_completes_onboarding_when_last_step_skipped()
    {
        Mail::fake();

        $progress = OnboardingProgress::factory()->create([
            'user_id' => $this->testUser->id,
            'current_step' => 'sample_data',
            'completed_steps' => ['company_setup', 'user_creation', 'pipeline_config'],
            'skipped_steps' => ['email_integration'],
            'started_at' => now()->subHour(),
        ]);

        Log::shouldReceive('info')->times(2); // Once for skip, once for completion

        $updatedProgress = $this->onboardingService->skipStep($this->testUser, 'sample_data');

        $this->assertTrue($updatedProgress->is_completed);
        $this->assertNotNull($updatedProgress->completed_at);

        Mail::assertSent(OnboardingComplete::class);
    }

    /** @test */
    public function it_throws_exception_skipping_invalid_step()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid step: invalid_step');

        $this->onboardingService->skipStep($this->testUser, 'invalid_step');
    }

    // ======================
    // Onboarding Completion Tests
    // ======================

    /** @test */
    public function it_completes_onboarding()
    {
        Mail::fake();

        $progress = OnboardingProgress::factory()->create([
            'user_id' => $this->testUser->id,
            'completed_steps' => ['company_setup', 'user_creation', 'pipeline_config', 'email_integration', 'sample_data'],
            'started_at' => now()->subHours(2),
        ]);

        Log::shouldReceive('info')->times(2); // Once for completion, once for email sent

        $completedProgress = $this->onboardingService->completeOnboarding($this->testUser);

        $this->assertTrue($completedProgress->is_completed);
        $this->assertNotNull($completedProgress->completed_at);

        Mail::assertSent(OnboardingComplete::class, function ($mail) {
            return $mail->progress->user_id === $this->testUser->id;
        });
    }

    /** @test */
    public function it_handles_email_failure_gracefully_on_completion()
    {
        Mail::shouldReceive('send')
            ->once()
            ->andThrow(new \Exception('Email service unavailable'));

        Log::shouldReceive('info')->once();
        Log::shouldReceive('warning')
            ->once()
            ->with('Failed to send onboarding completion email', \Mockery::on(function ($arg) {
                return $arg['user_id'] === $this->testUser->id
                    && $arg['error'] === 'Email service unavailable';
            }));

        $progress = OnboardingProgress::factory()->create([
            'user_id' => $this->testUser->id,
            'started_at' => now()->subHour(),
        ]);

        $completedProgress = $this->onboardingService->completeOnboarding($this->testUser);

        // Onboarding should still complete even if email fails
        $this->assertTrue($completedProgress->is_completed);
    }

    /** @test */
    public function it_checks_if_onboarding_can_be_completed()
    {
        // All steps completed
        $progress = OnboardingProgress::factory()->create([
            'user_id' => $this->testUser->id,
            'completed_steps' => ['company_setup', 'user_creation', 'pipeline_config', 'email_integration', 'sample_data'],
        ]);
        $this->assertTrue($this->onboardingService->canCompleteOnboarding($progress));

        // Some completed, some skipped
        $progress2 = OnboardingProgress::factory()->create([
            'user_id' => User::factory()->create()->id,
            'completed_steps' => ['company_setup', 'pipeline_config', 'sample_data'],
            'skipped_steps' => ['user_creation', 'email_integration'],
        ]);
        $this->assertTrue($this->onboardingService->canCompleteOnboarding($progress2));

        // Not all steps processed
        $progress3 = OnboardingProgress::factory()->create([
            'user_id' => User::factory()->create()->id,
            'completed_steps' => ['company_setup', 'user_creation'],
            'skipped_steps' => [],
        ]);
        $this->assertFalse($this->onboardingService->canCompleteOnboarding($progress3));
    }

    // ======================
    // Reset Tests
    // ======================

    /** @test */
    public function it_resets_onboarding_progress()
    {
        $progress = OnboardingProgress::factory()->create([
            'user_id' => $this->testUser->id,
            'current_step' => 'sample_data',
            'completed_steps' => ['company_setup', 'user_creation', 'pipeline_config', 'email_integration'],
            'skipped_steps' => [],
            'is_completed' => true,
            'started_at' => now()->subHours(3),
            'completed_at' => now()->subHour(),
        ]);

        Log::shouldReceive('info')
            ->once()
            ->with('Onboarding reset', ['user_id' => $this->testUser->id]);

        $resetProgress = $this->onboardingService->resetOnboarding($this->testUser);

        $this->assertEquals('company_setup', $resetProgress->current_step);
        $this->assertEmpty($resetProgress->completed_steps);
        $this->assertEmpty($resetProgress->skipped_steps);
        $this->assertFalse($resetProgress->is_completed);
        $this->assertNull($resetProgress->completed_at);
    }

    // ======================
    // Validation Tests
    // ======================

    /** @test */
    public function it_validates_company_setup_data()
    {
        $validData = [
            'company_name' => 'Test Company',
            'industry' => 'technology',
        ];

        // Should not throw exception
        $progress = OnboardingProgress::factory()->create([
            'user_id' => $this->testUser->id,
            'current_step' => 'company_setup',
        ]);

        Log::shouldReceive('info')->once();

        $result = $this->onboardingService->completeStep($this->testUser, 'company_setup', $validData);

        $this->assertInstanceOf(OnboardingProgress::class, $result);
    }

    /** @test */
    public function it_validates_user_creation_data()
    {
        $validData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'role' => 'admin',
        ];

        $progress = OnboardingProgress::factory()->create([
            'user_id' => $this->testUser->id,
            'current_step' => 'user_creation',
        ]);

        Log::shouldReceive('info')->once();

        $result = $this->onboardingService->completeStep($this->testUser, 'user_creation', $validData);

        $this->assertInstanceOf(OnboardingProgress::class, $result);
    }

    /** @test */
    public function it_throws_validation_exception_for_invalid_email()
    {
        $invalidData = [
            'name' => 'John Doe',
            'email' => 'invalid-email',
        ];

        OnboardingProgress::factory()->create([
            'user_id' => $this->testUser->id,
            'current_step' => 'user_creation',
        ]);

        $this->expectException(ValidationException::class);

        $this->onboardingService->completeStep($this->testUser, 'user_creation', $invalidData);
    }

    /** @test */
    public function it_allows_empty_data_for_optional_steps()
    {
        $progress = OnboardingProgress::factory()->create([
            'user_id' => $this->testUser->id,
            'current_step' => 'email_integration',
        ]);

        Log::shouldReceive('info')->once();

        // Email integration has nullable fields
        $result = $this->onboardingService->completeStep($this->testUser, 'email_integration', []);

        $this->assertInstanceOf(OnboardingProgress::class, $result);
    }

    // ======================
    // Statistics Tests
    // ======================

    /** @test */
    public function it_returns_completion_statistics()
    {
        // Create some test data
        User::factory()->count(3)->create()->each(function ($user) {
            OnboardingProgress::factory()->create([
                'user_id' => $user->id,
                'is_completed' => true,
                'started_at' => now()->subHours(2),
                'completed_at' => now()->subHour(),
            ]);
        });

        User::factory()->count(2)->create()->each(function ($user) {
            OnboardingProgress::factory()->create([
                'user_id' => $user->id,
                'is_completed' => false,
                'started_at' => now()->subHours(1),
            ]);
        });

        $stats = $this->onboardingService->getCompletionStatistics();

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('total_users', $stats);
        $this->assertArrayHasKey('completed_users', $stats);
        $this->assertArrayHasKey('completion_rate', $stats);
        $this->assertArrayHasKey('average_completion_time_hours', $stats);

        $this->assertEquals(5, $stats['total_users']);
        $this->assertEquals(3, $stats['completed_users']);
        $this->assertEquals(60, $stats['completion_rate']); // 3/5 = 60%
    }
}
