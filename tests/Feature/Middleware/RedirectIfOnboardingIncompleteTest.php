<?php

use App\Models\OnboardingProgress;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create a test user
    $this->user = User::factory()->create([
        'name' => 'Test User',
        'email' => 'test@example.com',
    ]);
});

it('allows guest users to access routes without redirect', function () {
    test()->get('/landing')
        ->assertStatus(200);
});

it('redirects authenticated users without onboarding progress to wizard', function () {
    test()->actingAs($this->user)
        ->get('/dashboard')
        ->assertRedirect(route('onboarding.index'));
});

it('allows authenticated users with completed onboarding to access routes', function () {
    // Create completed onboarding progress
    OnboardingProgress::create([
        'user_id' => $this->user->id,
        'is_completed' => true,
        'completed_at' => now(),
        'started_at' => now()->subHours(2),
        'completed_steps' => ['company_setup', 'user_creation', 'pipeline_config', 'email_integration', 'sample_data'],
        'skipped_steps' => [],
    ]);

    test()->actingAs($this->user)
        ->get('/dashboard')
        ->assertStatus(200);
});

it('redirects users with incomplete onboarding to current step', function () {
    // Create incomplete onboarding progress
    OnboardingProgress::create([
        'user_id' => $this->user->id,
        'current_step' => 'pipeline_config',
        'is_completed' => false,
        'started_at' => now()->subHours(1),
        'completed_steps' => ['company_setup', 'user_creation'],
        'skipped_steps' => [],
    ]);

    test()->actingAs($this->user)
        ->get('/dashboard')
        ->assertRedirect(route('onboarding.step', 'pipeline_config'));
});

it('does not redirect when accessing onboarding routes', function () {
    test()->actingAs($this->user)
        ->get(route('onboarding.index'))
        ->assertStatus(200);
});

it('does not redirect when accessing onboarding step routes', function () {
    test()->actingAs($this->user)
        ->get(route('onboarding.step', 'company_setup'))
        ->assertStatus(200);
});

it('does not redirect for API requests', function () {
    test()->actingAs($this->user)
        ->getJson('/api/onboarding/progress')
        ->assertStatus(200);
});

it('does not redirect when onboarding is disabled in config', function () {
    config(['onboarding.enabled' => false]);

    test()->actingAs($this->user)
        ->get('/dashboard')
        ->assertStatus(200);
});

it('does not redirect when auto_trigger is disabled in config', function () {
    config(['onboarding.auto_trigger' => false]);

    test()->actingAs($this->user)
        ->get('/dashboard')
        ->assertStatus(200);
});

it('does not redirect when accessing login routes', function () {
    test()->get('/login')
        ->assertStatus(200);
});

it('does not redirect when accessing password reset routes', function () {
    test()->get('/password/reset')
        ->assertStatus(200);
});

it('does not redirect when accessing consent routes', function () {
    test()->actingAs($this->user)
        ->get('/consent/required')
        ->assertStatus(200);
});

it('stores intended URL when redirecting to onboarding', function () {
    test()->actingAs($this->user)
        ->get('/compliance/dashboard')
        ->assertRedirect(route('onboarding.index'));

    expect(session('onboarding.intended_url'))->toContain('/compliance/dashboard');
});

it('does not store dashboard URL as intended URL', function () {
    test()->actingAs($this->user)
        ->get('/dashboard')
        ->assertRedirect(route('onboarding.index'));

    expect(session('onboarding.intended_url'))->toBeNull();
});

it('creates onboarding progress for new users automatically', function () {
    // Ensure no progress exists
    expect(OnboardingProgress::where('user_id', $this->user->id)->exists())->toBeFalse();

    // Access a protected route
    test()->actingAs($this->user)
        ->get('/dashboard')
        ->assertRedirect(route('onboarding.index'));

    // Verify progress was created
    expect(OnboardingProgress::where('user_id', $this->user->id)->exists())->toBeTrue();
});

it('redirects to onboarding index when no current step is set', function () {
    OnboardingProgress::create([
        'user_id' => $this->user->id,
        'current_step' => null,
        'is_completed' => false,
        'started_at' => now(),
        'completed_steps' => [],
        'skipped_steps' => [],
    ]);

    test()->actingAs($this->user)
        ->get('/dashboard')
        ->assertRedirect(route('onboarding.index'));
});

it('displays info message when redirecting to onboarding', function () {
    test()->actingAs($this->user)
        ->get('/dashboard')
        ->assertRedirect(route('onboarding.index'))
        ->assertSessionHas('info');
});

it('displays info message when redirecting to resume onboarding', function () {
    OnboardingProgress::create([
        'user_id' => $this->user->id,
        'current_step' => 'email_integration',
        'is_completed' => false,
        'started_at' => now()->subHours(1),
        'completed_steps' => ['company_setup', 'user_creation', 'pipeline_config'],
        'skipped_steps' => [],
    ]);

    test()->actingAs($this->user)
        ->get('/dashboard')
        ->assertRedirect(route('onboarding.step', 'email_integration'))
        ->assertSessionHas('info', 'Please complete the setup wizard to continue.');
});

it('allows access to excluded routes like demo request', function () {
    test()->post('/demo-request', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'company' => 'Test Company',
    ])
    ->assertStatus(302); // Redirects after successful submission or validation error
});
