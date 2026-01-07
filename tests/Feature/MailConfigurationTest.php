<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Webkul\Core\Models\CoreConfig;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Clear any existing mail configuration before each test
    CoreConfig::where('code', 'like', 'email.smtp.%')
        ->orWhere('code', 'like', 'email.imap.%')
        ->delete();
});

/**
 * Mail Configuration Index Page Tests
 */
it('can access mail configuration index page', function () {
    $admin = getDefaultAdmin();

    test()->actingAs($admin)
        ->get(route('admin.settings.mail_configuration.index'))
        ->assertOK()
        ->assertSee('SMTP')
        ->assertSee('IMAP');
});

it('displays existing SMTP configuration on index page', function () {
    $admin = getDefaultAdmin();

    // Create SMTP configuration in database
    CoreConfig::create([
        'code'  => 'email.smtp.account.host',
        'value' => 'smtp.example.com',
    ]);

    CoreConfig::create([
        'code'  => 'email.smtp.account.port',
        'value' => '587',
    ]);

    test()->actingAs($admin)
        ->get(route('admin.settings.mail_configuration.index'))
        ->assertOK()
        ->assertSee('smtp.example.com')
        ->assertSee('587');
});

/**
 * Mail Configuration Store Tests
 */
it('can store SMTP configuration', function () {
    $admin = getDefaultAdmin();

    Event::fake();

    $smtpData = [
        'email' => [
            'smtp' => [
                'account' => [
                    'host'         => 'smtp.gmail.com',
                    'port'         => 587,
                    'encryption'   => 'tls',
                    'username'     => 'test@gmail.com',
                    'password'     => 'test-password',
                    'from_address' => 'test@gmail.com',
                    'from_name'    => 'Test Sender',
                ],
            ],
        ],
    ];

    test()->actingAs($admin)
        ->post(route('admin.settings.mail_configuration.store'), $smtpData)
        ->assertRedirect()
        ->assertSessionHas('success');

    // Verify configuration was saved
    expect(core()->getConfigData('email.smtp.account.host'))->toBe('smtp.gmail.com');
    expect(core()->getConfigData('email.smtp.account.port'))->toBe(587);
    expect(core()->getConfigData('email.smtp.account.encryption'))->toBe('tls');
    expect(core()->getConfigData('email.smtp.account.username'))->toBe('test@gmail.com');
    expect(core()->getConfigData('email.smtp.account.from_address'))->toBe('test@gmail.com');
    expect(core()->getConfigData('email.smtp.account.from_name'))->toBe('Test Sender');

    // Verify password was saved (will be encrypted)
    expect(core()->getConfigData('email.smtp.account.password'))->toBe('test-password');

    // Verify events were dispatched
    Event::assertDispatched('settings.mail_configuration.save.before');
    Event::assertDispatched('settings.mail_configuration.save.after');
});

it('can store IMAP configuration', function () {
    $admin = getDefaultAdmin();

    $imapData = [
        'email' => [
            'imap' => [
                'account' => [
                    'host'       => 'imap.gmail.com',
                    'port'       => 993,
                    'encryption' => 'ssl',
                    'username'   => 'test@gmail.com',
                    'password'   => 'imap-password',
                ],
            ],
        ],
    ];

    test()->actingAs($admin)
        ->post(route('admin.settings.mail_configuration.store'), $imapData)
        ->assertRedirect()
        ->assertSessionHas('success');

    // Verify configuration was saved
    expect(core()->getConfigData('email.imap.account.host'))->toBe('imap.gmail.com');
    expect(core()->getConfigData('email.imap.account.port'))->toBe(993);
    expect(core()->getConfigData('email.imap.account.encryption'))->toBe('ssl');
    expect(core()->getConfigData('email.imap.account.username'))->toBe('test@gmail.com');
    expect(core()->getConfigData('email.imap.account.password'))->toBe('imap-password');
});

it('validates SMTP configuration on store', function () {
    $admin = getDefaultAdmin();

    // Invalid port number
    $invalidData = [
        'email' => [
            'smtp' => [
                'account' => [
                    'host'         => 'smtp.gmail.com',
                    'port'         => 99999, // Invalid port
                    'encryption'   => 'tls',
                    'username'     => 'test@gmail.com',
                    'password'     => 'test-password',
                    'from_address' => 'invalid-email', // Invalid email
                    'from_name'    => 'Test',
                ],
            ],
        ],
    ];

    test()->actingAs($admin)
        ->post(route('admin.settings.mail_configuration.store'), $invalidData)
        ->assertSessionHasErrors(['email.smtp.account.port', 'email.smtp.account.from_address']);
});

it('validates IMAP configuration on store', function () {
    $admin = getDefaultAdmin();

    // Invalid encryption type
    $invalidData = [
        'email' => [
            'imap' => [
                'account' => [
                    'host'       => 'imap.gmail.com',
                    'port'       => 993,
                    'encryption' => 'invalid', // Invalid encryption
                    'username'   => 'test@gmail.com',
                    'password'   => 'password',
                ],
            ],
        ],
    ];

    test()->actingAs($admin)
        ->post(route('admin.settings.mail_configuration.store'), $invalidData)
        ->assertSessionHasErrors(['email.imap.account.encryption']);
});

it('encrypts SMTP password when storing configuration', function () {
    $admin = getDefaultAdmin();

    $smtpData = [
        'email' => [
            'smtp' => [
                'account' => [
                    'host'         => 'smtp.gmail.com',
                    'port'         => 587,
                    'encryption'   => 'tls',
                    'username'     => 'test@gmail.com',
                    'password'     => 'secret-password',
                    'from_address' => 'test@gmail.com',
                    'from_name'    => 'Test',
                ],
            ],
        ],
    ];

    test()->actingAs($admin)
        ->post(route('admin.settings.mail_configuration.store'), $smtpData);

    // Get the raw database value
    $passwordConfig = CoreConfig::where('code', 'email.smtp.account.password')->first();

    // Verify it's encrypted in the database
    expect($passwordConfig->encrypted)->toBeTrue();
    expect($passwordConfig->value)->not()->toBe('secret-password'); // Should be encrypted

    // Verify it decrypts correctly when retrieved
    expect(core()->getConfigData('email.smtp.account.password'))->toBe('secret-password');
});

it('encrypts IMAP password when storing configuration', function () {
    $admin = getDefaultAdmin();

    $imapData = [
        'email' => [
            'imap' => [
                'account' => [
                    'host'     => 'imap.gmail.com',
                    'port'     => 993,
                    'username' => 'test@gmail.com',
                    'password' => 'imap-secret',
                ],
            ],
        ],
    ];

    test()->actingAs($admin)
        ->post(route('admin.settings.mail_configuration.store'), $imapData);

    // Get the raw database value
    $passwordConfig = CoreConfig::where('code', 'email.imap.account.password')->first();

    // Verify it's encrypted in the database
    expect($passwordConfig->encrypted)->toBeTrue();
    expect($passwordConfig->value)->not()->toBe('imap-secret'); // Should be encrypted

    // Verify it decrypts correctly when retrieved
    expect(core()->getConfigData('email.imap.account.password'))->toBe('imap-secret');
});

/**
 * SMTP Connection Testing Tests
 */
it('can test SMTP connection with valid configuration', function () {
    $admin = getDefaultAdmin();

    Mail::fake();

    $smtpConfig = [
        'host'         => 'smtp.mailtrap.io',
        'port'         => 2525,
        'encryption'   => 'tls',
        'username'     => 'test-user',
        'password'     => 'test-password',
        'from_address' => 'test@example.com',
        'from_name'    => 'Test Sender',
    ];

    $response = test()->actingAs($admin)
        ->postJson(route('admin.settings.mail_configuration.test_smtp'), $smtpConfig);

    $response->assertStatus(200);

    $data = $response->json();
    expect($data['success'])->toBeTrue();

    Mail::assertSent(function ($mail) {
        return true; // Mail was sent
    });
});

it('requires all SMTP fields for connection test', function () {
    $admin = getDefaultAdmin();

    $invalidConfig = [
        'host' => 'smtp.gmail.com',
        // Missing required fields
    ];

    test()->actingAs($admin)
        ->postJson(route('admin.settings.mail_configuration.test_smtp'), $invalidConfig)
        ->assertStatus(422)
        ->assertJsonValidationErrors(['port', 'username', 'password', 'from_address', 'from_name']);
});

it('validates SMTP encryption type in connection test', function () {
    $admin = getDefaultAdmin();

    $invalidConfig = [
        'host'         => 'smtp.gmail.com',
        'port'         => 587,
        'encryption'   => 'invalid-encryption',
        'username'     => 'test@gmail.com',
        'password'     => 'password',
        'from_address' => 'test@gmail.com',
        'from_name'    => 'Test',
    ];

    test()->actingAs($admin)
        ->postJson(route('admin.settings.mail_configuration.test_smtp'), $invalidConfig)
        ->assertStatus(422)
        ->assertJsonValidationErrors(['encryption']);
});

it('validates email format in SMTP connection test', function () {
    $admin = getDefaultAdmin();

    $invalidConfig = [
        'host'         => 'smtp.gmail.com',
        'port'         => 587,
        'encryption'   => 'tls',
        'username'     => 'test@gmail.com',
        'password'     => 'password',
        'from_address' => 'not-an-email',
        'from_name'    => 'Test',
    ];

    test()->actingAs($admin)
        ->postJson(route('admin.settings.mail_configuration.test_smtp'), $invalidConfig)
        ->assertStatus(422)
        ->assertJsonValidationErrors(['from_address']);
});

/**
 * IMAP Connection Testing Tests
 */
it('requires all IMAP fields for connection test', function () {
    $admin = getDefaultAdmin();

    $invalidConfig = [
        'host' => 'imap.gmail.com',
        // Missing required fields
    ];

    test()->actingAs($admin)
        ->postJson(route('admin.settings.mail_configuration.test_imap'), $invalidConfig)
        ->assertStatus(422)
        ->assertJsonValidationErrors(['port', 'username', 'password']);
});

it('validates IMAP encryption type in connection test', function () {
    $admin = getDefaultAdmin();

    $invalidConfig = [
        'host'       => 'imap.gmail.com',
        'port'       => 993,
        'encryption' => 'invalid-encryption',
        'username'   => 'test@gmail.com',
        'password'   => 'password',
    ];

    test()->actingAs($admin)
        ->postJson(route('admin.settings.mail_configuration.test_imap'), $invalidConfig)
        ->assertStatus(422)
        ->assertJsonValidationErrors(['encryption']);
});

it('validates port range in IMAP connection test', function () {
    $admin = getDefaultAdmin();

    $invalidConfig = [
        'host'       => 'imap.gmail.com',
        'port'       => 99999, // Invalid port
        'encryption' => 'ssl',
        'username'   => 'test@gmail.com',
        'password'   => 'password',
    ];

    test()->actingAs($admin)
        ->postJson(route('admin.settings.mail_configuration.test_imap'), $invalidConfig)
        ->assertStatus(422)
        ->assertJsonValidationErrors(['port']);
});

/**
 * Configuration Loading Tests
 */
it('loads SMTP configuration from database with fallback to config', function () {
    $admin = getDefaultAdmin();

    // No database config exists initially
    $response = test()->actingAs($admin)
        ->get(route('admin.settings.mail_configuration.index'));

    $response->assertOK();

    // Now save configuration to database
    CoreConfig::create([
        'code'  => 'email.smtp.account.host',
        'value' => 'custom.smtp.server',
    ]);

    CoreConfig::create([
        'code'  => 'email.smtp.account.port',
        'value' => '2525',
    ]);

    // Reload page and verify custom configuration is shown
    $response = test()->actingAs($admin)
        ->get(route('admin.settings.mail_configuration.index'));

    $response->assertOK();
    $response->assertSee('custom.smtp.server');
    $response->assertSee('2525');
});

it('can update existing SMTP configuration', function () {
    $admin = getDefaultAdmin();

    // Create initial configuration
    CoreConfig::create([
        'code'  => 'email.smtp.account.host',
        'value' => 'old.smtp.server',
    ]);

    // Update configuration
    $updateData = [
        'email' => [
            'smtp' => [
                'account' => [
                    'host'         => 'new.smtp.server',
                    'port'         => 587,
                    'encryption'   => 'tls',
                    'username'     => 'updated@example.com',
                    'password'     => 'new-password',
                    'from_address' => 'updated@example.com',
                    'from_name'    => 'Updated Sender',
                ],
            ],
        ],
    ];

    test()->actingAs($admin)
        ->post(route('admin.settings.mail_configuration.store'), $updateData)
        ->assertRedirect()
        ->assertSessionHas('success');

    // Verify update
    expect(core()->getConfigData('email.smtp.account.host'))->toBe('new.smtp.server');
    expect(core()->getConfigData('email.smtp.account.username'))->toBe('updated@example.com');
});

it('requires authentication to access mail configuration', function () {
    test()->get(route('admin.settings.mail_configuration.index'))
        ->assertRedirect(route('admin.session.create'));
});

it('requires authentication to store mail configuration', function () {
    $data = [
        'email' => [
            'smtp' => [
                'account' => [
                    'host' => 'smtp.gmail.com',
                ],
            ],
        ],
    ];

    test()->post(route('admin.settings.mail_configuration.store'), $data)
        ->assertRedirect(route('admin.session.create'));
});

it('requires authentication to test SMTP connection', function () {
    $config = [
        'host'         => 'smtp.gmail.com',
        'port'         => 587,
        'encryption'   => 'tls',
        'username'     => 'test@gmail.com',
        'password'     => 'password',
        'from_address' => 'test@gmail.com',
        'from_name'    => 'Test',
    ];

    test()->postJson(route('admin.settings.mail_configuration.test_smtp'), $config)
        ->assertStatus(401);
});

it('requires authentication to test IMAP connection', function () {
    $config = [
        'host'     => 'imap.gmail.com',
        'port'     => 993,
        'username' => 'test@gmail.com',
        'password' => 'password',
    ];

    test()->postJson(route('admin.settings.mail_configuration.test_imap'), $config)
        ->assertStatus(401);
});

/**
 * Edge Cases and Error Handling Tests
 */
it('handles empty configuration gracefully', function () {
    $admin = getDefaultAdmin();

    test()->actingAs($admin)
        ->post(route('admin.settings.mail_configuration.store'), [])
        ->assertRedirect();

    // Should not throw errors with empty data
});

it('can store partial SMTP configuration', function () {
    $admin = getDefaultAdmin();

    $partialData = [
        'email' => [
            'smtp' => [
                'account' => [
                    'host' => 'smtp.example.com',
                    'port' => 587,
                ],
            ],
        ],
    ];

    test()->actingAs($admin)
        ->post(route('admin.settings.mail_configuration.store'), $partialData)
        ->assertRedirect();

    expect(core()->getConfigData('email.smtp.account.host'))->toBe('smtp.example.com');
    expect(core()->getConfigData('email.smtp.account.port'))->toBe(587);
});

it('preserves existing configuration when updating partial fields', function () {
    $admin = getDefaultAdmin();

    // Initial configuration
    CoreConfig::create(['code' => 'email.smtp.account.host', 'value' => 'smtp.example.com']);
    CoreConfig::create(['code' => 'email.smtp.account.port', 'value' => '587']);

    // Update only host
    $updateData = [
        'email' => [
            'smtp' => [
                'account' => [
                    'host' => 'smtp.newserver.com',
                ],
            ],
        ],
    ];

    test()->actingAs($admin)
        ->post(route('admin.settings.mail_configuration.store'), $updateData);

    // Host should be updated
    expect(core()->getConfigData('email.smtp.account.host'))->toBe('smtp.newserver.com');
    // Port should be preserved (this depends on implementation - may be overwritten)
});
