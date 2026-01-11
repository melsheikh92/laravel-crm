<?php

namespace App\Providers;

use App\Services\Compliance\AuditLogger;
use App\Services\Compliance\AuditReportGenerator;
use App\Services\Compliance\ComplianceMetrics;
use App\Services\Compliance\ConsentManager;
use App\Services\Compliance\DataRetentionService;
use App\Services\Compliance\FieldEncryption;
use App\Services\Compliance\RightToErasureService;
use Illuminate\Support\ServiceProvider;

class ComplianceServiceProvider extends ServiceProvider
{
    /**
     * Register any compliance services.
     *
     * @return void
     */
    public function register()
    {
        // Register AuditLogger service as singleton
        $this->app->singleton(AuditLogger::class, function ($app) {
            return new AuditLogger();
        });

        // Register ConsentManager service as singleton
        $this->app->singleton(ConsentManager::class, function ($app) {
            return new ConsentManager();
        });

        // Register DataRetentionService service as singleton
        $this->app->singleton(DataRetentionService::class, function ($app) {
            return new DataRetentionService(
                $app->make(AuditLogger::class)
            );
        });

        // Register RightToErasureService service as singleton
        $this->app->singleton(RightToErasureService::class, function ($app) {
            return new RightToErasureService(
                $app->make(AuditLogger::class)
            );
        });

        // Register FieldEncryption service as singleton
        $this->app->singleton(FieldEncryption::class, function ($app) {
            return new FieldEncryption();
        });

        // Register ComplianceMetrics service as singleton
        $this->app->singleton(ComplianceMetrics::class, function ($app) {
            return new ComplianceMetrics();
        });

        // Register AuditReportGenerator service as singleton
        $this->app->singleton(AuditReportGenerator::class, function ($app) {
            return new AuditReportGenerator(
                $app->make(AuditLogger::class)
            );
        });
    }

    /**
     * Bootstrap any compliance services.
     *
     * @return void
     */
    public function boot()
    {
        // Publish compliance configuration
        $this->publishes([
            __DIR__ . '/../../config/compliance.php' => config_path('compliance.php'),
        ], 'compliance-config');

        // Publish compliance migrations
        $this->publishes([
            __DIR__ . '/../../database/migrations/' => database_path('migrations'),
        ], 'compliance-migrations');
    }
}
