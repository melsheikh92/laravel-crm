<?php

namespace Webkul\Admin\Listeners;

use Webkul\Activity\Repositories\ActivityRepository;

class MailConfiguration
{
    /**
     * Create a new listener instance.
     *
     * @return void
     */
    public function __construct(protected ActivityRepository $activityRepository) {}

    /**
     * Log mail configuration changes after save.
     */
    public function afterSave(): void
    {
        $configData = request()->all();

        $changedFields = $this->getChangedFields($configData);

        if (empty($changedFields)) {
            return;
        }

        // Create audit log activity
        $this->activityRepository->create([
            'type'       => 'system',
            'title'      => trans('admin::app.settings.mail-configuration.audit.configuration-updated'),
            'is_done'    => 1,
            'user_id'    => auth()->check() ? auth()->id() : null,
            'additional' => json_encode([
                'action'  => 'mail_configuration_updated',
                'changes' => $changedFields,
                'ip'      => request()->ip(),
                'timestamp' => now()->toDateTimeString(),
            ]),
        ]);
    }

    /**
     * Get changed fields with masked sensitive data.
     */
    protected function getChangedFields(array $configData): array
    {
        $changedFields = [];
        $sensitiveFields = ['password'];

        foreach ($configData as $key => $value) {
            // Skip CSRF token and non-mail config fields
            if ($key === '_token' || ! str_starts_with($key, 'email.')) {
                continue;
            }

            // Get the current value from database
            $currentValue = core()->getConfigData($key);

            // Skip if value hasn't changed (accounting for null/empty string equivalence)
            if ($currentValue == $value) {
                continue;
            }

            // Mask sensitive fields (passwords)
            $isSensitive = false;
            foreach ($sensitiveFields as $sensitiveField) {
                if (str_contains($key, $sensitiveField)) {
                    $isSensitive = true;
                    break;
                }
            }

            $changedFields[$key] = [
                'old' => $isSensitive && $currentValue ? '***masked***' : $currentValue,
                'new' => $isSensitive && $value ? '***masked***' : $value,
            ];
        }

        return $changedFields;
    }
}
