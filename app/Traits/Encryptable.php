<?php

namespace App\Traits;

use App\Services\Compliance\FieldEncryption;
use Illuminate\Support\Facades\App;

trait Encryptable
{
    /**
     * The field encryption service instance.
     *
     * @var FieldEncryption|null
     */
    protected static $fieldEncryptionService = null;

    /**
     * Boot the Encryptable trait.
     */
    public static function bootEncryptable()
    {
        // Initialize the field encryption service
        static::$fieldEncryptionService = App::make(FieldEncryption::class);
    }

    /**
     * Get the field encryption service instance.
     *
     * @return FieldEncryption
     */
    protected function getFieldEncryptionService(): FieldEncryption
    {
        if (static::$fieldEncryptionService === null) {
            static::$fieldEncryptionService = App::make(FieldEncryption::class);
        }

        return static::$fieldEncryptionService;
    }

    /**
     * Get the list of fields that should be encrypted.
     *
     * Models can define an $encrypted property to specify which fields
     * should be automatically encrypted and decrypted.
     *
     * @return array
     */
    protected function getEncryptedFields(): array
    {
        // Check if model defines encrypted fields
        if (property_exists($this, 'encrypted') && is_array($this->encrypted)) {
            return $this->encrypted;
        }

        // Fall back to configuration-based encrypted fields
        $service = $this->getFieldEncryptionService();
        return $service->getEncryptedFieldsForModel(get_class($this));
    }

    /**
     * Determine if the given attribute should be encrypted.
     *
     * @param string $key
     * @return bool
     */
    protected function shouldEncryptAttribute(string $key): bool
    {
        return in_array($key, $this->getEncryptedFields());
    }

    /**
     * Set a given attribute on the model.
     * Automatically encrypts the value if the field is in the encrypted list.
     *
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    public function setAttribute($key, $value)
    {
        // Only encrypt if the field should be encrypted and encryption is enabled
        if ($this->shouldEncryptAttribute($key) && $value !== null && $value !== '') {
            $service = $this->getFieldEncryptionService();

            // Only encrypt if the value is not already encrypted
            if (!$service->isEncryptedValue($value)) {
                $value = $service->encrypt($value);
            }
        }

        return parent::setAttribute($key, $value);
    }

    /**
     * Get an attribute from the model.
     * Automatically decrypts the value if the field is in the encrypted list.
     *
     * @param string $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        // Only decrypt if the field should be encrypted, has a value, and auto-decrypt is enabled
        if ($this->shouldEncryptAttribute($key) && $value !== null && $value !== '') {
            $service = $this->getFieldEncryptionService();

            // Only decrypt if auto-decryption is enabled
            if ($service->isAutoDecryptEnabled()) {
                $value = $service->decrypt($value);
            }
        }

        return $value;
    }

    /**
     * Get an attribute from the model without decryption.
     * Useful for getting the raw encrypted value.
     *
     * @param string $key
     * @return mixed
     */
    public function getAttributeEncrypted(string $key)
    {
        return parent::getAttribute($key);
    }

    /**
     * Set an attribute on the model without encryption.
     * Useful for setting already-encrypted values.
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function setAttributeEncrypted(string $key, $value)
    {
        // Temporarily remove from encrypted fields
        $encryptedFields = $this->getEncryptedFields();

        // Store original encrypted property if it exists
        $hasProperty = property_exists($this, 'encrypted');
        $originalEncrypted = $hasProperty ? $this->encrypted : null;

        // Remove field from encrypted list temporarily
        if ($hasProperty) {
            $this->encrypted = array_diff($encryptedFields, [$key]);
        }

        // Set the attribute without encryption
        parent::setAttribute($key, $value);

        // Restore original encrypted property
        if ($hasProperty) {
            $this->encrypted = $originalEncrypted;
        }

        return $this;
    }

    /**
     * Get the model's attributes array without decryption.
     * Useful for database operations where encrypted values should remain encrypted.
     *
     * @return array
     */
    public function getAttributesEncrypted(): array
    {
        $attributes = [];

        foreach ($this->attributes as $key => $value) {
            $attributes[$key] = $value;
        }

        return $attributes;
    }

    /**
     * Convert the model instance to an array with decrypted values.
     *
     * @return array
     */
    public function toArray()
    {
        // Ensure encrypted fields are decrypted when converting to array
        $array = parent::toArray();

        foreach ($this->getEncryptedFields() as $field) {
            if (array_key_exists($field, $array) && $array[$field] !== null) {
                $service = $this->getFieldEncryptionService();
                if ($service->isAutoDecryptEnabled()) {
                    $array[$field] = $service->decrypt($array[$field]);
                }
            }
        }

        return $array;
    }

    /**
     * Manually encrypt a specific field value.
     *
     * @param string $field
     * @param mixed $value
     * @return string|null
     */
    public function encryptField(string $field, $value): ?string
    {
        $service = $this->getFieldEncryptionService();
        return $service->encrypt($value);
    }

    /**
     * Manually decrypt a specific field value.
     *
     * @param string $field
     * @param mixed $value
     * @return mixed
     */
    public function decryptField(string $field, $value)
    {
        $service = $this->getFieldEncryptionService();
        return $service->decrypt($value);
    }

    /**
     * Check if a field value is encrypted.
     *
     * @param string $field
     * @return bool
     */
    public function isFieldEncrypted(string $field): bool
    {
        $value = $this->getAttributeEncrypted($field);

        if ($value === null || $value === '') {
            return false;
        }

        $service = $this->getFieldEncryptionService();
        return $service->isEncryptedValue($value);
    }

    /**
     * Re-encrypt all encrypted fields with a new encryption key.
     * Used during key rotation.
     *
     * @param string $oldKey The old encryption key (base64 encoded)
     * @return bool True if successful, false otherwise
     */
    public function rotateEncryptionKeys(string $oldKey): bool
    {
        $service = $this->getFieldEncryptionService();
        $success = true;

        foreach ($this->getEncryptedFields() as $field) {
            $encryptedValue = $this->getAttributeEncrypted($field);

            if ($encryptedValue === null || $encryptedValue === '') {
                continue;
            }

            try {
                // Rotate the key for this field
                $newValue = $service->rotateKey($encryptedValue, $oldKey);

                if ($newValue !== null) {
                    // Set the re-encrypted value
                    $this->setAttributeEncrypted($field, $newValue);
                } else {
                    $success = false;
                }
            } catch (\Exception $e) {
                $success = false;
            }
        }

        return $success;
    }
}
