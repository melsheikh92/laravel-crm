<?php

namespace Webkul\Marketplace\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SecurityScanner
{
    /**
     * Dangerous PHP functions that should be flagged.
     */
    protected array $dangerousFunctions = [
        'eval',
        'exec',
        'system',
        'shell_exec',
        'passthru',
        'popen',
        'proc_open',
        'pcntl_exec',
        'assert',
        'create_function',
        'include',
        'include_once',
        'require',
        'require_once',
        'file_get_contents',
        'file_put_contents',
        'fopen',
        'readfile',
        'unlink',
        'rmdir',
        'chmod',
        'chown',
        'chgrp',
        'symlink',
        'link',
        'dl',
        'extract',
        'parse_str',
        'putenv',
        'ini_set',
        'mail',
        'header',
        'curl_exec',
        'curl_multi_exec',
    ];

    /**
     * Critical dangerous functions that are always blocked.
     */
    protected array $criticalFunctions = [
        'eval',
        'exec',
        'system',
        'shell_exec',
        'passthru',
        'proc_open',
        'pcntl_exec',
    ];

    /**
     * Patterns to detect common vulnerabilities.
     */
    protected array $vulnerabilityPatterns = [
        'sql_injection' => [
            '/DB::raw\s*\(\s*\$/',
            '/DB::select\s*\(\s*[\'"].*\$/',
            '/->whereRaw\s*\(\s*[\'"].*\$/',
            '/->havingRaw\s*\(\s*[\'"].*\$/',
            '/->orderByRaw\s*\(\s*[\'"].*\$/',
        ],
        'xss' => [
            '/echo\s+\$/',
            '/print\s+\$/',
            '/\{\{\{\s*\$/',  // Triple curly braces in Blade (unescaped)
        ],
        'path_traversal' => [
            '/\.\.[\/\\\\]/',
            '/\$_GET\[.*\].*\.(php|inc|conf)/',
            '/\$_POST\[.*\].*\.(php|inc|conf)/',
        ],
        'code_injection' => [
            '/eval\s*\(\s*\$/',
            '/assert\s*\(\s*\$/',
            '/create_function\s*\(/',
            '/preg_replace\s*\(.*\/e/',
        ],
        'command_injection' => [
            '/system\s*\(\s*\$/',
            '/exec\s*\(\s*\$/',
            '/shell_exec\s*\(\s*\$/',
            '/passthru\s*\(\s*\$/',
        ],
    ];

    /**
     * Required files for a valid extension package.
     */
    protected array $requiredFiles = [
        'composer.json',
        'src',
    ];

    /**
     * Create a new service instance.
     */
    public function __construct(
        protected CompatibilityChecker $compatibilityChecker
    ) {}

    /**
     * Perform a comprehensive security scan on an extension package.
     *
     * @param  string  $packagePath
     * @return array
     */
    public function scan(string $packagePath): array
    {
        if (!File::isDirectory($packagePath)) {
            return [
                'success' => false,
                'error' => 'Package path does not exist or is not a directory',
            ];
        }

        $results = [
            'success' => true,
            'passed' => true,
            'issues' => [],
            'warnings' => [],
            'info' => [],
            'scans' => [],
        ];

        // 1. Validate package structure
        $structureCheck = $this->validateStructure($packagePath);
        $results['scans']['structure'] = $structureCheck;
        if (!$structureCheck['valid']) {
            $results['passed'] = false;
            $results['issues'] = array_merge($results['issues'], $structureCheck['errors'] ?? []);
        }

        // 2. Scan for dangerous functions
        $functionsCheck = $this->scanDangerousFunctions($packagePath);
        $results['scans']['dangerous_functions'] = $functionsCheck;
        if ($functionsCheck['has_critical']) {
            $results['passed'] = false;
            $results['issues'] = array_merge($results['issues'], $functionsCheck['critical'] ?? []);
        }
        if (!empty($functionsCheck['warnings'])) {
            $results['warnings'] = array_merge($results['warnings'], $functionsCheck['warnings']);
        }

        // 3. Scan for common vulnerabilities
        $vulnerabilitiesCheck = $this->scanVulnerabilities($packagePath);
        $results['scans']['vulnerabilities'] = $vulnerabilitiesCheck;
        if ($vulnerabilitiesCheck['has_vulnerabilities']) {
            $results['passed'] = false;
            $results['issues'] = array_merge($results['issues'], $vulnerabilitiesCheck['issues'] ?? []);
        }

        // 4. Validate composer.json
        $composerCheck = $this->validateComposerJson($packagePath);
        $results['scans']['composer'] = $composerCheck;
        if (!$composerCheck['valid']) {
            $results['passed'] = false;
            $results['issues'] = array_merge($results['issues'], $composerCheck['errors'] ?? []);
        }

        // 5. Check dependencies for known vulnerabilities
        $dependencyCheck = $this->checkDependencyVulnerabilities($packagePath);
        $results['scans']['dependencies'] = $dependencyCheck;
        if ($dependencyCheck['has_vulnerabilities']) {
            $results['warnings'] = array_merge($results['warnings'], $dependencyCheck['vulnerabilities'] ?? []);
        }

        // 6. Check file permissions and ownership
        $permissionsCheck = $this->checkFilePermissions($packagePath);
        $results['scans']['permissions'] = $permissionsCheck;
        if (!empty($permissionsCheck['warnings'])) {
            $results['warnings'] = array_merge($results['warnings'], $permissionsCheck['warnings']);
        }

        // 7. Scan for malicious patterns
        $malwareCheck = $this->scanMalwarePatterns($packagePath);
        $results['scans']['malware'] = $malwareCheck;
        if ($malwareCheck['has_malware']) {
            $results['passed'] = false;
            $results['issues'] = array_merge($results['issues'], $malwareCheck['patterns'] ?? []);
        }

        // Add summary
        $results['summary'] = [
            'total_issues' => count($results['issues']),
            'total_warnings' => count($results['warnings']),
            'passed' => $results['passed'],
            'scanned_at' => now()->toIso8601String(),
        ];

        return $results;
    }

    /**
     * Validate extension package structure.
     *
     * @param  string  $packagePath
     * @return array
     */
    public function validateStructure(string $packagePath): array
    {
        $errors = [];
        $valid = true;

        // Check for required files/directories
        foreach ($this->requiredFiles as $required) {
            $path = $packagePath . '/' . $required;
            if (!File::exists($path)) {
                $valid = false;
                $errors[] = "Required file/directory missing: {$required}";
            }
        }

        // Check for suspicious file names
        $suspiciousPatterns = ['backdoor', 'shell', 'c99', 'r57', 'webshell'];
        $files = File::allFiles($packagePath);

        foreach ($files as $file) {
            $filename = strtolower($file->getFilename());
            foreach ($suspiciousPatterns as $pattern) {
                if (strpos($filename, $pattern) !== false) {
                    $valid = false;
                    $errors[] = "Suspicious filename detected: {$file->getRelativePathname()}";
                }
            }

            // Check for files with double extensions
            if (preg_match('/\.(php|phtml|php3|php4|php5|phps)\.(jpg|jpeg|png|gif|txt)$/i', $filename)) {
                $valid = false;
                $errors[] = "Suspicious double extension detected: {$file->getRelativePathname()}";
            }
        }

        return [
            'valid' => $valid,
            'errors' => $errors,
            'files_checked' => count($files),
        ];
    }

    /**
     * Scan for dangerous PHP functions.
     *
     * @param  string  $packagePath
     * @return array
     */
    public function scanDangerousFunctions(string $packagePath): array
    {
        $critical = [];
        $warnings = [];
        $hasCritical = false;

        $phpFiles = $this->getPhpFiles($packagePath);

        foreach ($phpFiles as $file) {
            $content = File::get($file->getRealPath());
            $relativePath = $file->getRelativePathname();

            // Check for critical functions
            foreach ($this->criticalFunctions as $function) {
                if (preg_match('/\b' . preg_quote($function, '/') . '\s*\(/i', $content)) {
                    $hasCritical = true;
                    $critical[] = "Critical function '{$function}' found in {$relativePath}";
                }
            }

            // Check for other dangerous functions (warnings only)
            foreach ($this->dangerousFunctions as $function) {
                if (in_array($function, $this->criticalFunctions)) {
                    continue; // Already checked
                }

                if (preg_match('/\b' . preg_quote($function, '/') . '\s*\(/i', $content)) {
                    $warnings[] = "Potentially dangerous function '{$function}' found in {$relativePath}";
                }
            }
        }

        return [
            'has_critical' => $hasCritical,
            'critical' => $critical,
            'warnings' => $warnings,
            'files_scanned' => count($phpFiles),
        ];
    }

    /**
     * Scan for common vulnerability patterns.
     *
     * @param  string  $packagePath
     * @return array
     */
    public function scanVulnerabilities(string $packagePath): array
    {
        $issues = [];
        $hasVulnerabilities = false;

        $phpFiles = $this->getPhpFiles($packagePath);

        foreach ($phpFiles as $file) {
            $content = File::get($file->getRealPath());
            $relativePath = $file->getRelativePathname();

            foreach ($this->vulnerabilityPatterns as $type => $patterns) {
                foreach ($patterns as $pattern) {
                    if (preg_match($pattern, $content)) {
                        $hasVulnerabilities = true;
                        $issues[] = "Potential {$type} vulnerability detected in {$relativePath}";
                    }
                }
            }

            // Check for hardcoded credentials
            if (preg_match('/(password|passwd|pwd|secret|token|api[_-]?key)\s*=\s*[\'"][^\'"]{8,}[\'"]/i', $content)) {
                $hasVulnerabilities = true;
                $issues[] = "Potential hardcoded credentials found in {$relativePath}";
            }

            // Check for use of $_GET, $_POST without sanitization in sensitive operations
            if (preg_match('/\$_(GET|POST|REQUEST|COOKIE)\[.*?\](?!.*\b(sanitize|escape|filter|validate|clean)\b)/i', $content)) {
                // This is a basic check - could have false positives
                $issues[] = "Unsanitized user input detected in {$relativePath} (verify manually)";
            }
        }

        return [
            'has_vulnerabilities' => $hasVulnerabilities,
            'issues' => $issues,
            'files_scanned' => count($phpFiles),
        ];
    }

    /**
     * Validate composer.json structure and content.
     *
     * @param  string  $packagePath
     * @return array
     */
    public function validateComposerJson(string $packagePath): array
    {
        $composerPath = $packagePath . '/composer.json';
        $errors = [];
        $valid = true;

        if (!File::exists($composerPath)) {
            return [
                'valid' => false,
                'errors' => ['composer.json file not found'],
            ];
        }

        try {
            $content = File::get($composerPath);
            $composer = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return [
                    'valid' => false,
                    'errors' => ['Invalid JSON in composer.json: ' . json_last_error_msg()],
                ];
            }

            // Validate required fields
            $requiredFields = ['name', 'description', 'type', 'require', 'autoload'];
            foreach ($requiredFields as $field) {
                if (!isset($composer[$field])) {
                    $valid = false;
                    $errors[] = "Required field '{$field}' missing in composer.json";
                }
            }

            // Validate package name format
            if (isset($composer['name']) && !preg_match('/^[a-z0-9-]+\/[a-z0-9-]+$/', $composer['name'])) {
                $valid = false;
                $errors[] = "Invalid package name format in composer.json";
            }

            // Validate package type
            if (isset($composer['type'])) {
                $validTypes = ['library', 'project', 'metapackage', 'composer-plugin', 'laravel-package'];
                if (!in_array($composer['type'], $validTypes)) {
                    $errors[] = "Unusual package type '{$composer['type']}' in composer.json (verify manually)";
                }
            }

            // Check for suspicious scripts
            if (isset($composer['scripts'])) {
                foreach ($composer['scripts'] as $event => $scripts) {
                    $scriptArray = is_array($scripts) ? $scripts : [$scripts];
                    foreach ($scriptArray as $script) {
                        if (is_string($script)) {
                            // Check for shell commands in scripts
                            if (preg_match('/(rm\s+-rf|curl.*\|.*sh|wget.*\|.*sh|eval|exec)/i', $script)) {
                                $valid = false;
                                $errors[] = "Suspicious script command in composer.json: {$script}";
                            }
                        }
                    }
                }
            }

            return [
                'valid' => $valid,
                'errors' => $errors,
                'data' => $composer,
            ];
        } catch (\Exception $e) {
            return [
                'valid' => false,
                'errors' => ['Error reading composer.json: ' . $e->getMessage()],
            ];
        }
    }

    /**
     * Check dependencies for known vulnerabilities.
     *
     * @param  string  $packagePath
     * @return array
     */
    public function checkDependencyVulnerabilities(string $packagePath): array
    {
        $composerData = $this->compatibilityChecker->parseComposerJson($packagePath);

        if (!$composerData['success']) {
            return [
                'has_vulnerabilities' => false,
                'vulnerabilities' => [],
                'checked' => false,
                'error' => $composerData['error'] ?? 'Could not parse composer.json',
            ];
        }

        $vulnerabilities = [];
        $hasVulnerabilities = false;

        $requirements = array_merge(
            $composerData['data']['require'] ?? [],
            $composerData['data']['require-dev'] ?? []
        );

        // Check for known vulnerable packages (this is a simple check)
        // In production, you would integrate with a service like Snyk or GitHub Security Advisories
        $knownVulnerable = [
            'phpunit/phpunit' => ['4.0.0', '4.8.27', 'Arbitrary code execution vulnerability'],
            'symfony/http-kernel' => ['2.0.0', '2.8.51', 'HTTP header injection vulnerability'],
        ];

        foreach ($requirements as $package => $version) {
            // Skip PHP and extension requirements
            if ($package === 'php' || strpos($package, 'ext-') === 0) {
                continue;
            }

            // Check against known vulnerable packages
            if (isset($knownVulnerable[$package])) {
                $vulnInfo = $knownVulnerable[$package];
                $hasVulnerabilities = true;
                $vulnerabilities[] = "Package '{$package}' may have known vulnerabilities: {$vulnInfo[2]}";
            }

            // Flag deprecated packages
            $deprecatedPackages = ['kriswallsmith/assetic', 'jms/serializer-bundle'];
            if (in_array($package, $deprecatedPackages)) {
                $vulnerabilities[] = "Deprecated package '{$package}' detected - consider alternatives";
            }

            // Check for very old package versions (indicative of potential security issues)
            if (preg_match('/^[~^]?(\d+)\./', $version, $matches)) {
                $majorVersion = (int) $matches[1];
                if ($majorVersion < 2 && !in_array($package, ['php', 'laravel/framework'])) {
                    $vulnerabilities[] = "Very old version constraint for '{$package}': {$version} - may have security issues";
                }
            }
        }

        return [
            'has_vulnerabilities' => $hasVulnerabilities,
            'vulnerabilities' => $vulnerabilities,
            'packages_checked' => count($requirements),
            'checked' => true,
        ];
    }

    /**
     * Check file permissions for security issues.
     *
     * @param  string  $packagePath
     * @return array
     */
    public function checkFilePermissions(string $packagePath): array
    {
        $warnings = [];
        $files = File::allFiles($packagePath);

        foreach ($files as $file) {
            $perms = fileperms($file->getRealPath());
            $relativePath = $file->getRelativePathname();

            // Check if file is executable
            if ($perms & 0x0040) { // Owner has execute permission
                // PHP files generally shouldn't be executable
                if (preg_match('/\.php$/i', $file->getFilename())) {
                    $warnings[] = "PHP file has execute permission: {$relativePath}";
                }
            }

            // Check for world-writable files
            if ($perms & 0x0002) {
                $warnings[] = "File is world-writable: {$relativePath}";
            }
        }

        return [
            'warnings' => $warnings,
            'files_checked' => count($files),
        ];
    }

    /**
     * Scan for malware patterns.
     *
     * @param  string  $packagePath
     * @return array
     */
    public function scanMalwarePatterns(string $packagePath): array
    {
        $patterns = [];
        $hasMalware = false;

        $phpFiles = $this->getPhpFiles($packagePath);

        // Common malware patterns
        $malwareSignatures = [
            'base64_decode' => '/base64_decode\s*\(\s*[\'"][A-Za-z0-9+\/=]{100,}[\'"]\s*\)/',
            'gzinflate' => '/gzinflate\s*\(\s*base64_decode/',
            'str_rot13' => '/str_rot13\s*\(\s*[\'"][A-Za-z]{50,}[\'"]\s*\)/',
            'obfuscated_code' => '/\$[a-zA-Z0-9_]+\s*=\s*[\'"][A-Za-z0-9+\/=]{200,}[\'"]\s*;/',
            'encoded_eval' => '/(eval|assert)\s*\(\s*(base64_decode|gzinflate|str_rot13|gzuncompress)/',
            'iframe_injection' => '/<iframe[^>]*>.*?<\/iframe>/is',
            'hidden_iframe' => '/style\s*=\s*["\'].*?(display\s*:\s*none|visibility\s*:\s*hidden).*?<iframe/is',
        ];

        foreach ($phpFiles as $file) {
            $content = File::get($file->getRealPath());
            $relativePath = $file->getRelativePathname();

            foreach ($malwareSignatures as $type => $pattern) {
                if (preg_match($pattern, $content)) {
                    $hasMalware = true;
                    $patterns[] = "Potential malware pattern '{$type}' detected in {$relativePath}";
                }
            }

            // Check for extremely long lines (often used in obfuscation)
            $lines = explode("\n", $content);
            foreach ($lines as $lineNum => $line) {
                if (strlen($line) > 1000) {
                    $hasMalware = true;
                    $patterns[] = "Suspicious long line detected in {$relativePath}:" . ($lineNum + 1);
                    break;
                }
            }
        }

        return [
            'has_malware' => $hasMalware,
            'patterns' => $patterns,
            'files_scanned' => count($phpFiles),
        ];
    }

    /**
     * Get all PHP files in a directory.
     *
     * @param  string  $path
     * @return array
     */
    protected function getPhpFiles(string $path): array
    {
        $allFiles = File::allFiles($path);
        $phpFiles = [];

        foreach ($allFiles as $file) {
            if (in_array($file->getExtension(), ['php', 'php3', 'php4', 'php5', 'phtml'])) {
                $phpFiles[] = $file;
            }
        }

        return $phpFiles;
    }

    /**
     * Get a summary of security scan results.
     *
     * @param  array  $scanResults
     * @return string
     */
    public function getScanSummary(array $scanResults): string
    {
        if (!$scanResults['success']) {
            return 'Security scan failed: ' . ($scanResults['error'] ?? 'Unknown error');
        }

        $summary = [];

        if ($scanResults['passed']) {
            $summary[] = '✓ Security scan passed';
        } else {
            $summary[] = '✗ Security scan failed';
        }

        if (isset($scanResults['summary'])) {
            $summary[] = "Issues: {$scanResults['summary']['total_issues']}";
            $summary[] = "Warnings: {$scanResults['summary']['total_warnings']}";
        }

        return implode(' | ', $summary);
    }

    /**
     * Get detailed scan report as formatted text.
     *
     * @param  array  $scanResults
     * @return string
     */
    public function getDetailedReport(array $scanResults): string
    {
        if (!$scanResults['success']) {
            return "Security Scan Failed\n" .
                   "Error: " . ($scanResults['error'] ?? 'Unknown error') . "\n";
        }

        $report = [];
        $report[] = "=== Security Scan Report ===";
        $report[] = "Status: " . ($scanResults['passed'] ? 'PASSED' : 'FAILED');
        $report[] = "Scanned: " . ($scanResults['summary']['scanned_at'] ?? 'N/A');
        $report[] = "";

        if (!empty($scanResults['issues'])) {
            $report[] = "CRITICAL ISSUES (" . count($scanResults['issues']) . "):";
            foreach ($scanResults['issues'] as $issue) {
                $report[] = "  ✗ " . $issue;
            }
            $report[] = "";
        }

        if (!empty($scanResults['warnings'])) {
            $report[] = "WARNINGS (" . count($scanResults['warnings']) . "):";
            foreach ($scanResults['warnings'] as $warning) {
                $report[] = "  ⚠ " . $warning;
            }
            $report[] = "";
        }

        if (empty($scanResults['issues']) && empty($scanResults['warnings'])) {
            $report[] = "No security issues detected.";
        }

        return implode("\n", $report);
    }
}
