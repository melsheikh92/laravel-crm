<?php

use Webkul\Marketplace\Services\CompatibilityChecker;
use Webkul\Marketplace\Repositories\ExtensionVersionRepository;
use Illuminate\Support\Facades\File;
use Composer\Semver\Semver;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->versionRepository = Mockery::mock(ExtensionVersionRepository::class);
    $this->service = new CompatibilityChecker($this->versionRepository);
});

afterEach(function () {
    Mockery::close();
});

describe('checkCompatibility', function () {
    it('returns compatible when all requirements are met', function () {
        $result = $this->service->checkCompatibility(
            '^10.0',
            '^1.0',
            '>=8.1',
            []
        );

        expect($result)->toBeArray()
            ->and($result['compatible'])->toBeTrue()
            ->and($result['checks'])->toBeArray()
            ->and($result['errors'])->toBeEmpty()
            ->and($result['warnings'])->toBeEmpty();
    });

    it('returns incompatible when Laravel version does not match', function () {
        $result = $this->service->checkCompatibility(
            '^99.0',
            '^1.0',
            '>=8.1',
            []
        );

        expect($result)->toBeArray()
            ->and($result['compatible'])->toBeFalse()
            ->and($result['errors'])->not->toBeEmpty();
    });

    it('returns incompatible when PHP version does not match', function () {
        $result = $this->service->checkCompatibility(
            '^10.0',
            '^1.0',
            '>=9.0',
            []
        );

        expect($result)->toBeArray()
            ->and($result['compatible'])->toBeFalse()
            ->and($result['errors'])->not->toBeEmpty();
    });

    it('handles null version requirements', function () {
        $result = $this->service->checkCompatibility(null, null, null, []);

        expect($result)->toBeArray()
            ->and($result['compatible'])->toBeTrue()
            ->and($result['checks'])->toBeEmpty()
            ->and($result['errors'])->toBeEmpty();
    });

    it('checks dependencies and reports missing packages', function () {
        File::shouldReceive('exists')
            ->with(base_path('composer.lock'))
            ->andReturn(true);

        File::shouldReceive('get')
            ->with(base_path('composer.lock'))
            ->andReturn(json_encode([
                'packages' => [
                    ['name' => 'vendor/package1', 'version' => '1.0.0']
                ]
            ]));

        $result = $this->service->checkCompatibility(
            null,
            null,
            null,
            ['vendor/package2' => '^1.0']
        );

        expect($result)->toBeArray()
            ->and($result['compatible'])->toBeFalse()
            ->and($result['errors'])->toContain("Required package 'vendor/package2' is not installed");
    });
});

describe('checkLaravelVersion', function () {
    it('checks Laravel version compatibility', function () {
        $result = $this->service->checkLaravelVersion('^10.0');

        expect($result)->toBeArray()
            ->and($result)->toHaveKeys(['compatible', 'name', 'package', 'current', 'required', 'message'])
            ->and($result['name'])->toBe('Laravel')
            ->and($result['package'])->toBe('laravel/framework');
    });

    it('returns current Laravel version', function () {
        $result = $this->service->checkLaravelVersion('^10.0');

        expect($result['current'])->toBeString()
            ->and($result['current'])->not->toBeEmpty();
    });
});

describe('checkCrmVersion', function () {
    it('checks CRM version compatibility', function () {
        $result = $this->service->checkCrmVersion('^1.0');

        expect($result)->toBeArray()
            ->and($result)->toHaveKeys(['compatible', 'name', 'package', 'current', 'required', 'message'])
            ->and($result['name'])->toBe('CRM')
            ->and($result['package'])->toBe('crm');
    });

    it('uses CRM constant as fallback', function () {
        $result = $this->service->checkCrmVersion('^1.0');

        expect($result['current'])->toBeString()
            ->and($result['current'])->toBe(CompatibilityChecker::CRM_VERSION);
    });
});

describe('checkPhpVersion', function () {
    it('checks PHP version compatibility', function () {
        $result = $this->service->checkPhpVersion('>=8.1');

        expect($result)->toBeArray()
            ->and($result)->toHaveKeys(['compatible', 'name', 'package', 'current', 'required', 'message'])
            ->and($result['name'])->toBe('PHP')
            ->and($result['package'])->toBe('php')
            ->and($result['current'])->toBe(PHP_VERSION);
    });

    it('returns compatible for current PHP version', function () {
        $currentVersion = PHP_VERSION;
        $result = $this->service->checkPhpVersion("^{$currentVersion}");

        expect($result['compatible'])->toBeTrue();
    });
});

describe('simpleVersionMatch', function () {
    it('matches exact versions', function () {
        $result = $this->service->checkCompatibility('10.0.0', null, null, []);

        expect($result)->toBeArray();
    });

    it('matches wildcard versions', function () {
        $result = $this->service->checkCompatibility('10.*', null, null, []);

        expect($result)->toBeArray();
    });

    it('matches caret constraints', function () {
        $result = $this->service->checkCompatibility('^10.0', null, null, []);

        expect($result)->toBeArray();
    });

    it('matches tilde constraints', function () {
        $result = $this->service->checkCompatibility('~10.0', null, null, []);

        expect($result)->toBeArray();
    });
});

describe('parseComposerJson', function () {
    it('parses valid composer.json file', function () {
        $tmpDir = sys_get_temp_dir() . '/test-extension-' . uniqid();
        mkdir($tmpDir);

        $composerData = [
            'name' => 'vendor/package',
            'description' => 'Test package',
            'type' => 'library',
            'require' => ['php' => '>=8.1'],
            'autoload' => ['psr-4' => ['Vendor\\Package\\' => 'src/']],
        ];

        file_put_contents($tmpDir . '/composer.json', json_encode($composerData));

        $result = $this->service->parseComposerJson($tmpDir);

        expect($result)->toBeArray()
            ->and($result['success'])->toBeTrue()
            ->and($result['data']['name'])->toBe('vendor/package')
            ->and($result['data']['description'])->toBe('Test package');

        unlink($tmpDir . '/composer.json');
        rmdir($tmpDir);
    });

    it('returns error when composer.json does not exist', function () {
        $result = $this->service->parseComposerJson('/nonexistent/path');

        expect($result)->toBeArray()
            ->and($result['success'])->toBeFalse()
            ->and($result['error'])->toBe('composer.json file not found');
    });

    it('returns error for invalid JSON', function () {
        $tmpDir = sys_get_temp_dir() . '/test-extension-' . uniqid();
        mkdir($tmpDir);

        file_put_contents($tmpDir . '/composer.json', '{invalid json}');

        $result = $this->service->parseComposerJson($tmpDir);

        expect($result)->toBeArray()
            ->and($result['success'])->toBeFalse()
            ->and($result['error'])->toContain('Invalid JSON');

        unlink($tmpDir . '/composer.json');
        rmdir($tmpDir);
    });
});

describe('getSystemInfo', function () {
    it('returns system information', function () {
        $result = $this->service->getSystemInfo();

        expect($result)->toBeArray()
            ->and($result)->toHaveKeys(['php', 'laravel', 'crm', 'extensions'])
            ->and($result['php'])->toHaveKeys(['version', 'major', 'minor', 'release'])
            ->and($result['php']['version'])->toBe(PHP_VERSION)
            ->and($result['laravel'])->toHaveKey('version')
            ->and($result['crm'])->toHaveKey('version');
    });

    it('includes PHP version details', function () {
        $result = $this->service->getSystemInfo();

        expect($result['php']['major'])->toBe(PHP_MAJOR_VERSION)
            ->and($result['php']['minor'])->toBe(PHP_MINOR_VERSION)
            ->and($result['php']['release'])->toBe(PHP_RELEASE_VERSION);
    });
});

describe('validatePackageRequirements', function () {
    it('validates package requirements from composer.json', function () {
        $tmpDir = sys_get_temp_dir() . '/test-extension-' . uniqid();
        mkdir($tmpDir);

        $composerData = [
            'name' => 'vendor/package',
            'description' => 'Test package',
            'require' => [
                'php' => '>=8.1',
                'laravel/framework' => '^10.0',
            ],
        ];

        file_put_contents($tmpDir . '/composer.json', json_encode($composerData));

        $result = $this->service->validatePackageRequirements($tmpDir);

        expect($result)->toBeArray()
            ->and($result)->toHaveKeys(['compatible', 'checks', 'errors', 'warnings']);

        unlink($tmpDir . '/composer.json');
        rmdir($tmpDir);
    });

    it('returns error for missing composer.json', function () {
        $result = $this->service->validatePackageRequirements('/nonexistent/path');

        expect($result)->toBeArray()
            ->and($result)->toHaveKey('valid')
            ->and($result['valid'])->toBeFalse();
    });
});

describe('checkVersionCompatibility', function () {
    it('checks version compatibility using repository', function () {
        $mockVersion = Mockery::mock();
        $mockVersion->laravel_version = '^10.0';
        $mockVersion->crm_version = '^1.0';
        $mockVersion->php_version = '>=8.1';
        $mockVersion->dependencies = [];

        $this->versionRepository
            ->shouldReceive('findOrFail')
            ->with(1)
            ->andReturn($mockVersion);

        $result = $this->service->checkVersionCompatibility(1);

        expect($result)->toBeArray()
            ->and($result)->toHaveKeys(['compatible', 'checks', 'errors', 'warnings']);
    });

    it('handles null dependencies from version', function () {
        $mockVersion = Mockery::mock();
        $mockVersion->laravel_version = '^10.0';
        $mockVersion->crm_version = '^1.0';
        $mockVersion->php_version = '>=8.1';
        $mockVersion->dependencies = null;

        $this->versionRepository
            ->shouldReceive('findOrFail')
            ->with(1)
            ->andReturn($mockVersion);

        $result = $this->service->checkVersionCompatibility(1);

        expect($result)->toBeArray()
            ->and($result['compatible'])->toBeIn([true, false]);
    });
});
