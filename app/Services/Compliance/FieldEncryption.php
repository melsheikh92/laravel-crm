<?php

namespace App\Services\Compliance;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class FieldEncryption
{
    /**
     * Encrypt a single field value.
     *
     * @param mixed $value The value to encrypt
     * @param bool $serialize Whether to serialize the value before encryption
     * @return string|null The encrypted value or null if encryption is disabled/fails
     */
    public function encrypt($value, bool $serialize = true): ?string
    {
        if (!$this->isEncryptionEnabled()) {
            return $value;
        }

        if (is_null($value) || $value === '') {
            return $value;
        }

        try {
            return Crypt::encryptString($serialize ? serialize($value) : $value);
        } catch (\Exception $e) {
            Log::error('Field encryption failed', [
                'error' => $e->getMessage(),
                'value_type' => gettype($value),
            ]);

            // Return original value if encryption fails to prevent data loss
            return $value;
        }
    }

    /**
     * Decrypt a single field value.
     *
     * @param mixed $value The value to decrypt
     * @param bool $unserialize Whether to unserialize the value after decryption
     * @return mixed The decrypted value or original value if decryption fails
     */
    public function decrypt($value, bool $unserialize = true)
    {
        if (!$this->isEncryptionEnabled()) {
            return $value;
        }

        if (is_null($value) || $value === '') {
            return $value;
        }

        // If value is not encrypted (doesn't look like encrypted data), return as-is
        if (!$this->isEncryptedValue($value)) {
            return $value;
        }

        try {
            $decrypted = Crypt::decryptString($value);
            return $unserialize ? unserialize($decrypted) : $decrypted;
        } catch (DecryptException $e) {
            Log::error('Field decryption failed', [
                'error' => $e->getMessage(),
            ]);

            // Return original value if decryption fails
            return $value;
        }
    }

    /**
     * Encrypt multiple fields in an array.
     *
     * @param array $data The data array
     * @param array $fields The fields to encrypt
     * @param bool $serialize Whether to serialize values before encryption
     * @return array The data array with encrypted fields
     */
    public function encryptFields(array $data, array $fields, bool $serialize = true): array
    {
        if (!$this->isEncryptionEnabled()) {
            return $data;
        }

        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $data[$field] = $this->encrypt($data[$field], $serialize);
            }
        }

        return $data;
    }

    /**
     * Decrypt multiple fields in an array.
     *
     * @param array $data The data array
     * @param array $fields The fields to decrypt
     * @param bool $unserialize Whether to unserialize values after decryption
     * @return array The data array with decrypted fields
     */
    public function decryptFields(array $data, array $fields, bool $unserialize = true): array
    {
        if (!$this->isEncryptionEnabled()) {
            return $data;
        }

        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $data[$field] = $this->decrypt($data[$field], $unserialize);
            }
        }

        return $data;
    }

    /**
     * Rotate encryption keys by re-encrypting data with a new key.
     *
     * This method should be used in conjunction with a key rotation command
     * that handles the actual key replacement in the environment.
     *
     * @param string $encryptedValue The value encrypted with the old key
     * @param string $oldKey The old encryption key (base64 encoded)
     * @param bool $unserialize Whether the encrypted value was serialized
     * @return string|null The value re-encrypted with the current key
     */
    public function rotateKey(string $encryptedValue, string $oldKey, bool $unserialize = true): ?string
    {
        if (!$this->isEncryptionEnabled()) {
            return $encryptedValue;
        }

        if (is_null($encryptedValue) || $encryptedValue === '') {
            return $encryptedValue;
        }

        try {
            // Decrypt with old key
            $decrypted = $this->decryptWithKey($encryptedValue, $oldKey, $unserialize);

            // Re-encrypt with current key
            return $this->encrypt($decrypted, $unserialize);
        } catch (\Exception $e) {
            Log::error('Key rotation failed', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Decrypt a value using a specific encryption key.
     *
     * @param string $encryptedValue The encrypted value
     * @param string $key The encryption key (base64 encoded)
     * @param bool $unserialize Whether to unserialize after decryption
     * @return mixed The decrypted value
     * @throws DecryptException If decryption fails
     */
    public function decryptWithKey(string $encryptedValue, string $key, bool $unserialize = true)
    {
        try {
            // Create a temporary encrypter with the old key
            $cipher = Config::get('compliance.encryption.algorithm', 'AES-256-CBC');
            $encrypter = new \Illuminate\Encryption\Encrypter($key, $cipher);

            $decrypted = $encrypter->decryptString($encryptedValue);
            return $unserialize ? unserialize($decrypted) : $decrypted;
        } catch (DecryptException $e) {
            Log::error('Decryption with specific key failed', [
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Batch rotate keys for multiple encrypted values.
     *
     * @param array $encryptedValues Array of encrypted values
     * @param string $oldKey The old encryption key
     * @param bool $unserialize Whether values were serialized
     * @return array Array of re-encrypted values with their original keys
     */
    public function batchRotateKeys(array $encryptedValues, string $oldKey, bool $unserialize = true): array
    {
        $rotated = [];
        $failed = [];

        foreach ($encryptedValues as $key => $value) {
            $rotatedValue = $this->rotateKey($value, $oldKey, $unserialize);

            if ($rotatedValue !== null) {
                $rotated[$key] = $rotatedValue;
            } else {
                $failed[$key] = $value;
            }
        }

        if (!empty($failed)) {
            Log::warning('Some values failed during batch key rotation', [
                'failed_count' => count($failed),
                'total_count' => count($encryptedValues),
            ]);
        }

        return [
            'rotated' => $rotated,
            'failed' => $failed,
        ];
    }

    /**
     * Check if a value appears to be encrypted.
     *
     * @param mixed $value The value to check
     * @return bool True if the value appears to be encrypted
     */
    public function isEncryptedValue($value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        // Laravel encrypted values are base64 encoded JSON
        $decoded = base64_decode($value, true);

        if ($decoded === false) {
            return false;
        }

        $data = json_decode($decoded, true);

        // Laravel encrypted payloads have 'iv', 'value', and 'mac' keys
        return is_array($data) &&
               isset($data['iv']) &&
               isset($data['value']) &&
               isset($data['mac']);
    }

    /**
     * Get the encrypted fields configuration for a specific model.
     *
     * @param string $modelClass The model class name
     * @return array The fields that should be encrypted
     */
    public function getEncryptedFieldsForModel(string $modelClass): array
    {
        $modelName = class_basename($modelClass);
        $encryptedFields = Config::get('compliance.encryption.encrypted_fields', []);

        return $encryptedFields[$modelName] ?? [];
    }

    /**
     * Check if a specific field should be encrypted for a model.
     *
     * @param string $modelClass The model class name
     * @param string $field The field name
     * @return bool True if the field should be encrypted
     */
    public function shouldEncryptField(string $modelClass, string $field): bool
    {
        $encryptedFields = $this->getEncryptedFieldsForModel($modelClass);
        return in_array($field, $encryptedFields);
    }

    /**
     * Check if encryption is enabled.
     *
     * @return bool
     */
    protected function isEncryptionEnabled(): bool
    {
        return Config::get('compliance.enabled', true) &&
               Config::get('compliance.encryption.enabled', true);
    }

    /**
     * Check if auto-decryption is enabled.
     *
     * @return bool
     */
    public function isAutoDecryptEnabled(): bool
    {
        return Config::get('compliance.encryption.auto_decrypt', true);
    }

    /**
     * Check if key rotation is enabled.
     *
     * @return bool
     */
    public function isKeyRotationEnabled(): bool
    {
        return Config::get('compliance.encryption.key_rotation.enabled', false);
    }

    /**
     * Get the key rotation period in days.
     *
     * @return int
     */
    public function getKeyRotationDays(): int
    {
        return Config::get('compliance.encryption.key_rotation.rotation_days', 90);
    }
}
