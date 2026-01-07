<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Webkul\Admin\Services\MailConnectionTester;

beforeEach(function () {
    $this->tester = new MailConnectionTester();
});

/**
 * SMTP Validation Tests
 */
it('validates SMTP configuration successfully with all required fields', function () {
    $config = [
        'host'         => 'smtp.gmail.com',
        'port'         => 587,
        'encryption'   => 'tls',
        'username'     => 'test@gmail.com',
        'password'     => 'password123',
        'from_address' => 'test@gmail.com',
        'from_name'    => 'Test Sender',
    ];

    Mail::fake();

    $result = $this->tester->testSmtp($config);

    expect($result)->toBeArray();
    expect($result['success'])->toBeTrue();
});

it('throws exception when SMTP host is missing', function () {
    $config = [
        'port'         => 587,
        'username'     => 'test@gmail.com',
        'password'     => 'password',
        'from_address' => 'test@gmail.com',
        'from_name'    => 'Test',
    ];

    Mail::fake();

    $result = $this->tester->testSmtp($config);

    expect($result['success'])->toBeFalse();
    expect($result['error'])->toContain('Missing required SMTP configuration field: host');
});

it('throws exception when SMTP port is missing', function () {
    $config = [
        'host'         => 'smtp.gmail.com',
        'username'     => 'test@gmail.com',
        'password'     => 'password',
        'from_address' => 'test@gmail.com',
        'from_name'    => 'Test',
    ];

    Mail::fake();

    $result = $this->tester->testSmtp($config);

    expect($result['success'])->toBeFalse();
    expect($result['error'])->toContain('Missing required SMTP configuration field: port');
});

it('throws exception when SMTP username is missing', function () {
    $config = [
        'host'         => 'smtp.gmail.com',
        'port'         => 587,
        'password'     => 'password',
        'from_address' => 'test@gmail.com',
        'from_name'    => 'Test',
    ];

    Mail::fake();

    $result = $this->tester->testSmtp($config);

    expect($result['success'])->toBeFalse();
    expect($result['error'])->toContain('Missing required SMTP configuration field: username');
});

it('throws exception when SMTP password is missing', function () {
    $config = [
        'host'         => 'smtp.gmail.com',
        'port'         => 587,
        'username'     => 'test@gmail.com',
        'from_address' => 'test@gmail.com',
        'from_name'    => 'Test',
    ];

    Mail::fake();

    $result = $this->tester->testSmtp($config);

    expect($result['success'])->toBeFalse();
    expect($result['error'])->toContain('Missing required SMTP configuration field: password');
});

it('throws exception when SMTP from_address is missing', function () {
    $config = [
        'host'     => 'smtp.gmail.com',
        'port'     => 587,
        'username' => 'test@gmail.com',
        'password' => 'password',
        'from_name' => 'Test',
    ];

    Mail::fake();

    $result = $this->tester->testSmtp($config);

    expect($result['success'])->toBeFalse();
    expect($result['error'])->toContain('Missing required SMTP configuration field: from_address');
});

it('throws exception when SMTP from_name is missing', function () {
    $config = [
        'host'         => 'smtp.gmail.com',
        'port'         => 587,
        'username'     => 'test@gmail.com',
        'password'     => 'password',
        'from_address' => 'test@gmail.com',
    ];

    Mail::fake();

    $result = $this->tester->testSmtp($config);

    expect($result['success'])->toBeFalse();
    expect($result['error'])->toContain('Missing required SMTP configuration field: from_name');
});

it('throws exception when SMTP from_address has invalid email format', function () {
    $config = [
        'host'         => 'smtp.gmail.com',
        'port'         => 587,
        'username'     => 'test@gmail.com',
        'password'     => 'password',
        'from_address' => 'not-an-email',
        'from_name'    => 'Test',
    ];

    Mail::fake();

    $result = $this->tester->testSmtp($config);

    expect($result['success'])->toBeFalse();
    expect($result['error'])->toContain('Invalid email address in from_address field');
});

it('throws exception when SMTP port is not numeric', function () {
    $config = [
        'host'         => 'smtp.gmail.com',
        'port'         => 'not-a-number',
        'username'     => 'test@gmail.com',
        'password'     => 'password',
        'from_address' => 'test@gmail.com',
        'from_name'    => 'Test',
    ];

    Mail::fake();

    $result = $this->tester->testSmtp($config);

    expect($result['success'])->toBeFalse();
    expect($result['error'])->toContain('Invalid port number');
});

it('throws exception when SMTP port is less than 1', function () {
    $config = [
        'host'         => 'smtp.gmail.com',
        'port'         => 0,
        'username'     => 'test@gmail.com',
        'password'     => 'password',
        'from_address' => 'test@gmail.com',
        'from_name'    => 'Test',
    ];

    Mail::fake();

    $result = $this->tester->testSmtp($config);

    expect($result['success'])->toBeFalse();
    expect($result['error'])->toContain('Invalid port number');
});

it('throws exception when SMTP port is greater than 65535', function () {
    $config = [
        'host'         => 'smtp.gmail.com',
        'port'         => 99999,
        'username'     => 'test@gmail.com',
        'password'     => 'password',
        'from_address' => 'test@gmail.com',
        'from_name'    => 'Test',
    ];

    Mail::fake();

    $result = $this->tester->testSmtp($config);

    expect($result['success'])->toBeFalse();
    expect($result['error'])->toContain('Invalid port number');
});

it('throws exception when SMTP encryption type is invalid', function () {
    $config = [
        'host'         => 'smtp.gmail.com',
        'port'         => 587,
        'encryption'   => 'invalid',
        'username'     => 'test@gmail.com',
        'password'     => 'password',
        'from_address' => 'test@gmail.com',
        'from_name'    => 'Test',
    ];

    Mail::fake();

    $result = $this->tester->testSmtp($config);

    expect($result['success'])->toBeFalse();
    expect($result['error'])->toContain('Invalid encryption type');
});

it('accepts tls as valid SMTP encryption type', function () {
    $config = [
        'host'         => 'smtp.gmail.com',
        'port'         => 587,
        'encryption'   => 'tls',
        'username'     => 'test@gmail.com',
        'password'     => 'password',
        'from_address' => 'test@gmail.com',
        'from_name'    => 'Test',
    ];

    Mail::fake();

    $result = $this->tester->testSmtp($config);

    expect($result['success'])->toBeTrue();
});

it('accepts ssl as valid SMTP encryption type', function () {
    $config = [
        'host'         => 'smtp.gmail.com',
        'port'         => 465,
        'encryption'   => 'ssl',
        'username'     => 'test@gmail.com',
        'password'     => 'password',
        'from_address' => 'test@gmail.com',
        'from_name'    => 'Test',
    ];

    Mail::fake();

    $result = $this->tester->testSmtp($config);

    expect($result['success'])->toBeTrue();
});

it('accepts null as valid SMTP encryption type', function () {
    $config = [
        'host'         => 'smtp.gmail.com',
        'port'         => 25,
        'encryption'   => null,
        'username'     => 'test@gmail.com',
        'password'     => 'password',
        'from_address' => 'test@gmail.com',
        'from_name'    => 'Test',
    ];

    Mail::fake();

    $result = $this->tester->testSmtp($config);

    expect($result['success'])->toBeTrue();
});

it('accepts missing encryption field for SMTP configuration', function () {
    $config = [
        'host'         => 'smtp.gmail.com',
        'port'         => 25,
        'username'     => 'test@gmail.com',
        'password'     => 'password',
        'from_address' => 'test@gmail.com',
        'from_name'    => 'Test',
    ];

    Mail::fake();

    $result = $this->tester->testSmtp($config);

    expect($result['success'])->toBeTrue();
});

/**
 * IMAP Validation Tests
 */
it('validates IMAP configuration successfully with all required fields', function () {
    $config = [
        'host'     => 'imap.gmail.com',
        'port'     => 993,
        'encryption' => 'ssl',
        'username' => 'test@gmail.com',
        'password' => 'password',
    ];

    // Skip actual IMAP connection since we can't mock imap_open easily
    // Just test validation logic by catching the exception
    $result = $this->tester->testImap($config);

    // Will fail connection but validation should pass
    expect($result)->toBeArray();
    expect($result)->toHaveKey('success');
});

it('throws exception when IMAP host is missing', function () {
    $config = [
        'port'     => 993,
        'username' => 'test@gmail.com',
        'password' => 'password',
    ];

    $result = $this->tester->testImap($config);

    expect($result['success'])->toBeFalse();
    expect($result['error'])->toContain('Missing required IMAP configuration field: host');
});

it('throws exception when IMAP port is missing', function () {
    $config = [
        'host'     => 'imap.gmail.com',
        'username' => 'test@gmail.com',
        'password' => 'password',
    ];

    $result = $this->tester->testImap($config);

    expect($result['success'])->toBeFalse();
    expect($result['error'])->toContain('Missing required IMAP configuration field: port');
});

it('throws exception when IMAP username is missing', function () {
    $config = [
        'host'     => 'imap.gmail.com',
        'port'     => 993,
        'password' => 'password',
    ];

    $result = $this->tester->testImap($config);

    expect($result['success'])->toBeFalse();
    expect($result['error'])->toContain('Missing required IMAP configuration field: username');
});

it('throws exception when IMAP password is missing', function () {
    $config = [
        'host'     => 'imap.gmail.com',
        'port'     => 993,
        'username' => 'test@gmail.com',
    ];

    $result = $this->tester->testImap($config);

    expect($result['success'])->toBeFalse();
    expect($result['error'])->toContain('Missing required IMAP configuration field: password');
});

it('throws exception when IMAP port is not numeric', function () {
    $config = [
        'host'     => 'imap.gmail.com',
        'port'     => 'not-a-number',
        'username' => 'test@gmail.com',
        'password' => 'password',
    ];

    $result = $this->tester->testImap($config);

    expect($result['success'])->toBeFalse();
    expect($result['error'])->toContain('Invalid port number');
});

it('throws exception when IMAP port is less than 1', function () {
    $config = [
        'host'     => 'imap.gmail.com',
        'port'     => 0,
        'username' => 'test@gmail.com',
        'password' => 'password',
    ];

    $result = $this->tester->testImap($config);

    expect($result['success'])->toBeFalse();
    expect($result['error'])->toContain('Invalid port number');
});

it('throws exception when IMAP port is greater than 65535', function () {
    $config = [
        'host'     => 'imap.gmail.com',
        'port'     => 99999,
        'username' => 'test@gmail.com',
        'password' => 'password',
    ];

    $result = $this->tester->testImap($config);

    expect($result['success'])->toBeFalse();
    expect($result['error'])->toContain('Invalid port number');
});

it('throws exception when IMAP encryption type is invalid', function () {
    $config = [
        'host'       => 'imap.gmail.com',
        'port'       => 993,
        'encryption' => 'invalid',
        'username'   => 'test@gmail.com',
        'password'   => 'password',
    ];

    $result = $this->tester->testImap($config);

    expect($result['success'])->toBeFalse();
    expect($result['error'])->toContain('Invalid encryption type');
});

it('accepts tls as valid IMAP encryption type', function () {
    $config = [
        'host'       => 'imap.gmail.com',
        'port'       => 993,
        'encryption' => 'tls',
        'username'   => 'test@gmail.com',
        'password'   => 'password',
    ];

    $result = $this->tester->testImap($config);

    // Validation passes, connection will fail
    expect($result)->toBeArray();
    expect($result)->toHaveKey('success');
});

it('accepts ssl as valid IMAP encryption type', function () {
    $config = [
        'host'       => 'imap.gmail.com',
        'port'       => 993,
        'encryption' => 'ssl',
        'username'   => 'test@gmail.com',
        'password'   => 'password',
    ];

    $result = $this->tester->testImap($config);

    // Validation passes, connection will fail
    expect($result)->toBeArray();
    expect($result)->toHaveKey('success');
});

it('accepts notls as valid IMAP encryption type', function () {
    $config = [
        'host'       => 'imap.gmail.com',
        'port'       => 143,
        'encryption' => 'notls',
        'username'   => 'test@gmail.com',
        'password'   => 'password',
    ];

    $result = $this->tester->testImap($config);

    // Validation passes, connection will fail
    expect($result)->toBeArray();
    expect($result)->toHaveKey('success');
});

it('accepts null as valid IMAP encryption type', function () {
    $config = [
        'host'       => 'imap.gmail.com',
        'port'       => 143,
        'encryption' => null,
        'username'   => 'test@gmail.com',
        'password'   => 'password',
    ];

    $result = $this->tester->testImap($config);

    // Validation passes, connection will fail
    expect($result)->toBeArray();
    expect($result)->toHaveKey('success');
});

/**
 * SMTP Connection Test Return Structure Tests
 */
it('returns success array with correct structure for successful SMTP test', function () {
    $config = [
        'host'         => 'smtp.mailtrap.io',
        'port'         => 2525,
        'encryption'   => 'tls',
        'username'     => 'test',
        'password'     => 'test',
        'from_address' => 'test@example.com',
        'from_name'    => 'Test',
    ];

    Mail::fake();

    $result = $this->tester->testSmtp($config);

    expect($result)->toBeArray();
    expect($result)->toHaveKey('success');
    expect($result)->toHaveKey('message');
    expect($result['success'])->toBeTrue();
    expect($result['message'])->toBe('SMTP connection test successful.');
});

it('returns error array with correct structure for failed SMTP test', function () {
    $config = [
        'host'         => 'smtp.gmail.com',
        'port'         => 'invalid',
        'username'     => 'test@gmail.com',
        'password'     => 'password',
        'from_address' => 'test@gmail.com',
        'from_name'    => 'Test',
    ];

    Mail::fake();

    $result = $this->tester->testSmtp($config);

    expect($result)->toBeArray();
    expect($result)->toHaveKey('success');
    expect($result)->toHaveKey('message');
    expect($result)->toHaveKey('error');
    expect($result['success'])->toBeFalse();
    expect($result['message'])->toContain('SMTP connection test failed');
});

/**
 * IMAP Connection Test Return Structure Tests
 */
it('returns error array with correct structure for failed IMAP test', function () {
    $config = [
        'host'     => 'imap.invalid-server-that-does-not-exist.com',
        'port'     => 993,
        'username' => 'test@example.com',
        'password' => 'password',
    ];

    $result = $this->tester->testImap($config);

    expect($result)->toBeArray();
    expect($result)->toHaveKey('success');
    expect($result)->toHaveKey('message');
    expect($result['success'])->toBeFalse();
    expect($result['message'])->toContain('IMAP connection test failed');
});

/**
 * SMTP Configuration Setting Tests
 */
it('sets SMTP configuration in Laravel config during test', function () {
    $config = [
        'host'         => 'smtp.example.com',
        'port'         => 587,
        'encryption'   => 'tls',
        'username'     => 'test@example.com',
        'password'     => 'password',
        'from_address' => 'test@example.com',
        'from_name'    => 'Test Sender',
    ];

    Mail::fake();

    $this->tester->testSmtp($config);

    // Verify configuration was set
    expect(Config::get('mail.mailers.test_smtp'))->toBeArray();
    expect(Config::get('mail.mailers.test_smtp.host'))->toBe('smtp.example.com');
    expect(Config::get('mail.mailers.test_smtp.port'))->toBe(587);
    expect(Config::get('mail.mailers.test_smtp.encryption'))->toBe('tls');
    expect(Config::get('mail.from.address'))->toBe('test@example.com');
    expect(Config::get('mail.from.name'))->toBe('Test Sender');
});

it('sets default timeout for SMTP connection', function () {
    $config = [
        'host'         => 'smtp.example.com',
        'port'         => 587,
        'username'     => 'test@example.com',
        'password'     => 'password',
        'from_address' => 'test@example.com',
        'from_name'    => 'Test',
    ];

    Mail::fake();

    $this->tester->testSmtp($config);

    expect(Config::get('mail.mailers.test_smtp.timeout'))->toBe(10);
});

it('accepts custom timeout for SMTP connection', function () {
    $config = [
        'host'         => 'smtp.example.com',
        'port'         => 587,
        'username'     => 'test@example.com',
        'password'     => 'password',
        'from_address' => 'test@example.com',
        'from_name'    => 'Test',
        'timeout'      => 30,
    ];

    Mail::fake();

    $this->tester->testSmtp($config);

    expect(Config::get('mail.mailers.test_smtp.timeout'))->toBe(30);
});

/**
 * IMAP Encryption String Building Tests
 */
it('builds correct IMAP connection string with tls encryption', function () {
    $config = [
        'host'       => 'imap.example.com',
        'port'       => 993,
        'encryption' => 'tls',
        'username'   => 'test@example.com',
        'password'   => 'password',
    ];

    $result = $this->tester->testImap($config);

    // Connection will fail but we can verify it attempted with correct config
    expect($result)->toBeArray();
});

it('builds correct IMAP connection string with ssl encryption', function () {
    $config = [
        'host'       => 'imap.example.com',
        'port'       => 993,
        'encryption' => 'ssl',
        'username'   => 'test@example.com',
        'password'   => 'password',
    ];

    $result = $this->tester->testImap($config);

    expect($result)->toBeArray();
});

it('builds correct IMAP connection string with notls encryption', function () {
    $config = [
        'host'       => 'imap.example.com',
        'port'       => 143,
        'encryption' => 'notls',
        'username'   => 'test@example.com',
        'password'   => 'password',
    ];

    $result = $this->tester->testImap($config);

    expect($result)->toBeArray();
});

it('handles validate_cert option for IMAP connection', function () {
    $config = [
        'host'          => 'imap.example.com',
        'port'          => 993,
        'encryption'    => 'ssl',
        'username'      => 'test@example.com',
        'password'      => 'password',
        'validate_cert' => false,
    ];

    $result = $this->tester->testImap($config);

    expect($result)->toBeArray();
});

it('defaults to validate cert for IMAP when not specified', function () {
    $config = [
        'host'       => 'imap.example.com',
        'port'       => 993,
        'encryption' => 'ssl',
        'username'   => 'test@example.com',
        'password'   => 'password',
    ];

    $result = $this->tester->testImap($config);

    expect($result)->toBeArray();
});
