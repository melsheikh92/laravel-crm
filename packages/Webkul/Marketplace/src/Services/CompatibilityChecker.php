<?php

namespace Webkul\Marketplace\Services;

use Composer\Semver\Semver;
use Illuminate\Support\Facades\File;
use Webkul\Marketplace\Repositories\ExtensionVersionRepository;

class CompatibilityChecker
{
    /**
     * CRM version identifier.
     */
    const CRM_VERSION = '1.0.0';

    /**
     * Create a new service instance.
     */
    public function __construct(
        protected ExtensionVersionRepository $extensionVersionRepository
    ) {}

    /**
     * Check if an extension version is compatible with the current system.
     *
     * @param  int  $versionId
     * @return array
     */
    public function checkVersionCompatibility(int $versionId): array
    {
        $version = $this->extensionVersionRepository->findOrFail($versionId);

        return $this->checkCompatibility(
            $version->laravel_version,
            $version->crm_version,
            $version->php_version,
            $version->dependencies ?? []
        );
    }

    /**
     * Check if requirements are compatible with the current system.
     *
     * @param  string|null  $laravelVersion
     * @param  string|null  $crmVersion
     * @param  string|null  $phpVersion
     * @param  array  $dependencies
     * @return array
     */
    public function checkCompatibility(
        ?string $laravelVersion = null,
        ?string $crmVersion = null,
        ?string $phpVersion = null,
        array $dependencies = []
    ): array {
        $results = [
            'compatible' => true,
            'checks' => [],
            'errors' => [],
            'warnings' => [],
        ];

        // Check Laravel version
        if ($laravelVersion) {
            $laravelCheck = $this->checkLaravelVersion($laravelVersion);
            $results['checks']['laravel'] = $laravelCheck;

            if (!$laravelCheck['compatible']) {
                $results['compatible'] = false;
                $results['errors'][] = $laravelCheck['message'];
            }
        }

        // Check CRM version
        if ($crmVersion) {
            $crmCheck = $this->checkCrmVersion($crmVersion);
            $results['checks']['crm'] = $crmCheck;

            if (!$crmCheck['compatible']) {
                $results['compatible'] = false;
                $results['errors'][] = $crmCheck['message'];
            }
        }

        // Check PHP version
        if ($phpVersion) {
            $phpCheck = $this->checkPhpVersion($phpVersion);
            $results['checks']['php'] = $phpCheck;

            if (!$phpCheck['compatible']) {
                $results['compatible'] = false;
                $results['errors'][] = $phpCheck['message'];
            }
        }

        // Check dependencies
        if (!empty($dependencies)) {
            $dependenciesCheck = $this->checkDependencies($dependencies);
            $results['checks']['dependencies'] = $dependenciesCheck;

            if (!$dependenciesCheck['compatible']) {
                $results['compatible'] = false;
                $results['errors'] = array_merge($results['errors'], $dependenciesCheck['errors'] ?? []);
            }

            if (!empty($dependenciesCheck['warnings'])) {
                $results['warnings'] = array_merge($results['warnings'], $dependenciesCheck['warnings']);
            }
        }

        return $results;
    }

    /**
     * Check Laravel version compatibility.
     *
     * @param  string  $requiredVersion
     * @return array
     */
    public function checkLaravelVersion(string $requiredVersion): array
    {
        $currentVersion = app()->version();

        return $this->checkVersionConstraint(
            $currentVersion,
            $requiredVersion,
            'Laravel',
            'laravel/framework'
        );
    }

    /**
     * Check CRM version compatibility.
     *
     * @param  string  $requiredVersion
     * @return array
     */
    public function checkCrmVersion(string $requiredVersion): array
    {
        $currentVersion = $this->getCrmVersion();

        return $this->checkVersionConstraint(
            $currentVersion,
            $requiredVersion,
            'CRM',
            'crm'
        );
    }

    /**
     * Check PHP version compatibility.
     *
     * @param  string  $requiredVersion
     * @return array
     */
    public function checkPhpVersion(string $requiredVersion): array
    {
        $currentVersion = PHP_VERSION;

        return $this->checkVersionConstraint(
            $currentVersion,
            $requiredVersion,
            'PHP',
            'php'
        );
    }

    /**
     * Check dependencies compatibility.
     *
     * @param  array  $dependencies
     * @return array
     */
    public function checkDependencies(array $dependencies): array
    {
        $result = [
            'compatible' => true,
            'errors' => [],
            'warnings' => [],
            'details' => [],
        ];

        $installedPackages = $this->getInstalledPackages();

        foreach ($dependencies as $package => $versionConstraint) {
            if (!isset($installedPackages[$package])) {
                $result['compatible'] = false;
                $result['errors'][] = "Required package '{$package}' is not installed";
                $result['details'][$package] = [
                    'installed' => false,
                    'required' => $versionConstraint,
                ];
                continue;
            }

            $installedVersion = $installedPackages[$package];
            $check = $this->checkVersionConstraint(
                $installedVersion,
                $versionConstraint,
                $package,
                $package
            );

            $result['details'][$package] = [
                'installed' => true,
                'version' => $installedVersion,
                'required' => $versionConstraint,
                'compatible' => $check['compatible'],
            ];

            if (!$check['compatible']) {
                $result['compatible'] = false;
                $result['errors'][] = $check['message'];
            }
        }

        return $result;
    }

    /**
     * Check if a version satisfies a constraint.
     *
     * @param  string  $currentVersion
     * @param  string  $constraint
     * @param  string  $name
     * @param  string  $package
     * @return array
     */
    protected function checkVersionConstraint(
        string $currentVersion,
        string $constraint,
        string $name,
        string $package
    ): array {
        $constraint = trim($constraint);
        $compatible = false;

        try {
            // Use Composer's Semver library for accurate version comparison
            if (class_exists(Semver::class)) {
                $compatible = Semver::satisfies($currentVersion, $constraint);
            } else {
                // Fallback to simple version matching
                $compatible = $this->simpleVersionMatch($currentVersion, $constraint);
            }
        } catch (\Exception $e) {
            // If semver parsing fails, try simple version matching
            $compatible = $this->simpleVersionMatch($currentVersion, $constraint);
        }

        return [
            'compatible' => $compatible,
            'name' => $name,
            'package' => $package,
            'current' => $currentVersion,
            'required' => $constraint,
            'message' => $compatible
                ? "{$name} version {$currentVersion} satisfies requirement {$constraint}"
                : "{$name} version {$currentVersion} does not satisfy requirement {$constraint}",
        ];
    }

    /**
     * Simple version matching fallback.
     *
     * @param  string  $currentVersion
     * @param  string  $constraint
     * @return bool
     */
    protected function simpleVersionMatch(string $currentVersion, string $constraint): bool
    {
        // Remove 'v' prefix if present
        $currentVersion = ltrim($currentVersion, 'v');
        $constraint = ltrim($constraint, 'v');

        // Exact match
        if (strpos($constraint, '*') === false &&
            strpos($constraint, '^') === false &&
            strpos($constraint, '~') === false &&
            strpos($constraint, '>=') === false &&
            strpos($constraint, '<=') === false &&
            strpos($constraint, '>') === false &&
            strpos($constraint, '<') === false &&
            strpos($constraint, '|') === false) {
            return version_compare($currentVersion, $constraint, '=');
        }

        // Wildcard match (e.g., 8.*, 10.*)
        if (strpos($constraint, '*') !== false) {
            $pattern = str_replace('*', '', $constraint);
            return strpos($currentVersion, $pattern) === 0;
        }

        // Caret match (e.g., ^8.0 means >=8.0 <9.0, ^10.0 means >=10.0 <11.0)
        if (strpos($constraint, '^') === 0) {
            $minVersion = ltrim($constraint, '^');
            $parts = explode('.', $minVersion);
            $majorVersion = (int) $parts[0];

            return version_compare($currentVersion, $minVersion, '>=') &&
                   version_compare($currentVersion, ($majorVersion + 1) . '.0', '<');
        }

        // Tilde match (e.g., ~8.0 means >=8.0 <8.1, ~10.2 means >=10.2 <10.3)
        if (strpos($constraint, '~') === 0) {
            $minVersion = ltrim($constraint, '~');
            $parts = explode('.', $minVersion);

            if (count($parts) >= 2) {
                $majorVersion = (int) $parts[0];
                $minorVersion = (int) $parts[1];

                return version_compare($currentVersion, $minVersion, '>=') &&
                       version_compare($currentVersion, "{$majorVersion}." . ($minorVersion + 1), '<');
            }
        }

        // OR constraints (e.g., ^8.0|^9.0)
        if (strpos($constraint, '|') !== false) {
            $constraints = explode('|', $constraint);
            foreach ($constraints as $singleConstraint) {
                if ($this->simpleVersionMatch($currentVersion, trim($singleConstraint))) {
                    return true;
                }
            }
            return false;
        }

        // >= constraint
        if (strpos($constraint, '>=') === 0) {
            $minVersion = trim(substr($constraint, 2));
            return version_compare($currentVersion, $minVersion, '>=');
        }

        // <= constraint
        if (strpos($constraint, '<=') === 0) {
            $maxVersion = trim(substr($constraint, 2));
            return version_compare($currentVersion, $maxVersion, '<=');
        }

        // > constraint
        if (strpos($constraint, '>') === 0 && strpos($constraint, '>=') !== 0) {
            $minVersion = trim(substr($constraint, 1));
            return version_compare($currentVersion, $minVersion, '>');
        }

        // < constraint
        if (strpos($constraint, '<') === 0 && strpos($constraint, '<=') !== 0) {
            $maxVersion = trim(substr($constraint, 1));
            return version_compare($currentVersion, $maxVersion, '<');
        }

        return false;
    }

    /**
     * Parse composer.json file from an extension package.
     *
     * @param  string  $packagePath
     * @return array
     */
    public function parseComposerJson(string $packagePath): array
    {
        $composerPath = $packagePath . '/composer.json';

        if (!File::exists($composerPath)) {
            return [
                'success' => false,
                'error' => 'composer.json file not found',
            ];
        }

        try {
            $content = File::get($composerPath);
            $composer = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return [
                    'success' => false,
                    'error' => 'Invalid JSON in composer.json: ' . json_last_error_msg(),
                ];
            }

            return [
                'success' => true,
                'data' => [
                    'name' => $composer['name'] ?? null,
                    'description' => $composer['description'] ?? null,
                    'version' => $composer['version'] ?? null,
                    'type' => $composer['type'] ?? null,
                    'license' => $composer['license'] ?? null,
                    'authors' => $composer['authors'] ?? [],
                    'require' => $composer['require'] ?? [],
                    'require-dev' => $composer['require-dev'] ?? [],
                    'autoload' => $composer['autoload'] ?? [],
                    'extra' => $composer['extra'] ?? [],
                ],
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Error reading composer.json: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Validate extension package requirements.
     *
     * @param  string  $packagePath
     * @return array
     */
    public function validatePackageRequirements(string $packagePath): array
    {
        $composerData = $this->parseComposerJson($packagePath);

        if (!$composerData['success']) {
            return [
                'valid' => false,
                'errors' => [$composerData['error']],
            ];
        }

        $requirements = $composerData['data']['require'] ?? [];

        // Extract version requirements
        $laravelVersion = $requirements['laravel/framework'] ?? null;
        $phpVersion = $requirements['php'] ?? null;
        $crmVersion = $requirements['webkul/crm'] ?? $requirements['krayin/laravel-crm'] ?? null;

        // Remove Laravel, PHP, and CRM from dependencies to check other packages
        unset($requirements['php'], $requirements['laravel/framework'], $requirements['webkul/crm'], $requirements['krayin/laravel-crm']);

        return $this->checkCompatibility(
            $laravelVersion,
            $crmVersion,
            $phpVersion,
            $requirements
        );
    }

    /**
     * Get installed packages from composer.lock.
     *
     * @return array
     */
    protected function getInstalledPackages(): array
    {
        $lockPath = base_path('composer.lock');

        if (!File::exists($lockPath)) {
            return [];
        }

        try {
            $lock = json_decode(File::get($lockPath), true);
            $packages = [];

            if (isset($lock['packages']) && is_array($lock['packages'])) {
                foreach ($lock['packages'] as $package) {
                    $packages[$package['name']] = ltrim($package['version'], 'v');
                }
            }

            if (isset($lock['packages-dev']) && is_array($lock['packages-dev'])) {
                foreach ($lock['packages-dev'] as $package) {
                    $packages[$package['name']] = ltrim($package['version'], 'v');
                }
            }

            return $packages;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get the current CRM version.
     *
     * @return string
     */
    protected function getCrmVersion(): string
    {
        // Try to get from config
        if ($version = config('app.crm_version')) {
            return $version;
        }

        // Try to get from a version file
        $versionFile = base_path('VERSION');
        if (File::exists($versionFile)) {
            return trim(File::get($versionFile));
        }

        // Fallback to constant
        return self::CRM_VERSION;
    }

    /**
     * Get system information for compatibility display.
     *
     * @return array
     */
    public function getSystemInfo(): array
    {
        return [
            'php' => [
                'version' => PHP_VERSION,
                'major' => PHP_MAJOR_VERSION,
                'minor' => PHP_MINOR_VERSION,
                'release' => PHP_RELEASE_VERSION,
            ],
            'laravel' => [
                'version' => app()->version(),
            ],
            'crm' => [
                'version' => $this->getCrmVersion(),
            ],
            'extensions' => $this->getInstalledExtensions(),
        ];
    }

    /**
     * Get installed extensions.
     *
     * @return array
     */
    protected function getInstalledExtensions(): array
    {
        try {
            $installations = app('Webkul\Marketplace\Repositories\ExtensionInstallationRepository')
                ->with(['extension', 'version'])
                ->where('status', 'active')
                ->get();

            return $installations->map(function ($installation) {
                return [
                    'name' => $installation->extension->name ?? 'Unknown',
                    'slug' => $installation->extension->slug ?? 'unknown',
                    'version' => $installation->version->version ?? 'unknown',
                ];
            })->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }
}
