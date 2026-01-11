<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\SupportTicket;
use App\Services\Compliance\AuditLogger;
use App\Services\Compliance\FieldEncryption;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RotateEncryptionKeys extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'compliance:rotate-encryption-keys
                            {--old-key= : The old encryption key (base64 encoded)}
                            {--dry-run : Preview changes without actually rotating keys}
                            {--model= : Process only specific model type (User, SupportTicket)}
                            {--batch-size=100 : Number of records to process per batch}
                            {--stats : Show encryption statistics only}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rotate encryption keys and re-encrypt existing data';

    /**
     * The FieldEncryption service instance.
     *
     * @var FieldEncryption
     */
    protected FieldEncryption $fieldEncryption;

    /**
     * The AuditLogger service instance.
     *
     * @var AuditLogger
     */
    protected AuditLogger $auditLogger;

    /**
     * Models that use the Encryptable trait.
     *
     * @var array
     */
    protected array $encryptableModels = [
        'User' => User::class,
        'SupportTicket' => SupportTicket::class,
    ];

    /**
     * Create a new command instance.
     *
     * @param FieldEncryption $fieldEncryption
     * @param AuditLogger $auditLogger
     */
    public function __construct(FieldEncryption $fieldEncryption, AuditLogger $auditLogger)
    {
        parent::__construct();
        $this->fieldEncryption = $fieldEncryption;
        $this->auditLogger = $auditLogger;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->info('🔐 Encryption Key Rotation');
        $this->line('----------------------------');

        // Show statistics only if requested
        if ($this->option('stats')) {
            return $this->showStatistics();
        }

        // Check if encryption is enabled
        if (!config('compliance.encryption.enabled')) {
            $this->error('❌ Field encryption is disabled in configuration');
            return Command::FAILURE;
        }

        $oldKey = $this->option('old-key');
        $dryRun = $this->option('dry-run');
        $modelType = $this->option('model');
        $batchSize = (int) $this->option('batch-size');

        // Validate old key
        if (!$oldKey) {
            $this->error('❌ Old encryption key is required. Use --old-key option.');
            $this->line('Example: php artisan compliance:rotate-encryption-keys --old-key="base64:..."');
            return Command::FAILURE;
        }

        // Validate and normalize the old key
        if (!$this->validateKey($oldKey)) {
            $this->error('❌ Invalid encryption key format. Key must be base64 encoded.');
            return Command::FAILURE;
        }

        // Remove 'base64:' prefix if present
        $oldKey = str_starts_with($oldKey, 'base64:')
            ? base64_decode(substr($oldKey, 7))
            : base64_decode($oldKey);

        // Display mode information
        if ($dryRun) {
            $this->warn('Running in DRY RUN mode - no data will be modified');
        } else {
            $this->warn('⚠️  WARNING: This will modify encrypted data in the database!');
            if (!$this->confirm('Are you sure you want to proceed?')) {
                $this->info('Operation cancelled.');
                return Command::SUCCESS;
            }
        }

        if ($modelType) {
            if (!isset($this->encryptableModels[$modelType])) {
                $this->error("❌ Invalid model type: {$modelType}");
                $this->line('Available models: ' . implode(', ', array_keys($this->encryptableModels)));
                return Command::FAILURE;
            }
            $this->info("Processing model type: {$modelType}");
        } else {
            $this->info('Processing all encryptable models');
        }

        $this->line('');

        try {
            // Determine which models to process
            $modelsToProcess = $modelType
                ? [$modelType => $this->encryptableModels[$modelType]]
                : $this->encryptableModels;

            $results = [
                'total_processed' => 0,
                'total_rotated' => 0,
                'total_failed' => 0,
                'models' => [],
            ];

            // Process each model
            foreach ($modelsToProcess as $name => $modelClass) {
                $this->info("Processing {$name} records...");
                $result = $this->rotateKeysForModel($modelClass, $oldKey, $batchSize, $dryRun);

                $results['models'][$name] = $result;
                $results['total_processed'] += $result['processed'];
                $results['total_rotated'] += $result['rotated'];
                $results['total_failed'] += $result['failed'];

                $this->displayModelResults($name, $result);
                $this->line('');
            }

            // Show summary
            $this->showSummary($results, $dryRun);

            // Log the key rotation operation
            if (!$dryRun && $results['total_rotated'] > 0) {
                $this->auditLogger->logCustomEvent(
                    'encryption_key_rotated',
                    'System',
                    null,
                    [],
                    [
                        'models_processed' => array_keys($results['models']),
                        'total_records_rotated' => $results['total_rotated'],
                        'total_records_failed' => $results['total_failed'],
                    ],
                    ['compliance', 'encryption', 'key-rotation']
                );
            }

            return $results['total_failed'] > 0 ? Command::FAILURE : Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Error during key rotation: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            Log::error('Encryption key rotation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return Command::FAILURE;
        }
    }

    /**
     * Rotate encryption keys for a specific model.
     *
     * @param string $modelClass
     * @param string $oldKey
     * @param int $batchSize
     * @param bool $dryRun
     * @return array
     */
    protected function rotateKeysForModel(string $modelClass, string $oldKey, int $batchSize, bool $dryRun): array
    {
        $processed = 0;
        $rotated = 0;
        $failed = 0;
        $errors = [];

        // Get total count
        $totalRecords = $modelClass::count();

        if ($totalRecords === 0) {
            return [
                'processed' => 0,
                'rotated' => 0,
                'failed' => 0,
                'total' => 0,
                'errors' => [],
            ];
        }

        // Create progress bar
        $progressBar = $this->output->createProgressBar($totalRecords);
        $progressBar->start();

        // Process records in chunks
        $modelClass::chunk($batchSize, function ($records) use (
            $oldKey,
            $dryRun,
            &$processed,
            &$rotated,
            &$failed,
            &$errors,
            $progressBar
        ) {
            foreach ($records as $record) {
                $processed++;

                try {
                    if ($dryRun) {
                        // In dry run mode, just verify we can decrypt with old key
                        $success = $this->verifyDecryptionWithOldKey($record, $oldKey);
                    } else {
                        // Perform actual key rotation in a transaction
                        $success = DB::transaction(function () use ($record, $oldKey) {
                            return $record->rotateEncryptionKeys($oldKey);
                        });

                        // Save the record if rotation was successful
                        if ($success) {
                            $record->saveQuietly(); // Use saveQuietly to avoid triggering events
                        }
                    }

                    if ($success) {
                        $rotated++;
                    } else {
                        $failed++;
                        $errors[] = "Record ID {$record->id}: Key rotation failed";
                    }
                } catch (\Exception $e) {
                    $failed++;
                    $errors[] = "Record ID {$record->id}: {$e->getMessage()}";
                    Log::error('Key rotation failed for record', [
                        'model' => get_class($record),
                        'id' => $record->id,
                        'error' => $e->getMessage(),
                    ]);
                }

                $progressBar->advance();
            }
        });

        $progressBar->finish();
        $this->line('');

        return [
            'processed' => $processed,
            'rotated' => $rotated,
            'failed' => $failed,
            'total' => $totalRecords,
            'errors' => $errors,
        ];
    }

    /**
     * Verify that a record can be decrypted with the old key.
     *
     * @param mixed $record
     * @param string $oldKey
     * @return bool
     */
    protected function verifyDecryptionWithOldKey($record, string $oldKey): bool
    {
        try {
            // Get encrypted fields for this model
            $encryptedFields = $record->getEncryptedFields();

            foreach ($encryptedFields as $field) {
                $value = $record->getAttributeEncrypted($field);

                if ($value === null || $value === '') {
                    continue;
                }

                // Try to decrypt with old key
                if ($this->fieldEncryption->isEncryptedValue($value)) {
                    $this->fieldEncryption->decryptWithKey($value, $oldKey);
                }
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Validate encryption key format.
     *
     * @param string $key
     * @return bool
     */
    protected function validateKey(string $key): bool
    {
        // Remove 'base64:' prefix if present
        $keyValue = str_starts_with($key, 'base64:')
            ? substr($key, 7)
            : $key;

        // Check if it's valid base64
        $decoded = base64_decode($keyValue, true);

        if ($decoded === false) {
            return false;
        }

        // AES-256-CBC requires 32-byte key
        return strlen($decoded) === 32;
    }

    /**
     * Display the results for a specific model.
     *
     * @param string $modelName
     * @param array $result
     * @return void
     */
    protected function displayModelResults(string $modelName, array $result): void
    {
        $this->line("📋 <comment>{$modelName}</comment>");
        $this->line("   Total Records: {$result['total']}");
        $this->line("   Processed: {$result['processed']}");
        $this->line("   <fg=green>Rotated: {$result['rotated']}</>");

        if ($result['failed'] > 0) {
            $this->line("   <fg=red>Failed: {$result['failed']}</>");

            // Show first 5 errors
            if (!empty($result['errors'])) {
                $this->line('   <fg=red>Errors:</>');
                foreach (array_slice($result['errors'], 0, 5) as $error) {
                    $this->line("     - {$error}");
                }

                if (count($result['errors']) > 5) {
                    $remaining = count($result['errors']) - 5;
                    $this->line("     ... and {$remaining} more errors");
                }
            }
        }
    }

    /**
     * Show summary of key rotation operation.
     *
     * @param array $results
     * @param bool $dryRun
     * @return void
     */
    protected function showSummary(array $results, bool $dryRun): void
    {
        $this->line('----------------------------');
        $this->info('Summary:');
        $this->line("Models Processed: " . count($results['models']));
        $this->line("Total Records Processed: {$results['total_processed']}");

        if ($dryRun) {
            $this->line("<fg=green>Would Rotate: {$results['total_rotated']}</>");
            $this->line("<fg=red>Would Fail: {$results['total_failed']}</>");
        } else {
            $this->line("<fg=green>Successfully Rotated: {$results['total_rotated']}</>");

            if ($results['total_failed'] > 0) {
                $this->line("<fg=red>Failed: {$results['total_failed']}</>");
            }
        }

        $this->line('----------------------------');

        if ($dryRun) {
            $this->info('✅ Dry run completed successfully');
            $this->line('Run without --dry-run to perform actual key rotation');
        } else {
            if ($results['total_failed'] > 0) {
                $this->warn('⚠️  Key rotation completed with errors');
                $this->line('Please review the errors above and check the logs for more details');
            } else {
                $this->info('✅ Key rotation completed successfully');
            }
        }
    }

    /**
     * Show encryption statistics.
     *
     * @return int
     */
    protected function showStatistics(): int
    {
        try {
            $this->info('📊 Encryption Statistics');
            $this->line('');

            // Display configuration
            $this->line('Configuration:');
            $this->line("  Encryption Enabled: " . (config('compliance.encryption.enabled', true) ? 'Yes' : 'No'));
            $this->line("  Auto Decrypt: " . (config('compliance.encryption.auto_decrypt', true) ? 'Yes' : 'No'));
            $this->line("  Algorithm: " . config('compliance.encryption.algorithm', 'AES-256-CBC'));
            $this->line("  Key Rotation Enabled: " . (config('compliance.encryption.key_rotation.enabled', false) ? 'Yes' : 'No'));
            $this->line("  Rotation Period: " . config('compliance.encryption.key_rotation.rotation_days', 90) . ' days');
            $this->line('');

            $this->info('Encryptable Models:');
            $this->line('');

            $totalRecords = 0;
            foreach ($this->encryptableModels as $name => $class) {
                $model = new $class();
                $fields = $this->getEncryptedFields($model);
                $count = $class::count();
                $totalRecords += $count;

                $this->line("📋 <comment>{$name}</comment>");
                $this->line("   Encrypted Fields: " . (empty($fields) ? 'None' : implode(', ', $fields)));
                $this->line("   Total Records: {$count}");
                $this->line('');
            }

            $this->line('----------------------------');
            $this->line("Total Encryptable Records: {$totalRecords}");
            $this->line('----------------------------');

            $this->info('✅ Statistics retrieved successfully');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Error retrieving statistics: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Get encrypted fields for a model instance.
     *
     * @param mixed $model
     * @return array
     */
    protected function getEncryptedFields($model): array
    {
        // Try to call the protected getEncryptedFields method via reflection
        try {
            $reflection = new \ReflectionClass($model);
            if ($reflection->hasMethod('getEncryptedFields')) {
                $method = $reflection->getMethod('getEncryptedFields');
                $method->setAccessible(true);
                $fields = $method->invoke($model);
                if (is_array($fields)) {
                    return $fields;
                }
            }
        } catch (\ReflectionException $e) {
            // Continue to next approach
        }

        // Check if model has encrypted property using reflection
        if (property_exists($model, 'encrypted')) {
            try {
                $reflection = new \ReflectionClass($model);
                $property = $reflection->getProperty('encrypted');
                $property->setAccessible(true);
                $value = $property->getValue($model);

                if (is_array($value)) {
                    return $value;
                }
            } catch (\ReflectionException $e) {
                // Fall through to config-based lookup
            }
        }

        // Fall back to config-based encrypted fields
        return $this->fieldEncryption->getEncryptedFieldsForModel(get_class($model));
    }
}
