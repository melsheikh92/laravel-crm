<?php

namespace App\Services\Onboarding;

use App\Contracts\WizardStepContract;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Abstract base class for wizard steps.
 *
 * This class provides common functionality for all wizard steps,
 * reducing code duplication and ensuring consistent behavior.
 * Concrete step classes should extend this class and implement
 * the required abstract methods.
 *
 * @package App\Services\Onboarding
 */
abstract class AbstractWizardStep implements WizardStepContract
{
    /**
     * The unique step identifier.
     *
     * @var string
     */
    protected string $stepId;

    /**
     * The step title.
     *
     * @var string
     */
    protected string $title;

    /**
     * The step description.
     *
     * @var string
     */
    protected string $description;

    /**
     * The step icon.
     *
     * @var string
     */
    protected string $icon;

    /**
     * Estimated minutes to complete this step.
     *
     * @var int
     */
    protected int $estimatedMinutes = 5;

    /**
     * Whether this step can be skipped.
     *
     * @var bool
     */
    protected bool $skippable = false;

    /**
     * Help text for this step.
     *
     * @var string
     */
    protected string $helpText = '';

    /**
     * Help tips for this step.
     *
     * @var array<string>
     */
    protected array $helpTips = [];

    /**
     * Validation rules for this step.
     *
     * @var array
     */
    protected array $validationRules = [];

    /**
     * Custom validation messages.
     *
     * @var array
     */
    protected array $validationMessages = [];

    /**
     * The view path for rendering this step.
     *
     * @var string
     */
    protected string $viewPath;

    /**
     * {@inheritdoc}
     */
    public function getStepId(): string
    {
        return $this->stepId;
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * {@inheritdoc}
     */
    public function getIcon(): string
    {
        return $this->icon;
    }

    /**
     * {@inheritdoc}
     */
    public function getEstimatedMinutes(): int
    {
        return $this->estimatedMinutes;
    }

    /**
     * {@inheritdoc}
     */
    public function canSkip(): bool
    {
        return $this->skippable;
    }

    /**
     * {@inheritdoc}
     */
    public function getHelpText(): string
    {
        return $this->helpText;
    }

    /**
     * {@inheritdoc}
     */
    public function getHelpTips(): array
    {
        return $this->helpTips;
    }

    /**
     * {@inheritdoc}
     */
    public function getValidationRules(): array
    {
        return $this->validationRules;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration(): array
    {
        return [
            'id' => $this->getStepId(),
            'title' => $this->getTitle(),
            'description' => $this->getDescription(),
            'icon' => $this->getIcon(),
            'estimated_minutes' => $this->getEstimatedMinutes(),
            'skippable' => $this->canSkip(),
            'help_text' => $this->getHelpText(),
            'help_tips' => $this->getHelpTips(),
            'validation_rules' => $this->getValidationRules(),
            'view_path' => $this->getViewPath(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function validate(array $data): bool
    {
        $rules = $this->getValidationRules();

        if (empty($rules)) {
            return true;
        }

        $validator = Validator::make($data, $rules, $this->validationMessages);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getViewPath(): string
    {
        return $this->viewPath ?? 'onboarding.steps.' . $this->getStepId();
    }

    /**
     * {@inheritdoc}
     */
    public function render(array $data = []): View
    {
        $viewData = array_merge([
            'step' => $this,
            'config' => $this->getConfiguration(),
        ], $data);

        return view($this->getViewPath(), $viewData);
    }

    /**
     * {@inheritdoc}
     */
    public function onComplete(array $data, $user): void
    {
        // Log step completion
        Log::info('Wizard step completed', [
            'step_id' => $this->getStepId(),
            'user_id' => is_object($user) ? $user->id : $user,
            'title' => $this->getTitle(),
        ]);

        // Subclasses can override this method to add custom behavior
    }

    /**
     * {@inheritdoc}
     */
    public function onSkip($user): void
    {
        // Log step skip
        Log::info('Wizard step skipped', [
            'step_id' => $this->getStepId(),
            'user_id' => is_object($user) ? $user->id : $user,
            'title' => $this->getTitle(),
        ]);

        // Subclasses can override this method to add custom behavior
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultData($user): array
    {
        // Default implementation returns empty array
        // Subclasses can override to provide pre-filled data
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function hasBeenCompleted($user): bool
    {
        // Default implementation - subclasses should override
        // to check actual completion status from database or other sources
        return false;
    }

    /**
     * Execute the step logic with the provided data.
     *
     * This is an abstract method that must be implemented by concrete step classes.
     * Each step should define its own business logic for processing the step data.
     *
     * @param array $data The validated step data
     * @param mixed $user The user completing the step (User model or ID)
     * @return bool True if execution was successful
     * @throws \Exception
     */
    abstract public function execute(array $data, $user): bool;

    /**
     * Get the user ID from a User model or ID.
     *
     * Helper method to normalize user parameter.
     *
     * @param mixed $user
     * @return int
     */
    protected function getUserId($user): int
    {
        return is_object($user) ? $user->id : (int) $user;
    }

    /**
     * Log an error that occurred during step execution.
     *
     * @param string $message
     * @param \Throwable $exception
     * @param mixed $user
     * @return void
     */
    protected function logError(string $message, \Throwable $exception, $user): void
    {
        Log::error($message, [
            'step_id' => $this->getStepId(),
            'user_id' => $this->getUserId($user),
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
