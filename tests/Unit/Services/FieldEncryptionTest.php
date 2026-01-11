<?php

namespace Tests\Unit\Services;

use App\Services\Compliance\FieldEncryption;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

class FieldEncryptionTest extends TestCase
{
    use RefreshDatabase;

    protected FieldEncryption $fieldEncryption;

    protected function setUp(): void
    {
        parent::setUp();

        // Enable compliance features
        Config::set('compliance.enabled', true);
        Config::set('compliance.encryption.enabled', true);
        Config::set('compliance.encryption.auto_decrypt', true);
        Config::set('compliance.encryption.algorithm', 'AES-256-CBC');

        // Instantiate the service
        $this->fieldEncryption = new FieldEncryption();
    }

    /** @test */
    public function it_encrypts_a_string_value()
    {
        $plaintext = 'sensitive data';

        $encrypted = $this->fieldEncryption->encrypt($plaintext);

        $this->assertNotNull($encrypted);
        $this->assertIsString($encrypted);
        $this->assertNotEquals($plaintext, $encrypted);
        $this->assertTrue($this->fieldEncryption->isEncryptedValue($encrypted));
    }

    /** @test */
    public function it_encrypts_and_decrypts_a_string_value()
    {
        $plaintext = 'sensitive data';

        $encrypted = $this->fieldEncryption->encrypt($plaintext);
        $decrypted = $this->fieldEncryption->decrypt($encrypted);

        $this->assertEquals($plaintext, $decrypted);
    }

    /** @test */
    public function it_returns_null_values_unchanged()
    {
        $result = $this->fieldEncryption->encrypt(null);
        $this->assertNull($result);

        $result = $this->fieldEncryption->decrypt(null);
        $this->assertNull($result);
    }

    /** @test */
    public function it_returns_empty_strings_unchanged()
    {
        $result = $this->fieldEncryption->encrypt('');
        $this->assertEquals('', $result);

        $result = $this->fieldEncryption->decrypt('');
        $this->assertEquals('', $result);
    }

    /** @test */
    public function it_encrypts_arrays_when_serialize_is_true()
    {
        $data = ['key' => 'value', 'another' => 'data'];

        $encrypted = $this->fieldEncryption->encrypt($data, true);
        $decrypted = $this->fieldEncryption->decrypt($encrypted, true);

        $this->assertIsString($encrypted);
        $this->assertEquals($data, $decrypted);
    }

    /** @test */
    public function it_encrypts_numbers()
    {
        $number = 12345;

        $encrypted = $this->fieldEncryption->encrypt($number);
        $decrypted = $this->fieldEncryption->decrypt($encrypted);

        $this->assertEquals($number, $decrypted);
    }

    /** @test */
    public function it_encrypts_multiple_fields_in_array()
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '555-1234',
            'public_field' => 'visible',
        ];

        $fieldsToEncrypt = ['email', 'phone'];

        $encrypted = $this->fieldEncryption->encryptFields($data, $fieldsToEncrypt);

        $this->assertEquals($data['name'], $encrypted['name']); // Unchanged
        $this->assertEquals($data['public_field'], $encrypted['public_field']); // Unchanged
        $this->assertNotEquals($data['email'], $encrypted['email']); // Encrypted
        $this->assertNotEquals($data['phone'], $encrypted['phone']); // Encrypted
        $this->assertTrue($this->fieldEncryption->isEncryptedValue($encrypted['email']));
        $this->assertTrue($this->fieldEncryption->isEncryptedValue($encrypted['phone']));
    }

    /** @test */
    public function it_decrypts_multiple_fields_in_array()
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '555-1234',
        ];

        $fieldsToEncrypt = ['email', 'phone'];

        $encrypted = $this->fieldEncryption->encryptFields($data, $fieldsToEncrypt);
        $decrypted = $this->fieldEncryption->decryptFields($encrypted, $fieldsToEncrypt);

        $this->assertEquals($data, $decrypted);
    }

    /** @test */
    public function it_returns_original_value_when_encryption_is_disabled()
    {
        Config::set('compliance.encryption.enabled', false);

        $plaintext = 'sensitive data';
        $encrypted = $this->fieldEncryption->encrypt($plaintext);

        $this->assertEquals($plaintext, $encrypted);
    }

    /** @test */
    public function it_returns_original_value_when_compliance_is_disabled()
    {
        Config::set('compliance.enabled', false);

        $plaintext = 'sensitive data';
        $encrypted = $this->fieldEncryption->encrypt($plaintext);

        $this->assertEquals($plaintext, $encrypted);
    }

    /** @test */
    public function it_returns_unencrypted_value_when_decrypting_plain_text()
    {
        $plaintext = 'not encrypted';

        $result = $this->fieldEncryption->decrypt($plaintext);

        $this->assertEquals($plaintext, $result);
    }

    /** @test */
    public function it_detects_encrypted_values()
    {
        $plaintext = 'test data';
        $encrypted = $this->fieldEncryption->encrypt($plaintext);

        $this->assertTrue($this->fieldEncryption->isEncryptedValue($encrypted));
        $this->assertFalse($this->fieldEncryption->isEncryptedValue($plaintext));
        $this->assertFalse($this->fieldEncryption->isEncryptedValue(12345));
        $this->assertFalse($this->fieldEncryption->isEncryptedValue(null));
    }

    /** @test */
    public function it_rotates_encryption_key()
    {
        // Generate a temporary old key
        $oldKey = base64_decode(substr(config('app.key'), 7)); // Remove 'base64:' prefix

        // Encrypt with old key
        $plaintext = 'test data';
        $encryptedWithOldKey = Crypt::encryptString(serialize($plaintext));

        // Rotate to new key (current app key)
        $rotated = $this->fieldEncryption->rotateKey($encryptedWithOldKey, $oldKey);

        $this->assertNotNull($rotated);
        $this->assertTrue($this->fieldEncryption->isEncryptedValue($rotated));

        // Should be able to decrypt with current key
        $decrypted = $this->fieldEncryption->decrypt($rotated);
        $this->assertEquals($plaintext, $decrypted);
    }

    /** @test */
    public function it_decrypts_with_specific_key()
    {
        $plaintext = 'test data';
        $key = base64_decode(substr(config('app.key'), 7)); // Remove 'base64:' prefix

        // Encrypt with the key
        $encrypted = Crypt::encryptString(serialize($plaintext));

        // Decrypt with the specific key
        $decrypted = $this->fieldEncryption->decryptWithKey($encrypted, $key);

        $this->assertEquals($plaintext, $decrypted);
    }

    /** @test */
    public function it_throws_exception_when_decrypting_with_wrong_key()
    {
        $this->expectException(DecryptException::class);

        $plaintext = 'test data';
        $encrypted = Crypt::encryptString(serialize($plaintext));

        // Generate a different key
        $wrongKey = random_bytes(32);

        $this->fieldEncryption->decryptWithKey($encrypted, $wrongKey);
    }

    /** @test */
    public function it_batch_rotates_keys_for_multiple_values()
    {
        $oldKey = base64_decode(substr(config('app.key'), 7));

        $values = [
            'field1' => Crypt::encryptString(serialize('value1')),
            'field2' => Crypt::encryptString(serialize('value2')),
            'field3' => Crypt::encryptString(serialize('value3')),
        ];

        $result = $this->fieldEncryption->batchRotateKeys($values, $oldKey);

        $this->assertArrayHasKey('rotated', $result);
        $this->assertArrayHasKey('failed', $result);
        $this->assertCount(3, $result['rotated']);
        $this->assertEmpty($result['failed']);

        // Verify all rotated values can be decrypted
        foreach ($result['rotated'] as $key => $encrypted) {
            $decrypted = $this->fieldEncryption->decrypt($encrypted);
            $this->assertEquals('value' . substr($key, -1), $decrypted);
        }
    }

    /** @test */
    public function it_tracks_failed_values_during_batch_rotation()
    {
        $oldKey = base64_decode(substr(config('app.key'), 7));
        $wrongKey = random_bytes(32);

        $values = [
            'field1' => Crypt::encryptString(serialize('value1')),
            'field2' => 'invalid encrypted data', // This will fail
            'field3' => Crypt::encryptString(serialize('value3')),
        ];

        $result = $this->fieldEncryption->batchRotateKeys($values, $oldKey);

        $this->assertCount(2, $result['rotated']); // field1 and field3
        $this->assertCount(1, $result['failed']); // field2
        $this->assertArrayHasKey('field2', $result['failed']);
    }

    /** @test */
    public function it_gets_encrypted_fields_for_model()
    {
        Config::set('compliance.encryption.encrypted_fields', [
            'User' => ['email', 'phone'],
            'SupportTicket' => ['subject', 'description'],
        ]);

        $fields = $this->fieldEncryption->getEncryptedFieldsForModel('App\Models\User');
        $this->assertEquals(['email', 'phone'], $fields);

        $fields = $this->fieldEncryption->getEncryptedFieldsForModel('App\Models\SupportTicket');
        $this->assertEquals(['subject', 'description'], $fields);
    }

    /** @test */
    public function it_returns_empty_array_for_models_without_encrypted_fields()
    {
        Config::set('compliance.encryption.encrypted_fields', []);

        $fields = $this->fieldEncryption->getEncryptedFieldsForModel('App\Models\User');
        $this->assertIsArray($fields);
        $this->assertEmpty($fields);
    }

    /** @test */
    public function it_checks_if_field_should_be_encrypted()
    {
        Config::set('compliance.encryption.encrypted_fields', [
            'User' => ['email', 'phone'],
        ]);

        $this->assertTrue($this->fieldEncryption->shouldEncryptField('App\Models\User', 'email'));
        $this->assertTrue($this->fieldEncryption->shouldEncryptField('App\Models\User', 'phone'));
        $this->assertFalse($this->fieldEncryption->shouldEncryptField('App\Models\User', 'name'));
    }

    /** @test */
    public function it_checks_if_encryption_is_enabled()
    {
        Config::set('compliance.enabled', true);
        Config::set('compliance.encryption.enabled', true);

        $this->assertTrue($this->fieldEncryption->isAutoDecryptEnabled());
    }

    /** @test */
    public function it_checks_if_auto_decrypt_is_enabled()
    {
        Config::set('compliance.encryption.auto_decrypt', true);
        $this->assertTrue($this->fieldEncryption->isAutoDecryptEnabled());

        Config::set('compliance.encryption.auto_decrypt', false);
        $this->assertFalse($this->fieldEncryption->isAutoDecryptEnabled());
    }

    /** @test */
    public function it_checks_if_key_rotation_is_enabled()
    {
        Config::set('compliance.encryption.key_rotation.enabled', true);
        $this->assertTrue($this->fieldEncryption->isKeyRotationEnabled());

        Config::set('compliance.encryption.key_rotation.enabled', false);
        $this->assertFalse($this->fieldEncryption->isKeyRotationEnabled());
    }

    /** @test */
    public function it_gets_key_rotation_days()
    {
        Config::set('compliance.encryption.key_rotation.rotation_days', 90);
        $this->assertEquals(90, $this->fieldEncryption->getKeyRotationDays());

        Config::set('compliance.encryption.key_rotation.rotation_days', 180);
        $this->assertEquals(180, $this->fieldEncryption->getKeyRotationDays());
    }

    /** @test */
    public function it_encrypts_boolean_values()
    {
        $value = true;
        $encrypted = $this->fieldEncryption->encrypt($value);
        $decrypted = $this->fieldEncryption->decrypt($encrypted);

        $this->assertTrue($decrypted);

        $value = false;
        $encrypted = $this->fieldEncryption->encrypt($value);
        $decrypted = $this->fieldEncryption->decrypt($encrypted);

        $this->assertFalse($decrypted);
    }

    /** @test */
    public function it_handles_encryption_without_serialization()
    {
        $plaintext = 'test data';

        $encrypted = $this->fieldEncryption->encrypt($plaintext, false);
        $decrypted = $this->fieldEncryption->decrypt($encrypted, false);

        $this->assertEquals($plaintext, $decrypted);
    }

    /** @test */
    public function it_handles_empty_array_in_encrypt_fields()
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ];

        $result = $this->fieldEncryption->encryptFields($data, []);

        $this->assertEquals($data, $result);
    }

    /** @test */
    public function it_handles_non_existent_fields_in_encrypt_fields()
    {
        $data = [
            'name' => 'John Doe',
        ];

        $result = $this->fieldEncryption->encryptFields($data, ['email', 'phone']);

        // Should not add non-existent fields
        $this->assertEquals($data, $result);
        $this->assertArrayNotHasKey('email', $result);
        $this->assertArrayNotHasKey('phone', $result);
    }

    /** @test */
    public function it_returns_null_on_rotation_failure()
    {
        $wrongKey = random_bytes(32);
        $invalidEncrypted = 'invalid encrypted data';

        $result = $this->fieldEncryption->rotateKey($invalidEncrypted, $wrongKey);

        $this->assertNull($result);
    }

    /** @test */
    public function it_returns_value_unchanged_when_encryption_disabled_during_rotation()
    {
        Config::set('compliance.encryption.enabled', false);

        $value = 'test data';
        $oldKey = base64_decode(substr(config('app.key'), 7));

        $result = $this->fieldEncryption->rotateKey($value, $oldKey);

        $this->assertEquals($value, $result);
    }

    /** @test */
    public function it_handles_special_characters_in_encryption()
    {
        $specialChars = "Test with special chars: !@#$%^&*()[]{}|;':\"<>?,./~`\n\t\r";

        $encrypted = $this->fieldEncryption->encrypt($specialChars);
        $decrypted = $this->fieldEncryption->decrypt($encrypted);

        $this->assertEquals($specialChars, $decrypted);
    }

    /** @test */
    public function it_handles_unicode_characters_in_encryption()
    {
        $unicode = "Unicode test: 日本語 中文 العربية Ελληνικά";

        $encrypted = $this->fieldEncryption->encrypt($unicode);
        $decrypted = $this->fieldEncryption->decrypt($encrypted);

        $this->assertEquals($unicode, $decrypted);
    }

    /** @test */
    public function it_handles_long_text_encryption()
    {
        $longText = str_repeat('This is a long text. ', 500);

        $encrypted = $this->fieldEncryption->encrypt($longText);
        $decrypted = $this->fieldEncryption->decrypt($encrypted);

        $this->assertEquals($longText, $decrypted);
    }
}
