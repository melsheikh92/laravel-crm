<?php

namespace App\Contracts;

use Illuminate\Contracts\View\View;

/**
 * Interface WizardStepContract
 *
 * Defines the contract that all onboarding wizard step implementations must follow.
 * Each wizard step should implement this interface to ensure consistent behavior
 * across the onboarding flow.
 *
 * @package App\Contracts
 */
interface WizardStepContract
{
    /**
     * Get the unique identifier for this step.
     *
     * This should match one of the step identifiers defined in the OnboardingService
     * (e.g., 'company_setup', 'user_creation', 'pipeline_config', etc.)
     *
     * @return string
     */
    public function getStepId(): string;

    /**
     * Get the step title.
     *
     * @return string
     */
    public function getTitle(): string;

    /**
     * Get the step description.
     *
     * @return string
     */
    public function getDescription(): string;

    /**
     * Get the step icon (icon class or identifier).
     *
     * @return string
     */
    public function getIcon(): string;

    /**
     * Get the estimated time in minutes to complete this step.
     *
     * @return int
     */
    public function getEstimatedMinutes(): int;

    /**
     * Check if this step can be skipped.
     *
     * @return bool
     */
    public function canSkip(): bool;

    /**
     * Get the help text for this step.
     *
     * @return string
     */
    public function getHelpText(): string;

    /**
     * Get help tips for this step.
     *
     * @return array<string>
     */
    public function getHelpTips(): array;

    /**
     * Get the complete configuration for this step.
     *
     * This should return all metadata including title, description, icon,
     * help text, fields, validation rules, etc.
     *
     * @return array
     */
    public function getConfiguration(): array;

    /**
     * Validate the step data.
     *
     * This method should validate the provided data against the step's
     * validation rules and throw a ValidationException if validation fails.
     *
     * @param array $data The data to validate
     * @return bool True if validation passes
     * @throws \Illuminate\Validation\ValidationException
     */
    public function validate(array $data): bool;

    /**
     * Get validation rules for this step.
     *
     * @return array
     */
    public function getValidationRules(): array;

    /**
     * Execute the step logic with the provided data.
     *
     * This method processes and stores the step data. It should handle
     * all business logic specific to this step (e.g., creating records,
     * updating settings, sending emails, etc.)
     *
     * @param array $data The validated step data
     * @param mixed $user The user completing the step (User model or ID)
     * @return bool True if execution was successful
     * @throws \Exception
     */
    public function execute(array $data, $user): bool;

    /**
     * Render the step view.
     *
     * This method should return the Blade view for displaying this step
     * in the wizard interface.
     *
     * @param array $data Optional data to pass to the view
     * @return View
     */
    public function render(array $data = []): View;

    /**
     * Get the view path for this step.
     *
     * @return string
     */
    public function getViewPath(): string;

    /**
     * Handle step completion.
     *
     * This method is called after the step has been successfully executed.
     * It can be used for additional actions like sending notifications,
     * logging events, or triggering subsequent processes.
     *
     * @param array $data The step data
     * @param mixed $user The user completing the step
     * @return void
     */
    public function onComplete(array $data, $user): void;

    /**
     * Handle step skip.
     *
     * This method is called when a user skips this step. It can be used
     * for logging, analytics, or setting default values.
     *
     * @param mixed $user The user skipping the step
     * @return void
     */
    public function onSkip($user): void;

    /**
     * Get default data for this step.
     *
     * This method should return default values that can be pre-filled
     * in the step form (e.g., from existing user data, system settings, etc.)
     *
     * @param mixed $user The current user
     * @return array
     */
    public function getDefaultData($user): array;

    /**
     * Check if this step has been previously completed.
     *
     * This method can be used to determine if the step should show
     * existing data or start fresh.
     *
     * @param mixed $user The current user
     * @return bool
     */
    public function hasBeenCompleted($user): bool;
}
