<?php

namespace Tests\Unit\Notifications;

use App\Models\OnboardingProgress;
use App\Models\User;
use App\Notifications\OnboardingComplete;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnboardingCompleteTest extends TestCase
{
    use RefreshDatabase;

    protected User $testUser;
    protected OnboardingProgress $progress;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test user
        $this->testUser = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Create onboarding progress
        $this->progress = OnboardingProgress::create([
            'user_id' => $this->testUser->id,
            'current_step' => 'sample_data',
            'completed_steps' => ['company_setup', 'user_creation', 'pipeline_config', 'email_integration', 'sample_data'],
            'skipped_steps' => [],
            'is_completed' => true,
            'started_at' => now()->subHours(2),
            'completed_at' => now(),
        ]);
    }

    /** @test */
    public function it_can_be_instantiated_with_onboarding_progress()
    {
        $notification = new OnboardingComplete($this->progress);

        $this->assertInstanceOf(OnboardingComplete::class, $notification);
        $this->assertEquals($this->progress->id, $notification->progress->id);
    }

    /** @test */
    public function it_builds_email_with_correct_recipient()
    {
        $notification = new OnboardingComplete($this->progress);
        $mail = $notification->build();

        $this->assertEquals($this->testUser->email, $mail->to[0]['address']);
    }

    /** @test */
    public function it_has_correct_subject()
    {
        $notification = new OnboardingComplete($this->progress);
        $mail = $notification->build();

        $this->assertStringContainsString('Onboarding', $mail->subject);
    }

    /** @test */
    public function it_uses_correct_email_view()
    {
        $notification = new OnboardingComplete($this->progress);
        $mail = $notification->build();

        $this->assertEquals('emails.onboarding.complete', $mail->view);
    }

    /** @test */
    public function it_passes_user_name_to_view()
    {
        $notification = new OnboardingComplete($this->progress);
        $mail = $notification->build();

        $this->assertArrayHasKey('user_name', $mail->viewData);
        $this->assertEquals($this->testUser->name, $mail->viewData['user_name']);
    }

    /** @test */
    public function it_passes_completed_steps_count_to_view()
    {
        $notification = new OnboardingComplete($this->progress);
        $mail = $notification->build();

        $this->assertArrayHasKey('completed_steps', $mail->viewData);
        $this->assertEquals(5, $mail->viewData['completed_steps']);
    }

    /** @test */
    public function it_passes_skipped_steps_count_to_view()
    {
        $notification = new OnboardingComplete($this->progress);
        $mail = $notification->build();

        $this->assertArrayHasKey('skipped_steps', $mail->viewData);
        $this->assertEquals(0, $mail->viewData['skipped_steps']);
    }

    /** @test */
    public function it_passes_duration_hours_to_view()
    {
        $notification = new OnboardingComplete($this->progress);
        $mail = $notification->build();

        $this->assertArrayHasKey('duration_hours', $mail->viewData);
        $this->assertIsFloat($mail->viewData['duration_hours']);
    }

    /** @test */
    public function it_passes_progress_instance_to_view()
    {
        $notification = new OnboardingComplete($this->progress);
        $mail = $notification->build();

        $this->assertArrayHasKey('progress', $mail->viewData);
        $this->assertInstanceOf(OnboardingProgress::class, $mail->viewData['progress']);
    }

    /** @test */
    public function it_handles_progress_with_skipped_steps()
    {
        // Update progress with skipped steps
        $this->progress->update([
            'completed_steps' => ['company_setup', 'pipeline_config', 'sample_data'],
            'skipped_steps' => ['user_creation', 'email_integration'],
        ]);

        $notification = new OnboardingComplete($this->progress);
        $mail = $notification->build();

        $this->assertEquals(3, $mail->viewData['completed_steps']);
        $this->assertEquals(2, $mail->viewData['skipped_steps']);
    }
}
