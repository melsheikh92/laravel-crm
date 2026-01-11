<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ComplianceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->seedDataRetentionPolicies();
        $this->displayConsentTypes();

        $this->command->info('Compliance data seeded successfully!');
    }

    /**
     * Seed default data retention policies from config.
     *
     * @return void
     */
    protected function seedDataRetentionPolicies()
    {
        $policies = config('compliance.data_retention.policies', []);

        $policyMapping = [
            'audit_logs' => 'App\Models\AuditLog',
            'consent_records' => 'App\Models\ConsentRecord',
            'deleted_users' => 'App\Models\User',
            'support_tickets' => 'App\Models\SupportTicket',
        ];

        foreach ($policyMapping as $key => $modelType) {
            if (isset($policies[$key])) {
                $policyConfig = $policies[$key];

                // Check if policy already exists
                $existingPolicy = DB::table('data_retention_policies')
                    ->where('model_type', $modelType)
                    ->first();

                if (!$existingPolicy) {
                    // Create new policy
                    DB::table('data_retention_policies')->insert([
                        'model_type' => $modelType,
                        'retention_period_days' => $policyConfig['retention_days'] ?? 2555,
                        'delete_after_days' => $policyConfig['delete_after_days'] ?? 2555,
                        'conditions' => $this->getConditionsForPolicy($key),
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $this->command->info("Created retention policy for {$modelType}");
                } else {
                    // Update existing policy to match config
                    DB::table('data_retention_policies')
                        ->where('model_type', $modelType)
                        ->update([
                            'retention_period_days' => $policyConfig['retention_days'] ?? 2555,
                            'delete_after_days' => $policyConfig['delete_after_days'] ?? 2555,
                            'conditions' => $this->getConditionsForPolicy($key),
                            'updated_at' => now(),
                        ]);

                    $this->command->info("Updated retention policy for {$modelType}");
                }
            }
        }
    }

    /**
     * Get specific conditions for each policy type.
     *
     * @param string $policyKey
     * @return string|null
     */
    protected function getConditionsForPolicy(string $policyKey): ?string
    {
        $conditions = match ($policyKey) {
            'deleted_users' => [
                // Only apply to soft-deleted users
                'deleted_at' => ['not_in' => [null]],
            ],
            default => null,
        };

        return $conditions ? json_encode($conditions) : null;
    }

    /**
     * Display available consent types from configuration.
     * Note: Consent types are config-based and consent records are created
     * when users actually give consent, so we don't seed consent records.
     *
     * @return void
     */
    protected function displayConsentTypes()
    {
        $consentTypes = config('compliance.consent.types', []);

        if (empty($consentTypes)) {
            $this->command->warn('No consent types configured.');
            return;
        }

        $this->command->info('Available consent types from configuration:');

        foreach ($consentTypes as $type => $config) {
            $required = $config['required'] ? 'Required' : 'Optional';
            $description = $config['description'] ?? $type;

            $this->command->line("  - {$type}: {$description} ({$required})");
        }

        $this->command->info('Consent types are configuration-based. Actual consent records are created when users give consent.');
    }
}
