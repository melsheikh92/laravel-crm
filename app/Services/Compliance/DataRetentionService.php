<?php

namespace App\Services\Compliance;

use App\Models\DataRetentionPolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DataRetentionService
{
    /**
     * The AuditLogger instance for logging retention actions.
     *
     * @var AuditLogger
     */
    protected AuditLogger $auditLogger;

    /**
     * Create a new DataRetentionService instance.
     *
     * @param AuditLogger $auditLogger
     */
    public function __construct(AuditLogger $auditLogger)
    {
        $this->auditLogger = $auditLogger;
    }

    /**
     * Apply all active retention policies and process expired data.
     *
     * @param bool $dryRun Whether to perform a dry run without deleting data
     * @param string|null $modelType Optional model type to apply policies for (null for all)
     * @return array Summary of applied policies and actions taken
     */
    public function applyPolicies(bool $dryRun = false, ?string $modelType = null): array
    {
        if (!$this->isDataRetentionEnabled()) {
            return [
                'status' => 'disabled',
                'message' => 'Data retention is disabled',
                'policies_applied' => 0,
                'records_expired' => 0,
                'records_deleted' => 0,
            ];
        }

        $policies = $modelType
            ? DataRetentionPolicy::getPoliciesForModel($modelType)
            : DataRetentionPolicy::getAllActivePolicies();

        $summary = [
            'status' => 'success',
            'policies_applied' => 0,
            'records_expired' => 0,
            'records_deleted' => 0,
            'records_anonymized' => 0,
            'dry_run' => $dryRun,
            'details' => [],
        ];

        foreach ($policies as $policy) {
            try {
                $policyResult = $this->applyPolicy($policy, $dryRun);
                $summary['policies_applied']++;
                $summary['records_expired'] += $policyResult['expired_count'];
                $summary['records_deleted'] += $policyResult['deleted_count'];
                $summary['records_anonymized'] += $policyResult['anonymized_count'];
                $summary['details'][] = $policyResult;
            } catch (\Exception $e) {
                Log::error('Error applying retention policy', [
                    'policy_id' => $policy->id,
                    'model_type' => $policy->model_type,
                    'error' => $e->getMessage(),
                ]);

                $summary['details'][] = [
                    'policy_id' => $policy->id,
                    'model_type' => $policy->model_type,
                    'status' => 'error',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $summary;
    }

    /**
     * Delete expired data according to retention policies.
     *
     * @param string|null $modelType Optional model type to delete expired data for
     * @param bool $force Whether to force deletion even if auto_delete is disabled
     * @return array Summary of deletion operations
     */
    public function deleteExpiredData(?string $modelType = null, bool $force = false): array
    {
        if (!$this->isDataRetentionEnabled()) {
            return [
                'status' => 'disabled',
                'message' => 'Data retention is disabled',
                'records_deleted' => 0,
            ];
        }

        $autoDelete = Config::get('compliance.data_retention.auto_delete', false);

        if (!$autoDelete && !$force) {
            return [
                'status' => 'skipped',
                'message' => 'Auto-delete is disabled. Use force=true to override.',
                'records_deleted' => 0,
            ];
        }

        $policies = $modelType
            ? DataRetentionPolicy::getPoliciesForModel($modelType)
            : DataRetentionPolicy::getAllActivePolicies();

        $summary = [
            'status' => 'success',
            'records_deleted' => 0,
            'records_anonymized' => 0,
            'details' => [],
        ];

        foreach ($policies as $policy) {
            try {
                $deletableRecords = $policy->getDeletableRecords();

                if ($deletableRecords->isEmpty()) {
                    continue;
                }

                $result = $this->deleteRecords($policy, $deletableRecords);
                $summary['records_deleted'] += $result['deleted_count'];
                $summary['records_anonymized'] += $result['anonymized_count'];
                $summary['details'][] = $result;
            } catch (\Exception $e) {
                Log::error('Error deleting expired data', [
                    'policy_id' => $policy->id,
                    'model_type' => $policy->model_type,
                    'error' => $e->getMessage(),
                ]);

                $summary['details'][] = [
                    'policy_id' => $policy->id,
                    'model_type' => $policy->model_type,
                    'status' => 'error',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $summary;
    }

    /**
     * Get expired records according to retention policies.
     *
     * @param string|null $modelType Optional model type to get expired records for
     * @param bool $deletableOnly Whether to return only records eligible for deletion
     * @return Collection Collection of expired records grouped by policy
     */
    public function getExpiredRecords(?string $modelType = null, bool $deletableOnly = false): Collection
    {
        if (!$this->isDataRetentionEnabled()) {
            return collect();
        }

        $policies = $modelType
            ? DataRetentionPolicy::getPoliciesForModel($modelType)
            : DataRetentionPolicy::getAllActivePolicies();

        $results = collect();

        foreach ($policies as $policy) {
            $records = $deletableOnly
                ? $policy->getDeletableRecords()
                : $policy->getExpiredRecords();

            if ($records->isNotEmpty()) {
                $results->push([
                    'policy_id' => $policy->id,
                    'model_type' => $policy->model_type,
                    'retention_period_days' => $policy->retention_period_days,
                    'delete_after_days' => $policy->delete_after_days,
                    'record_count' => $records->count(),
                    'records' => $records,
                ]);
            }
        }

        return $results;
    }

    /**
     * Get statistics for all retention policies.
     *
     * @param string|null $modelType Optional model type to get statistics for
     * @return array Statistics for all policies
     */
    public function getRetentionStatistics(?string $modelType = null): array
    {
        if (!$this->isDataRetentionEnabled()) {
            return [
                'status' => 'disabled',
                'policies' => [],
            ];
        }

        $policies = $modelType
            ? DataRetentionPolicy::getPoliciesForModel($modelType)
            : DataRetentionPolicy::getAllActivePolicies();

        $statistics = [
            'status' => 'enabled',
            'total_policies' => $policies->count(),
            'total_expired_records' => 0,
            'total_deletable_records' => 0,
            'policies' => [],
        ];

        foreach ($policies as $policy) {
            $policyStats = $policy->getStatistics();
            $statistics['total_expired_records'] += $policyStats['total_expired'];
            $statistics['total_deletable_records'] += $policyStats['total_deletable'];
            $statistics['policies'][] = $policyStats;
        }

        return $statistics;
    }

    /**
     * Check if a specific record has expired according to its retention policy.
     *
     * @param Model $record The record to check
     * @return bool True if the record has expired, false otherwise
     */
    public function isRecordExpired(Model $record): bool
    {
        if (!$this->isDataRetentionEnabled()) {
            return false;
        }

        $policy = DataRetentionPolicy::getActivePolicyForModel(get_class($record));

        if (!$policy) {
            return false;
        }

        return $policy->isExpired($record);
    }

    /**
     * Check if a specific record should be deleted according to its retention policy.
     *
     * @param Model $record The record to check
     * @return bool True if the record should be deleted, false otherwise
     */
    public function shouldRecordBeDeleted(Model $record): bool
    {
        if (!$this->isDataRetentionEnabled()) {
            return false;
        }

        $policy = DataRetentionPolicy::getActivePolicyForModel(get_class($record));

        if (!$policy) {
            return false;
        }

        return $policy->shouldBeDeleted($record);
    }

    /**
     * Get the retention policy for a specific model type.
     *
     * @param string $modelType The model class name
     * @return DataRetentionPolicy|null The active policy or null if none exists
     */
    public function getPolicyForModel(string $modelType): ?DataRetentionPolicy
    {
        return DataRetentionPolicy::getActivePolicyForModel($modelType);
    }

    /**
     * Get days until a record expires.
     *
     * @param Model $record The record to check
     * @return int|null Days until expiration or null if no policy applies
     */
    public function getDaysUntilExpiration(Model $record): ?int
    {
        if (!$this->isDataRetentionEnabled()) {
            return null;
        }

        $policy = DataRetentionPolicy::getActivePolicyForModel(get_class($record));

        if (!$policy) {
            return null;
        }

        return $policy->getDaysUntilExpiration($record);
    }

    /**
     * Get days until a record should be deleted.
     *
     * @param Model $record The record to check
     * @return int|null Days until deletion or null if no policy applies
     */
    public function getDaysUntilDeletion(Model $record): ?int
    {
        if (!$this->isDataRetentionEnabled()) {
            return null;
        }

        $policy = DataRetentionPolicy::getActivePolicyForModel(get_class($record));

        if (!$policy) {
            return null;
        }

        return $policy->getDaysUntilDeletion($record);
    }

    /**
     * Protected helper methods
     */

    /**
     * Apply a single retention policy.
     *
     * @param DataRetentionPolicy $policy The policy to apply
     * @param bool $dryRun Whether to perform a dry run
     * @return array Results of applying the policy
     */
    protected function applyPolicy(DataRetentionPolicy $policy, bool $dryRun = false): array
    {
        $expiredRecords = $policy->getExpiredRecords();
        $deletableRecords = $policy->getDeletableRecords();

        $result = [
            'policy_id' => $policy->id,
            'model_type' => $policy->model_type,
            'retention_period_days' => $policy->retention_period_days,
            'delete_after_days' => $policy->delete_after_days,
            'expired_count' => $expiredRecords->count(),
            'deletable_count' => $deletableRecords->count(),
            'deleted_count' => 0,
            'anonymized_count' => 0,
            'status' => 'success',
        ];

        if ($dryRun || $deletableRecords->isEmpty()) {
            return $result;
        }

        // Delete or anonymize deletable records if auto_delete is enabled
        if (Config::get('compliance.data_retention.auto_delete', false)) {
            $deleteResult = $this->deleteRecords($policy, $deletableRecords);
            $result['deleted_count'] = $deleteResult['deleted_count'];
            $result['anonymized_count'] = $deleteResult['anonymized_count'];
        }

        return $result;
    }

    /**
     * Delete or anonymize records according to policy settings.
     *
     * @param DataRetentionPolicy $policy The retention policy
     * @param Collection $records The records to delete or anonymize
     * @return array Results of deletion operation
     */
    protected function deleteRecords(DataRetentionPolicy $policy, Collection $records): array
    {
        $preferAnonymization = Config::get('compliance.data_retention.prefer_anonymization', true);
        $deletedCount = 0;
        $anonymizedCount = 0;

        DB::beginTransaction();

        try {
            foreach ($records as $record) {
                // Log the deletion/anonymization before performing it
                $this->auditLogger->logDeletion(
                    $record,
                    null,
                    ['reason' => 'retention_policy', 'policy_id' => $policy->id],
                    ['retention_policy', 'automated_deletion']
                );

                if ($preferAnonymization && method_exists($record, 'anonymize')) {
                    // Anonymize the record if the model supports it
                    $record->anonymize();
                    $anonymizedCount++;
                } else {
                    // Hard delete or soft delete depending on model configuration
                    if (method_exists($record, 'forceDelete')) {
                        $record->forceDelete();
                    } else {
                        $record->delete();
                    }
                    $deletedCount++;
                }
            }

            DB::commit();

            Log::info('Data retention policy applied', [
                'policy_id' => $policy->id,
                'model_type' => $policy->model_type,
                'deleted_count' => $deletedCount,
                'anonymized_count' => $anonymizedCount,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error deleting records for retention policy', [
                'policy_id' => $policy->id,
                'model_type' => $policy->model_type,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }

        return [
            'policy_id' => $policy->id,
            'model_type' => $policy->model_type,
            'deleted_count' => $deletedCount,
            'anonymized_count' => $anonymizedCount,
            'status' => 'success',
        ];
    }

    /**
     * Check if data retention is enabled.
     *
     * @return bool
     */
    protected function isDataRetentionEnabled(): bool
    {
        return Config::get('compliance.enabled', true) &&
               Config::get('compliance.data_retention.enabled', true);
    }
}
