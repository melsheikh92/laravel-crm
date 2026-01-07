<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Webkul\Core\Models\CoreConfig;
use Webkul\Core\Repositories\CoreConfigRepository;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->repository = app(CoreConfigRepository::class);
});

/**
 * SMTP Password Encryption Tests
 */
it('encrypts SMTP password when storing configuration', function () {
    $smtpPassword = 'test-smtp-password-123';

    $this->repository->create([
        'emails' => [
            'smtp' => [
                'password' => $smtpPassword,
            ],
        ],
    ]);

    $config = CoreConfig::where('code', 'emails.smtp.password')->first();

    expect($config)->not()->toBeNull();
    expect($config->encrypted)->toBeTrue();
    expect($config->getAttributes()['value'])->not()->toBe($smtpPassword);
    expect($config->value)->toBe($smtpPassword);
});

it('decrypts SMTP password when retrieving configuration', function () {
    $smtpPassword = 'secure-smtp-pass';

    $this->repository->create([
        'emails' => [
            'smtp' => [
                'password' => $smtpPassword,
            ],
        ],
    ]);

    $config = CoreConfig::where('code', 'emails.smtp.password')->first();

    expect($config->value)->toBe($smtpPassword);
    expect($config->encrypted)->toBeTrue();
});

it('stores encrypted value in database for SMTP password', function () {
    $smtpPassword = 'my-smtp-password';

    $this->repository->create([
        'emails' => [
            'smtp' => [
                'password' => $smtpPassword,
            ],
        ],
    ]);

    $config = CoreConfig::where('code', 'emails.smtp.password')->first();
    $rawValue = $config->getAttributes()['value'];

    // Raw value should be encrypted and not match original
    expect($rawValue)->not()->toBe($smtpPassword);

    // Should be able to decrypt to original value
    $decrypted = Crypt::decryptString($rawValue);
    expect($decrypted)->toBe($smtpPassword);
});

it('does not encrypt SMTP host', function () {
    $smtpHost = 'smtp.gmail.com';

    $this->repository->create([
        'emails' => [
            'smtp' => [
                'host' => $smtpHost,
            ],
        ],
    ]);

    $config = CoreConfig::where('code', 'emails.smtp.host')->first();

    expect($config)->not()->toBeNull();
    expect($config->encrypted)->toBeFalse();
    expect($config->value)->toBe($smtpHost);
    expect($config->getAttributes()['value'])->toBe($smtpHost);
});

it('does not encrypt SMTP username', function () {
    $smtpUsername = 'user@example.com';

    $this->repository->create([
        'emails' => [
            'smtp' => [
                'username' => $smtpUsername,
            ],
        ],
    ]);

    $config = CoreConfig::where('code', 'emails.smtp.username')->first();

    expect($config)->not()->toBeNull();
    expect($config->encrypted)->toBeFalse();
    expect($config->value)->toBe($smtpUsername);
});

it('does not encrypt SMTP from_address', function () {
    $fromAddress = 'noreply@example.com';

    $this->repository->create([
        'emails' => [
            'smtp' => [
                'from_address' => $fromAddress,
            ],
        ],
    ]);

    $config = CoreConfig::where('code', 'emails.smtp.from_address')->first();

    expect($config)->not()->toBeNull();
    expect($config->encrypted)->toBeFalse();
    expect($config->value)->toBe($fromAddress);
});

/**
 * IMAP Password Encryption Tests
 */
it('encrypts IMAP password when storing configuration', function () {
    $imapPassword = 'test-imap-password-456';

    $this->repository->create([
        'emails' => [
            'imap' => [
                'password' => $imapPassword,
            ],
        ],
    ]);

    $config = CoreConfig::where('code', 'emails.imap.password')->first();

    expect($config)->not()->toBeNull();
    expect($config->encrypted)->toBeTrue();
    expect($config->getAttributes()['value'])->not()->toBe($imapPassword);
    expect($config->value)->toBe($imapPassword);
});

it('decrypts IMAP password when retrieving configuration', function () {
    $imapPassword = 'secure-imap-pass';

    $this->repository->create([
        'emails' => [
            'imap' => [
                'password' => $imapPassword,
            ],
        ],
    ]);

    $config = CoreConfig::where('code', 'emails.imap.password')->first();

    expect($config->value)->toBe($imapPassword);
    expect($config->encrypted)->toBeTrue();
});

it('stores encrypted value in database for IMAP password', function () {
    $imapPassword = 'my-imap-password';

    $this->repository->create([
        'emails' => [
            'imap' => [
                'password' => $imapPassword,
            ],
        ],
    ]);

    $config = CoreConfig::where('code', 'emails.imap.password')->first();
    $rawValue = $config->getAttributes()['value'];

    // Raw value should be encrypted and not match original
    expect($rawValue)->not()->toBe($imapPassword);

    // Should be able to decrypt to original value
    $decrypted = Crypt::decryptString($rawValue);
    expect($decrypted)->toBe($imapPassword);
});

it('does not encrypt IMAP host', function () {
    $imapHost = 'imap.gmail.com';

    $this->repository->create([
        'emails' => [
            'imap' => [
                'host' => $imapHost,
            ],
        ],
    ]);

    $config = CoreConfig::where('code', 'emails.imap.host')->first();

    expect($config)->not()->toBeNull();
    expect($config->encrypted)->toBeFalse();
    expect($config->value)->toBe($imapHost);
    expect($config->getAttributes()['value'])->toBe($imapHost);
});

it('does not encrypt IMAP username', function () {
    $imapUsername = 'imap@example.com';

    $this->repository->create([
        'emails' => [
            'imap' => [
                'username' => $imapUsername,
            ],
        ],
    ]);

    $config = CoreConfig::where('code', 'emails.imap.username')->first();

    expect($config)->not()->toBeNull();
    expect($config->encrypted)->toBeFalse();
    expect($config->value)->toBe($imapUsername);
});

/**
 * Password Update Tests
 */
it('updates SMTP password with encryption', function () {
    $oldPassword = 'old-smtp-password';
    $newPassword = 'new-smtp-password';

    // Create initial config
    $this->repository->create([
        'emails' => [
            'smtp' => [
                'password' => $oldPassword,
            ],
        ],
    ]);

    // Update password
    $this->repository->create([
        'emails' => [
            'smtp' => [
                'password' => $newPassword,
            ],
        ],
    ]);

    $config = CoreConfig::where('code', 'emails.smtp.password')->first();

    expect($config->value)->toBe($newPassword);
    expect($config->value)->not()->toBe($oldPassword);
    expect($config->encrypted)->toBeTrue();
    expect($config->getAttributes()['value'])->not()->toBe($newPassword);
});

it('updates IMAP password with encryption', function () {
    $oldPassword = 'old-imap-password';
    $newPassword = 'new-imap-password';

    // Create initial config
    $this->repository->create([
        'emails' => [
            'imap' => [
                'password' => $oldPassword,
            ],
        ],
    ]);

    // Update password
    $this->repository->create([
        'emails' => [
            'imap' => [
                'password' => $newPassword,
            ],
        ],
    ]);

    $config = CoreConfig::where('code', 'emails.imap.password')->first();

    expect($config->value)->toBe($newPassword);
    expect($config->value)->not()->toBe($oldPassword);
    expect($config->encrypted)->toBeTrue();
    expect($config->getAttributes()['value'])->not()->toBe($newPassword);
});

/**
 * Multiple Configuration Storage Tests
 */
it('encrypts both SMTP and IMAP passwords when stored together', function () {
    $smtpPassword = 'smtp-pass-123';
    $imapPassword = 'imap-pass-456';

    $this->repository->create([
        'emails' => [
            'smtp' => [
                'host'     => 'smtp.gmail.com',
                'username' => 'smtp@example.com',
                'password' => $smtpPassword,
            ],
            'imap' => [
                'host'     => 'imap.gmail.com',
                'username' => 'imap@example.com',
                'password' => $imapPassword,
            ],
        ],
    ]);

    $smtpConfig = CoreConfig::where('code', 'emails.smtp.password')->first();
    $imapConfig = CoreConfig::where('code', 'emails.imap.password')->first();

    expect($smtpConfig->encrypted)->toBeTrue();
    expect($imapConfig->encrypted)->toBeTrue();
    expect($smtpConfig->value)->toBe($smtpPassword);
    expect($imapConfig->value)->toBe($imapPassword);
    expect($smtpConfig->getAttributes()['value'])->not()->toBe($smtpPassword);
    expect($imapConfig->getAttributes()['value'])->not()->toBe($imapPassword);
});

/**
 * Edge Case Tests
 */
it('handles empty password value', function () {
    $this->repository->create([
        'emails' => [
            'smtp' => [
                'password' => '',
            ],
        ],
    ]);

    $config = CoreConfig::where('code', 'emails.smtp.password')->first();

    expect($config)->not()->toBeNull();
    expect($config->value)->toBe('');
    expect($config->encrypted)->toBeTrue();
});

it('handles null password value', function () {
    $this->repository->create([
        'emails' => [
            'smtp' => [
                'password' => null,
            ],
        ],
    ]);

    $config = CoreConfig::where('code', 'emails.smtp.password')->first();

    // Null values should not be encrypted
    expect($config)->not()->toBeNull();
    expect($config->value)->toBeNull();
});

it('correctly identifies password fields for encryption', function () {
    $testData = [
        'emails' => [
            'smtp' => [
                'host'         => 'smtp.test.com',
                'port'         => '587',
                'username'     => 'user@test.com',
                'password'     => 'secret-password',
                'from_address' => 'from@test.com',
                'from_name'    => 'Test Sender',
            ],
        ],
    ];

    $this->repository->create($testData);

    // Check that only password is encrypted
    $hostConfig = CoreConfig::where('code', 'emails.smtp.host')->first();
    $portConfig = CoreConfig::where('code', 'emails.smtp.port')->first();
    $usernameConfig = CoreConfig::where('code', 'emails.smtp.username')->first();
    $passwordConfig = CoreConfig::where('code', 'emails.smtp.password')->first();
    $fromAddressConfig = CoreConfig::where('code', 'emails.smtp.from_address')->first();
    $fromNameConfig = CoreConfig::where('code', 'emails.smtp.from_name')->first();

    expect($hostConfig->encrypted)->toBeFalse();
    expect($portConfig->encrypted)->toBeFalse();
    expect($usernameConfig->encrypted)->toBeFalse();
    expect($passwordConfig->encrypted)->toBeTrue();
    expect($fromAddressConfig->encrypted)->toBeFalse();
    expect($fromNameConfig->encrypted)->toBeFalse();
});

/**
 * Decryption Fallback Tests
 */
it('returns original value if decryption fails', function () {
    // Manually create a config with invalid encrypted data
    $config = CoreConfig::create([
        'code'      => 'test.invalid.encrypted',
        'value'     => 'not-actually-encrypted',
        'encrypted' => true,
    ]);

    // Should return the original value if decryption fails
    expect($config->value)->toBe('not-actually-encrypted');
});

it('handles special characters in password', function () {
    $specialPassword = 'p@ssw0rd!#$%^&*()_+-=[]{}|;:,.<>?/~`';

    $this->repository->create([
        'emails' => [
            'smtp' => [
                'password' => $specialPassword,
            ],
        ],
    ]);

    $config = CoreConfig::where('code', 'emails.smtp.password')->first();

    expect($config->value)->toBe($specialPassword);
    expect($config->encrypted)->toBeTrue();
});

it('handles unicode characters in password', function () {
    $unicodePassword = '密码测试🔐🔑';

    $this->repository->create([
        'emails' => [
            'smtp' => [
                'password' => $unicodePassword,
            ],
        ],
    ]);

    $config = CoreConfig::where('code', 'emails.smtp.password')->first();

    expect($config->value)->toBe($unicodePassword);
    expect($config->encrypted)->toBeTrue();
});

it('maintains encryption flag across multiple updates', function () {
    $passwords = ['password1', 'password2', 'password3'];

    foreach ($passwords as $password) {
        $this->repository->create([
            'emails' => [
                'smtp' => [
                    'password' => $password,
                ],
            ],
        ]);
    }

    $config = CoreConfig::where('code', 'emails.smtp.password')->first();

    expect($config->value)->toBe('password3');
    expect($config->encrypted)->toBeTrue();
});

/**
 * Integration with core()->getConfigData() Tests
 */
it('retrieves decrypted password using core helper', function () {
    $smtpPassword = 'helper-test-password';

    $this->repository->create([
        'emails' => [
            'smtp' => [
                'password' => $smtpPassword,
            ],
        ],
    ]);

    $retrievedPassword = core()->getConfigData('emails.smtp.password');

    expect($retrievedPassword)->toBe($smtpPassword);
});

it('retrieves non-encrypted fields using core helper', function () {
    $smtpHost = 'smtp.example.com';

    $this->repository->create([
        'emails' => [
            'smtp' => [
                'host' => $smtpHost,
            ],
        ],
    ]);

    $retrievedHost = core()->getConfigData('emails.smtp.host');

    expect($retrievedHost)->toBe($smtpHost);
});
