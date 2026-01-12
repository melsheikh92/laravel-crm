<?php

namespace App\Services\Onboarding\Steps;

use App\Services\Onboarding\AbstractWizardStep;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Webkul\Admin\Notifications\User\Create as UserCreatedNotification;
use Webkul\Core\Repositories\CoreConfigRepository;
use Webkul\User\Repositories\RoleRepository;
use Webkul\User\Repositories\UserRepository;

/**
 * User Creation Step
 *
 * This step allows the administrator to add their first team member during
 * onboarding. It creates a new user account with the specified role and
 * optionally sends an invitation email to the new team member.
 *
 * @package App\Services\Onboarding\Steps
 */
class UserCreationStep extends AbstractWizardStep
{
    /**
     * Core config repository instance.
     *
     * @var CoreConfigRepository
     */
    protected CoreConfigRepository $coreConfigRepository;

    /**
     * User repository instance.
     *
     * @var UserRepository
     */
    protected UserRepository $userRepository;

    /**
     * Role repository instance.
     *
     * @var RoleRepository
     */
    protected RoleRepository $roleRepository;

    /**
     * Create a new UserCreationStep instance.
     *
     * @param CoreConfigRepository $coreConfigRepository
     * @param UserRepository $userRepository
     * @param RoleRepository $roleRepository
     */
    public function __construct(
        CoreConfigRepository $coreConfigRepository,
        UserRepository $userRepository,
        RoleRepository $roleRepository
    ) {
        $this->coreConfigRepository = $coreConfigRepository;
        $this->userRepository = $userRepository;
        $this->roleRepository = $roleRepository;

        // Load configuration from onboarding config
        $config = config('onboarding.steps.user_creation', []);
        $validation = config('onboarding.validation.user_creation', []);

        // Set step properties from config
        $this->stepId = 'user_creation';
        $this->title = $config['title'] ?? 'Add Team Members';
        $this->description = $config['description'] ?? 'Invite your first team member to collaborate';
        $this->icon = $config['icon'] ?? 'users';
        $this->estimatedMinutes = $config['estimated_minutes'] ?? 2;
        $this->skippable = $config['skippable'] ?? true;
        $this->helpText = $config['help_text'] ?? '';
        $this->helpTips = $config['help_tips'] ?? [];
        $this->validationRules = $validation;
        $this->viewPath = 'onboarding.steps.user_creation';
    }

    /**
     * Execute the user creation step.
     *
     * This method creates a new user with the specified role and optionally
     * sends an invitation email to the new team member.
     *
     * @param array $data The validated step data
     * @param mixed $user The user completing the step
     * @return bool True if execution was successful
     * @throws \Exception
     */
    public function execute(array $data, $user): bool
    {
        try {
            DB::beginTransaction();

            // Generate a random password for the new user
            $password = Str::random(16);

            // Prepare user data
            $userData = [
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => bcrypt($password),
                'role_id' => $this->resolveRoleId($data['role'] ?? null),
                'status' => 1, // Active by default
                'view_permission' => 'global', // Default permission
            ];

            // Fire before event
            Event::dispatch('settings.user.create.before');

            // Create the user
            $newUser = $this->userRepository->create($userData);

            // Fire after event
            Event::dispatch('settings.user.create.after', $newUser);

            // Send invitation email if requested
            if (($data['send_invitation'] ?? true) && $newUser) {
                try {
                    Mail::queue(new UserCreatedNotification($newUser));

                    Log::info('User invitation email queued', [
                        'step_id' => $this->getStepId(),
                        'new_user_id' => $newUser->id,
                        'new_user_email' => $newUser->email,
                    ]);
                } catch (\Exception $e) {
                    // Log but don't fail - email issues shouldn't block user creation
                    Log::warning('Failed to send user invitation email', [
                        'step_id' => $this->getStepId(),
                        'new_user_id' => $newUser->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Store completion metadata
            $this->saveConfigValue('onboarding.user_creation.user_id', $newUser->id);
            $this->saveConfigValue('onboarding.user_creation.completed_at', now()->toDateTimeString());
            $this->saveConfigValue('onboarding.user_creation.completed_by', $this->getUserId($user));

            DB::commit();

            Log::info('User creation step completed successfully', [
                'step_id' => $this->getStepId(),
                'user_id' => $this->getUserId($user),
                'new_user_id' => $newUser->id,
                'new_user_name' => $newUser->name,
            ]);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError('Failed to execute user creation step', $e, $user);

            throw $e;
        }
    }

    /**
     * Resolve the role ID from the role name.
     *
     * @param string|null $roleName
     * @return int|null
     */
    protected function resolveRoleId(?string $roleName): ?int
    {
        if (empty($roleName)) {
            // Default to the first available role (typically 'admin')
            $defaultRole = $this->roleRepository->first();

            return $defaultRole?->id;
        }

        // Try to find role by name
        $role = $this->roleRepository->findOneWhere(['name' => $roleName]);

        if ($role) {
            return $role->id;
        }

        // If role not found by exact name, try to find a similar match
        $allRoles = $this->roleRepository->all();

        foreach ($allRoles as $role) {
            if (stripos($role->name, $roleName) !== false) {
                return $role->id;
            }
        }

        // Fallback to first available role
        return $allRoles->first()?->id;
    }

    /**
     * Save a configuration value to the database.
     *
     * This method updates the config if it exists, or creates a new one.
     *
     * @param string $code The config code/key
     * @param mixed $value The config value
     * @return void
     */
    protected function saveConfigValue(string $code, mixed $value): void
    {
        $existingConfig = $this->coreConfigRepository->findOneWhere(['code' => $code]);

        if ($existingConfig) {
            $this->coreConfigRepository->update([
                'code' => $code,
                'value' => $value,
            ], $existingConfig->id);
        } else {
            $this->coreConfigRepository->create([
                'code' => $code,
                'value' => $value,
            ]);
        }
    }

    /**
     * Get default data for this step.
     *
     * Returns pre-filled data if the user creation has been completed before.
     *
     * @param mixed $user The current user
     * @return array
     */
    public function getDefaultData($user): array
    {
        $defaultData = [];

        // Get the previously created user ID
        $userIdConfig = $this->coreConfigRepository->findOneWhere([
            'code' => 'onboarding.user_creation.user_id'
        ]);

        if ($userIdConfig && !empty($userIdConfig->value)) {
            // Try to load the previously created user
            try {
                $createdUser = $this->userRepository->find($userIdConfig->value);

                if ($createdUser) {
                    $defaultData['name'] = $createdUser->name;
                    $defaultData['email'] = $createdUser->email;

                    // Get role name if available
                    if ($createdUser->role_id) {
                        $role = $this->roleRepository->find($createdUser->role_id);
                        if ($role) {
                            $defaultData['role'] = $role->name;
                        }
                    }

                    // Don't send invitation again by default
                    $defaultData['send_invitation'] = false;
                }
            } catch (\Exception $e) {
                // If user not found or error, return empty data
                Log::debug('Could not load previously created user', [
                    'user_id' => $userIdConfig->value,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $defaultData;
    }

    /**
     * Check if this step has been previously completed.
     *
     * Returns true if a user has been created during onboarding.
     *
     * @param mixed $user The current user
     * @return bool
     */
    public function hasBeenCompleted($user): bool
    {
        $userIdConfig = $this->coreConfigRepository->findOneWhere([
            'code' => 'onboarding.user_creation.user_id'
        ]);

        return $userIdConfig && !empty($userIdConfig->value);
    }

    /**
     * Handle step completion.
     *
     * Logs the completion and can be extended for additional actions.
     *
     * @param array $data The step data
     * @param mixed $user The user completing the step
     * @return void
     */
    public function onComplete(array $data, $user): void
    {
        parent::onComplete($data, $user);

        Log::info('User creation step completed', [
            'user_name' => $data['name'] ?? 'N/A',
            'user_email' => $data['email'] ?? 'N/A',
            'role' => $data['role'] ?? 'N/A',
            'invitation_sent' => $data['send_invitation'] ?? true,
        ]);
    }

    /**
     * Handle step skip.
     *
     * This step can be skipped if the administrator wants to add team
     * members later.
     *
     * @param mixed $user The user skipping the step
     * @return void
     */
    public function onSkip($user): void
    {
        parent::onSkip($user);

        Log::info('User creation step skipped', [
            'user_id' => $this->getUserId($user),
        ]);
    }
}
