<?php

namespace Webkul\Marketplace\Console\Commands;

use Illuminate\Console\Command;
use Webkul\Marketplace\Notifications\ExtensionUpdateAvailable;
use Webkul\Marketplace\Repositories\ExtensionInstallationRepository;
use Webkul\Marketplace\Repositories\ExtensionVersionRepository;

class CheckExtensionUpdates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'marketplace:check-updates
                            {--notify : Send notification to users about available updates}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for extension updates and notify users';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        protected ExtensionInstallationRepository $installationRepository,
        protected ExtensionVersionRepository $versionRepository
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Checking for extension updates...');

        // Get all active installations with their related data
        $installations = $this->installationRepository
            ->with(['extension', 'version', 'user'])
            ->findWhere(['status' => 'active']);

        if ($installations->isEmpty()) {
            $this->info('No active extension installations found.');

            return 0;
        }

        $this->info("Found {$installations->count()} active installation(s).");

        $updatesFound = 0;
        $notificationsSent = 0;

        foreach ($installations as $installation) {
            if (! $installation->extension || ! $installation->version) {
                continue;
            }

            // Check if there's a newer version available
            $latestVersion = $this->versionRepository->getLatestVersion($installation->extension_id);

            if (! $latestVersion || ! $latestVersion->isApproved()) {
                continue;
            }

            // Compare versions
            if (version_compare($latestVersion->version, $installation->version->version, '>')) {
                $updatesFound++;

                $this->line(sprintf(
                    '  ⬆️  %s: %s → %s (for user: %s)',
                    $installation->extension->name,
                    $installation->version->version,
                    $latestVersion->version,
                    $installation->user->name ?? 'Unknown'
                ));

                // Send notification if --notify option is enabled
                if ($this->option('notify') && $installation->user) {
                    try {
                        $installation->user->notify(
                            new ExtensionUpdateAvailable(
                                $installation->extension,
                                $latestVersion,
                                $installation->version
                            )
                        );
                        $notificationsSent++;
                    } catch (\Exception $e) {
                        $this->error("  ❌ Failed to send notification: {$e->getMessage()}");
                    }
                }
            }
        }

        $this->newLine();

        if ($updatesFound === 0) {
            $this->info('✅ All extensions are up to date!');
        } else {
            $this->info("✅ Found {$updatesFound} update(s) available.");

            if ($this->option('notify')) {
                $this->info("📧 Sent {$notificationsSent} notification(s) to users.");
            } else {
                $this->comment('💡 Run with --notify option to send update notifications to users.');
            }
        }

        return 0;
    }
}
