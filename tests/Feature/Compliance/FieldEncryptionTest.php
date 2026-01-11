<?php

use App\Models\User;
use App\Models\SupportTicket;
use App\Services\Compliance\FieldEncryption;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;

beforeEach(function () {
    // Enable compliance features for all tests
    Config::set('compliance.enabled', true);
    Config::set('compliance.encryption.enabled', true);
    Config::set('compliance.encryption.auto_decrypt', true);
    Config::set('compliance.encryption.algorithm', 'AES-256-CBC');
});

// ============================================
// Encryptable Trait - User Model Tests
// ============================================

it('automatically encrypts user email field on create', function () {
    $user = User::factory()->create([
        'role_id' => 1,
    ]);

    // Get the raw encrypted value from database
    $rawEmail = $user->getAttributeEncrypted('email');

    // Verify the raw value is encrypted
    expect($rawEmail)->not->toBe('test@example.com');
    expect($rawEmail)->toBeString();

    // Verify the value can be decrypted
    $fieldEncryption = app(FieldEncryption::class);
    expect($fieldEncryption->isEncryptedValue($rawEmail))->toBeTrue();
});

it('automatically decrypts user email field on read', function () {
    $user = User::factory()->create([
        'role_id' => 1,
    ]);

    // Verify the decrypted value is returned
    expect($user->email)->toBe('test@example.com');
});

it('encrypts user phone field', function () {
    $user = User::factory()->create([
        'role_id' => 1,
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
        'phone' => '123-456-7890',
    ]);

    $rawPhone = $user->getAttributeEncrypted('phone');

    // Verify the raw value is encrypted
    expect($rawPhone)->not->toBe('123-456-7890');

    // Verify the value can be decrypted
    expect($user->phone)->toBe('123-456-7890');
});

it('does not encrypt fields not in encrypted property', function () {
    $user = User::factory()->create([
        'role_id' => 1,
    ]);

    // Name should not be encrypted
    $rawName = $user->getAttributeEncrypted('name');
    expect($rawName)->toBe('Test User');
});

it('handles null values in encrypted fields', function () {
    $user = User::factory()->create([
        'role_id' => 1,
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
        'phone' => null,
    ]);

    expect($user->phone)->toBeNull();
});

it('handles empty string values in encrypted fields', function () {
    $user = User::factory()->create([
        'role_id' => 1,
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
        'phone' => '',
    ]);

    expect($user->phone)->toBe('');
});

it('updates encrypted fields correctly', function () {
    $user = User::factory()->create([
        'role_id' => 1,
    ]);

    $user->update(['email' => 'newemail@example.com']);

    expect($user->email)->toBe('newemail@example.com');

    // Verify it's encrypted in database
    $rawEmail = $user->getAttributeEncrypted('email');
    expect($rawEmail)->not->toBe('newemail@example.com');
});

it('returns decrypted values in toArray()', function () {
    $user = User::factory()->create([
        'role_id' => 1,
    ]);

    $array = $user->toArray();

    expect($array['email'])->toBe('test@example.com');
});

it('does not double encrypt already encrypted values', function () {
    $user = User::factory()->create([
        'role_id' => 1,
    ]);

    // Get the encrypted value
    $encryptedEmail = $user->getAttributeEncrypted('email');

    // Try to set it again
    $user->email = $encryptedEmail;
    $user->save();

    // Should still decrypt correctly
    $user->refresh();
    expect($user->email)->toBe('test@example.com');
});

// ============================================
// Encryptable Trait - SupportTicket Model Tests
// ============================================

it('encrypts support ticket subject and description', function () {
    $ticket = SupportTicket::factory()->create([
        'ticket_number' => 'TKT-2024-0001',
        'subject' => 'Test Ticket Subject',
        'description' => 'This is a test ticket description with sensitive data',
        'status' => 'open',
        'priority' => 'normal',
    ]);

    // Verify subject is encrypted
    $rawSubject = $ticket->getAttributeEncrypted('subject');
    expect($rawSubject)->not->toBe('Test Ticket Subject');

    // Verify description is encrypted
    $rawDescription = $ticket->getAttributeEncrypted('description');
    expect($rawDescription)->not->toBe('This is a test ticket description with sensitive data');

    // Verify decryption works
    expect($ticket->subject)->toBe('Test Ticket Subject');
    expect($ticket->description)->toBe('This is a test ticket description with sensitive data');
});

// ============================================
// Configuration-Based Encryption Control
// ============================================

it('does not encrypt when encryption is disabled', function () {
    Config::set('compliance.encryption.enabled', false);

    $user = User::factory()->create([
        'role_id' => 1,
    ]);

    // Email should not be encrypted
    $rawEmail = $user->getAttributeEncrypted('email');
    expect($rawEmail)->toBe('test@example.com');
});

it('does not decrypt when auto_decrypt is disabled', function () {
    // Create user with encryption enabled
    $user = User::factory()->create([
        'role_id' => 1,
    ]);

    // Disable auto decryption
    Config::set('compliance.encryption.auto_decrypt', false);

    // Refresh the user to clear any cached decrypted values
    $user = User::find($user->id);

    // Should return encrypted value
    expect($user->email)->not->toBe('test@example.com');

    // But should still be encrypted
    $fieldEncryption = app(FieldEncryption::class);
    expect($fieldEncryption->isEncryptedValue($user->email))->toBeTrue();
});

it('does not encrypt when compliance is disabled', function () {
    Config::set('compliance.enabled', false);

    $user = User::factory()->create([
        'role_id' => 1,
    ]);

    // Email should not be encrypted
    $rawEmail = $user->getAttributeEncrypted('email');
    expect($rawEmail)->toBe('test@example.com');
});

// ============================================
// Manual Encryption/Decryption Methods
// ============================================

it('can manually encrypt a field', function () {
    $user = User::factory()->create([
        'role_id' => 1,
    ]);

    $encrypted = $user->encryptField('phone', '123-456-7890');

    expect($encrypted)->not->toBe('123-456-7890');
    expect($encrypted)->toBeString();
});

it('can manually decrypt a field', function () {
    $user = User::factory()->create([
        'role_id' => 1,
    ]);

    $encrypted = $user->encryptField('phone', '123-456-7890');
    $decrypted = $user->decryptField('phone', $encrypted);

    expect($decrypted)->toBe('123-456-7890');
});

it('can check if a field value is encrypted', function () {
    $user = User::factory()->create([
        'role_id' => 1,
    ]);

    expect($user->isFieldEncrypted('email'))->toBeTrue();
    expect($user->isFieldEncrypted('name'))->toBeFalse();
});

it('can set an already encrypted value without double encryption', function () {
    $user = User::factory()->create([
        'role_id' => 1,
    ]);

    $encryptedEmail = $user->getAttributeEncrypted('email');

    // Create new user with pre-encrypted value
    $user2 = new User([
        'name' => 'Test User 2',
        'password' => bcrypt('password'),
    ]);

    $user2->setAttributeEncrypted('email', $encryptedEmail);
    $user2->save();

    // Should decrypt to original value
    expect($user2->email)->toBe('test@example.com');
});

// ============================================
// Key Rotation Tests
// ============================================

it('can rotate encryption keys for a user', function () {
    // Create user with current key
    $user = User::factory()->create([
        'role_id' => 1,
    ]);

    // Simulate old key (for testing, we'll use the current key as "old")
    $oldKey = config('app.key');
    $oldKey = str_starts_with($oldKey, 'base64:')
        ? base64_decode(substr($oldKey, 7))
        : $oldKey;

    // Rotate keys (in real scenario, this would re-encrypt with new key)
    $success = $user->rotateEncryptionKeys($oldKey);

    expect($success)->toBeTrue();
});

it('handles key rotation for empty encrypted fields', function () {
    $user = User::factory()->create([
        'role_id' => 1,
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
        'phone' => null,
    ]);

    $oldKey = config('app.key');
    $oldKey = str_starts_with($oldKey, 'base64:')
        ? base64_decode(substr($oldKey, 7))
        : $oldKey;

    $success = $user->rotateEncryptionKeys($oldKey);

    expect($success)->toBeTrue();
    expect($user->phone)->toBeNull();
});

// ============================================
// FieldEncryption Service Integration Tests
// ============================================

it('integrates with FieldEncryption service for model configuration', function () {
    $fieldEncryption = app(FieldEncryption::class);

    $encryptedFields = $fieldEncryption->getEncryptedFieldsForModel(User::class);

    // Should respect config if model doesn't define encrypted property
    expect($encryptedFields)->toBeArray();
});

it('uses model encrypted property over config', function () {
    $user = new User();

    // User model defines $encrypted property
    $reflection = new \ReflectionClass($user);
    $property = $reflection->getProperty('encrypted');
    $property->setAccessible(true);
    $encrypted = $property->getValue($user);

    expect($encrypted)->toBeArray();
    expect($encrypted)->toContain('email');
});

it('service can check if specific field should be encrypted', function () {
    $fieldEncryption = app(FieldEncryption::class);

    // Based on User model's $encrypted property
    $shouldEncrypt = $fieldEncryption->shouldEncryptField(User::class, 'email');

    expect($shouldEncrypt)->toBeTrue();
});

// ============================================
// Edge Cases and Error Handling
// ============================================

it('handles encryption of complex data types', function () {
    $fieldEncryption = app(FieldEncryption::class);

    $complexData = [
        'nested' => [
            'key' => 'value',
            'array' => [1, 2, 3],
        ],
    ];

    $encrypted = $fieldEncryption->encrypt($complexData, true);
    $decrypted = $fieldEncryption->decrypt($encrypted, true);

    expect($decrypted)->toEqual($complexData);
});

it('handles batch encryption of multiple fields', function () {
    $fieldEncryption = app(FieldEncryption::class);

    $data = [
        'email' => 'test@example.com',
        'phone' => '123-456-7890',
        'name' => 'Test User',
    ];

    $encrypted = $fieldEncryption->encryptFields($data, ['email', 'phone']);

    expect($encrypted['email'])->not->toBe('test@example.com');
    expect($encrypted['phone'])->not->toBe('123-456-7890');
    expect($encrypted['name'])->toBe('Test User'); // Not encrypted
});

it('handles batch decryption of multiple fields', function () {
    $fieldEncryption = app(FieldEncryption::class);

    $data = [
        'email' => 'test@example.com',
        'phone' => '123-456-7890',
        'name' => 'Test User',
    ];

    $encrypted = $fieldEncryption->encryptFields($data, ['email', 'phone']);
    $decrypted = $fieldEncryption->decryptFields($encrypted, ['email', 'phone']);

    expect($decrypted['email'])->toBe('test@example.com');
    expect($decrypted['phone'])->toBe('123-456-7890');
});

it('gracefully handles decryption failures', function () {
    $fieldEncryption = app(FieldEncryption::class);

    // Try to decrypt an invalid encrypted value
    $result = $fieldEncryption->decrypt('invalid-encrypted-value');

    // Should return original value on failure
    expect($result)->toBe('invalid-encrypted-value');
});

it('detects encrypted values correctly', function () {
    $fieldEncryption = app(FieldEncryption::class);

    $plainValue = 'test@example.com';
    $encryptedValue = $fieldEncryption->encrypt($plainValue);

    expect($fieldEncryption->isEncryptedValue($plainValue))->toBeFalse();
    expect($fieldEncryption->isEncryptedValue($encryptedValue))->toBeTrue();
    expect($fieldEncryption->isEncryptedValue(null))->toBeFalse();
    expect($fieldEncryption->isEncryptedValue(''))->toBeFalse();
    expect($fieldEncryption->isEncryptedValue(123))->toBeFalse();
});

// ============================================
// Integration with Other Models
// ============================================

it('encrypts multiple models independently', function () {
    $user = User::factory()->create([
        'role_id' => 1,
    ]);

    $ticket = SupportTicket::factory()->create([
        'ticket_number' => 'TKT-2024-0001',
        'subject' => 'Ticket Subject',
        'description' => 'Ticket Description',
        'status' => 'open',
        'priority' => 'normal',
    ]);

    // Verify both are encrypted
    expect($user->getAttributeEncrypted('email'))->not->toBe('user@example.com');
    expect($ticket->getAttributeEncrypted('subject'))->not->toBe('Ticket Subject');

    // Verify both decrypt correctly
    expect($user->email)->toBe('user@example.com');
    expect($ticket->subject)->toBe('Ticket Subject');
});

// ============================================
// Performance and Memory Tests
// ============================================

it('handles large text encryption efficiently', function () {
    $largeText = str_repeat('Lorem ipsum dolor sit amet. ', 1000); // ~30KB

    $ticket = SupportTicket::factory()->create([
        'ticket_number' => 'TKT-2024-0001',
        'subject' => 'Large Ticket',
        'description' => $largeText,
        'status' => 'open',
        'priority' => 'normal',
    ]);

    expect($ticket->description)->toBe($largeText);
});

it('handles special characters in encrypted fields', function () {
    $specialChars = "Special chars: !@#$%^&*()_+-=[]{}|;':\",./<>?\\";

    $user = User::factory()->create([
        'role_id' => 1,
        'name' => $specialChars,
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    expect($user->name)->toBe($specialChars);
});

it('handles unicode characters in encrypted fields', function () {
    $unicode = '你好世界 مرحا العالم Здравствуй мир';

    $ticket = SupportTicket::factory()->create([
        'ticket_number' => 'TKT-2024-0001',
        'subject' => $unicode,
        'description' => 'Description',
        'status' => 'open',
        'priority' => 'normal',
    ]);

    expect($ticket->subject)->toBe($unicode);
});

// ============================================
// Key Rotation Command Tests
// ============================================

it('displays error when encryption is disabled in rotate command', function () {
    Config::set('compliance.encryption.enabled', false);

    $this->artisan('compliance:rotate-encryption-keys')
        ->expectsOutput('❌ Field encryption is disabled in configuration')
        ->assertExitCode(1);
});

it('requires old key parameter in rotate command', function () {
    $this->artisan('compliance:rotate-encryption-keys')
        ->expectsOutput('❌ Old encryption key is required. Use --old-key option.')
        ->assertExitCode(1);
});

it('validates encryption key format in rotate command', function () {
    $this->artisan('compliance:rotate-encryption-keys', [
        '--old-key' => 'invalid-key',
    ])
        ->expectsOutput('❌ Invalid encryption key format. Key must be base64 encoded.')
        ->assertExitCode(1);
});

it('displays statistics with stats option', function () {
    User::factory()->count(3)->create();
    SupportTicket::factory()->count(2)->create();

    $this->artisan('compliance:rotate-encryption-keys', [
        '--stats' => true,
    ])
        ->expectsOutput('📊 Encryption Statistics')
        ->expectsOutput('Encryptable Models:')
        ->assertExitCode(0);
});

it('performs dry run of key rotation without modifying data', function () {
    User::factory()->count(2)->create();

    $oldKey = 'base64:' . base64_encode(base64_decode(substr(config('app.key'), 7)));

    $this->artisan('compliance:rotate-encryption-keys', [
        '--old-key' => $oldKey,
        '--dry-run' => true,
    ])
        ->expectsOutput('Running in DRY RUN mode - no data will be modified')
        ->assertExitCode(0);
});

it('rotates keys for specific model type only', function () {
    User::factory()->count(2)->create();
    SupportTicket::factory()->count(3)->create();

    $oldKey = 'base64:' . base64_encode(base64_decode(substr(config('app.key'), 7)));

    $this->artisan('compliance:rotate-encryption-keys', [
        '--old-key' => $oldKey,
        '--model' => 'User',
        '--dry-run' => true,
    ])
        ->expectsOutput('Processing model type: User')
        ->assertExitCode(0);
});

it('validates model type parameter in rotate command', function () {
    $oldKey = 'base64:' . base64_encode(base64_decode(substr(config('app.key'), 7)));

    $this->artisan('compliance:rotate-encryption-keys', [
        '--old-key' => $oldKey,
        '--model' => 'InvalidModel',
    ])
        ->expectsOutput('❌ Invalid model type: InvalidModel')
        ->assertExitCode(1);
});

it('supports custom batch size in rotation command', function () {
    User::factory()->count(5)->create();

    $oldKey = 'base64:' . base64_encode(base64_decode(substr(config('app.key'), 7)));

    $this->artisan('compliance:rotate-encryption-keys', [
        '--old-key' => $oldKey,
        '--batch-size' => 2,
        '--dry-run' => true,
    ])
        ->assertExitCode(0);
});

it('requires confirmation for actual key rotation', function () {
    User::factory()->count(1)->create();

    $oldKey = 'base64:' . base64_encode(base64_decode(substr(config('app.key'), 7)));

    $this->artisan('compliance:rotate-encryption-keys', [
        '--old-key' => $oldKey,
    ])
        ->expectsQuestion('Are you sure you want to proceed?', false)
        ->expectsOutput('Operation cancelled.')
        ->assertExitCode(0);
});

it('handles empty database gracefully in rotation command', function () {
    // Ensure no users or tickets exist
    User::query()->delete();
    SupportTicket::query()->delete();

    $oldKey = 'base64:' . base64_encode(base64_decode(substr(config('app.key'), 7)));

    $this->artisan('compliance:rotate-encryption-keys', [
        '--old-key' => $oldKey,
        '--dry-run' => true,
    ])
        ->assertExitCode(0);
});

// ============================================
// Additional Integration Tests
// ============================================

it('maintains data integrity across multiple operations', function () {
    $originalEmail = 'original@example.com';
    $updatedEmail = 'updated@example.com';

    // Create
    $user = User::factory()->create([
        'role_id' => 1,
        'name' => 'Test User',
        'email' => $originalEmail,
        'password' => bcrypt('password'),
    ]);

    expect($user->email)->toBe($originalEmail);

    // Update
    $user->update(['email' => $updatedEmail]);
    expect($user->email)->toBe($updatedEmail);

    // Retrieve from database
    $freshUser = User::find($user->id);
    expect($freshUser->email)->toBe($updatedEmail);

    // Verify encryption is still applied
    expect($freshUser->getAttributeEncrypted('email'))->not->toBe($updatedEmail);
});

it('handles concurrent model operations with encryption', function () {
    $user = User::factory()->create([
        'role_id' => 1,
    ]);

    // Simulate concurrent updates
    $user1 = User::find($user->id);
    $user2 = User::find($user->id);

    $user1->update(['email' => 'updated1@example.com']);
    $user2->update(['email' => 'updated2@example.com']);

    // Last write wins
    $final = User::find($user->id);
    expect($final->email)->toBe('updated2@example.com');
});

it('encrypts and queries multiple models efficiently', function () {
    // Create multiple users
    User::factory()->create([
        'role_id' => 1,
    ]);
    User::factory()->create([
        'role_id' => 1,
    ]);
    User::factory()->create([
        'role_id' => 1,
    ]);

    $users = User::all();

    expect($users)->toHaveCount(3);

    foreach ($users as $user) {
        expect($user->email)->toContain('@example.com');
        expect($user->isFieldEncrypted('email'))->toBeTrue();
    }
});

it('handles model replication with encrypted fields', function () {
    $user = User::factory()->create([
        'role_id' => 1,
    ]);

    $replica = $user->replicate();
    $replica->save();

    // Both should have encrypted emails
    expect($user->isFieldEncrypted('email'))->toBeTrue();
    expect($replica->isFieldEncrypted('email'))->toBeTrue();

    // And both should decrypt to the same value
    expect($replica->email)->toBe($user->email);
});

it('converts models to json with decrypted values', function () {
    $email = 'test@example.com';

    $user = User::factory()->create([
        'role_id' => 1,
        'name' => 'Test User',
        'email' => $email,
        'password' => bcrypt('password'),
    ]);

    $json = json_decode($user->toJson(), true);

    expect($json['email'])->toBe($email);
});

it('handles searching limitations with encrypted fields', function () {
    // Note: This demonstrates that direct DB searches on encrypted fields won't work
    // This is expected behavior and a documented limitation
    $email = 'test@example.com';

    $user = User::factory()->create([
        'role_id' => 1,
        'name' => 'Test User',
        'email' => $email,
        'password' => bcrypt('password'),
    ]);

    // Searching by encrypted field in database will not work
    $result = User::where('email', $email)->first();
    expect($result)->toBeNull();

    // But we can find by ID and verify
    $found = User::find($user->id);
    expect($found->email)->toBe($email);
});

it('preserves encryption during model updates with unchanged encrypted fields', function () {
    $user = User::factory()->create([
        'role_id' => 1,
    ]);

    $originalEncrypted = $user->getAttributeEncrypted('email');

    // Update non-encrypted field
    $user->update(['name' => 'Updated Name']);

    // Encrypted email should remain the same
    $user->refresh();
    expect($user->getAttributeEncrypted('email'))->toBe($originalEncrypted);
    expect($user->email)->toBe('test@example.com');
});

it('handles batch operations with encrypted fields', function () {
    $users = User::factory()->count(10)->create();

    foreach ($users as $user) {
        $email = $user->email;

        // Refresh from database
        $freshUser = User::find($user->id);

        expect($freshUser->email)->toBe($email);
        expect($freshUser->getAttributeEncrypted('email'))->not->toBe($email);
    }

    expect(User::count())->toBe(10);
});

it('encrypts long text values correctly', function () {
    $longDescription = str_repeat('This is a very long description with sensitive information. ', 100);

    $ticket = SupportTicket::factory()->create([
        'ticket_number' => 'TKT-2024-0001',
        'subject' => 'Long Description Test',
        'description' => $longDescription,
        'status' => 'open',
        'priority' => 'normal',
    ]);

    expect($ticket->description)->toBe($longDescription);
    expect($ticket->getAttributeEncrypted('description'))->not->toBe($longDescription);
});

it('handles multiple encrypted fields on same model', function () {
    $ticket = SupportTicket::factory()->create([
        'ticket_number' => 'TKT-2024-0001',
        'subject' => 'Test Subject',
        'description' => 'Test Description',
        'status' => 'open',
        'priority' => 'normal',
    ]);

    // Both fields should be encrypted
    expect($ticket->getAttributeEncrypted('subject'))->not->toBe('Test Subject');
    expect($ticket->getAttributeEncrypted('description'))->not->toBe('Test Description');

    // Both should decrypt correctly
    expect($ticket->subject)->toBe('Test Subject');
    expect($ticket->description)->toBe('Test Description');
});

it('handles null and empty values in batch encryption', function () {
    $fieldEncryption = app(FieldEncryption::class);

    $data = [
        'email' => 'test@example.com',
        'phone' => null,
        'address' => '',
    ];

    $encrypted = $fieldEncryption->encryptFields($data, ['email', 'phone', 'address']);

    expect($encrypted['email'])->not->toBe('test@example.com');
    expect($encrypted['phone'])->toBeNull();
    expect($encrypted['address'])->toBe('');
});

it('supports getAttributesEncrypted for raw database values', function () {
    $user = User::factory()->create([
        'role_id' => 1,
    ]);

    $attributes = $user->getAttributesEncrypted();

    // Should contain raw encrypted values
    expect($attributes['email'])->not->toBe('test@example.com');
    expect($attributes['name'])->toBe('Test User'); // Not encrypted
});

it('handles encryption service configuration checks', function () {
    $fieldEncryption = app(FieldEncryption::class);

    // Check auto decrypt status
    expect($fieldEncryption->isAutoDecryptEnabled())->toBeTrue();

    // Disable auto decrypt
    Config::set('compliance.encryption.auto_decrypt', false);
    expect($fieldEncryption->isAutoDecryptEnabled())->toBeFalse();
});

it('handles key rotation configuration checks', function () {
    $fieldEncryption = app(FieldEncryption::class);

    // Set key rotation enabled
    Config::set('compliance.encryption.key_rotation.enabled', true);
    expect($fieldEncryption->isKeyRotationEnabled())->toBeTrue();

    // Check rotation days
    Config::set('compliance.encryption.key_rotation.rotation_days', 90);
    expect($fieldEncryption->getKeyRotationDays())->toBe(90);
});
