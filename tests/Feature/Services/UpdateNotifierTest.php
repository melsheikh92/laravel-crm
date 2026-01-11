<?php

use Webkul\Marketplace\Services\UpdateNotifier;
use Webkul\Marketplace\Repositories\ExtensionInstallationRepository;
use Webkul\Marketplace\Repositories\ExtensionVersionRepository;
use Webkul\Marketplace\Repositories\ExtensionRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->installationRepository = Mockery::mock(ExtensionInstallationRepository::class);
    $this->versionRepository = Mockery::mock(ExtensionVersionRepository::class);
    $this->extensionRepository = Mockery::mock(ExtensionRepository::class);

    $this->service = new UpdateNotifier(
        $this->installationRepository,
        $this->versionRepository,
        $this->extensionRepository
    );
});

afterEach(function () {
    Mockery::close();
});

describe('checkForUpdate', function () {
    it('detects available update', function () {
        $mockExtension = Mockery::mock();
        $mockExtension->id = 1;

        $mockCurrentVersion = Mockery::mock();
        $mockCurrentVersion->version = '1.0.0';

        $mockLatestVersion = Mockery::mock();
        $mockLatestVersion->id = 2;
        $mockLatestVersion->version = '2.0.0';
        $mockLatestVersion->changelog = 'New features';
        $mockLatestVersion->formatted_file_size = '5 MB';
        $mockLatestVersion->downloads_count = 100;
        $mockLatestVersion->release_date = null;
        $mockLatestVersion->shouldReceive('isApproved')->andReturn(true);

        $mockInstallation = Mockery::mock();
        $mockInstallation->extension = $mockExtension;
        $mockInstallation->version = $mockCurrentVersion;
        $mockInstallation->extension_id = 1;

        $this->installationRepository
            ->shouldReceive('findOrFail')
            ->with(1)
            ->andReturn($mockInstallation);

        $this->versionRepository
            ->shouldReceive('getLatestVersion')
            ->with(1)
            ->andReturn($mockLatestVersion);

        $result = $this->service->checkForUpdate(1);

        expect($result)->toBeArray()
            ->and($result['success'])->toBeTrue()
            ->and($result['has_update'])->toBeTrue()
            ->and($result['current_version'])->toBe('1.0.0')
            ->and($result['latest_version'])->toBe('2.0.0')
            ->and($result['update_info'])->toBeArray();
    });

    it('returns no update when versions match', function () {
        $mockExtension = Mockery::mock();
        $mockExtension->id = 1;

        $mockCurrentVersion = Mockery::mock();
        $mockCurrentVersion->version = '1.0.0';

        $mockLatestVersion = Mockery::mock();
        $mockLatestVersion->version = '1.0.0';
        $mockLatestVersion->shouldReceive('isApproved')->andReturn(true);

        $mockInstallation = Mockery::mock();
        $mockInstallation->extension = $mockExtension;
        $mockInstallation->version = $mockCurrentVersion;
        $mockInstallation->extension_id = 1;

        $this->installationRepository
            ->shouldReceive('findOrFail')
            ->with(1)
            ->andReturn($mockInstallation);

        $this->versionRepository
            ->shouldReceive('getLatestVersion')
            ->with(1)
            ->andReturn($mockLatestVersion);

        $result = $this->service->checkForUpdate(1);

        expect($result)->toBeArray()
            ->and($result['success'])->toBeTrue()
            ->and($result['has_update'])->toBeFalse();
    });

    it('returns no update when latest version is not approved', function () {
        $mockExtension = Mockery::mock();
        $mockExtension->id = 1;

        $mockCurrentVersion = Mockery::mock();
        $mockCurrentVersion->version = '1.0.0';

        $mockLatestVersion = Mockery::mock();
        $mockLatestVersion->version = '2.0.0';
        $mockLatestVersion->shouldReceive('isApproved')->andReturn(false);

        $mockInstallation = Mockery::mock();
        $mockInstallation->extension = $mockExtension;
        $mockInstallation->version = $mockCurrentVersion;
        $mockInstallation->extension_id = 1;

        $this->installationRepository
            ->shouldReceive('findOrFail')
            ->with(1)
            ->andReturn($mockInstallation);

        $this->versionRepository
            ->shouldReceive('getLatestVersion')
            ->with(1)
            ->andReturn($mockLatestVersion);

        $result = $this->service->checkForUpdate(1);

        expect($result)->toBeArray()
            ->and($result['success'])->toBeTrue()
            ->and($result['has_update'])->toBeFalse()
            ->and($result['message'])->toBe('No approved versions available');
    });

    it('returns error when extension not found', function () {
        $mockInstallation = Mockery::mock();
        $mockInstallation->extension = null;

        $this->installationRepository
            ->shouldReceive('findOrFail')
            ->with(1)
            ->andReturn($mockInstallation);

        $result = $this->service->checkForUpdate(1);

        expect($result)->toBeArray()
            ->and($result['success'])->toBeFalse()
            ->and($result['error'])->toBe('Extension not found');
    });

    it('handles no available versions', function () {
        $mockExtension = Mockery::mock();
        $mockExtension->id = 1;

        $mockCurrentVersion = Mockery::mock();
        $mockCurrentVersion->version = '1.0.0';

        $mockInstallation = Mockery::mock();
        $mockInstallation->extension = $mockExtension;
        $mockInstallation->version = $mockCurrentVersion;
        $mockInstallation->extension_id = 1;

        $this->installationRepository
            ->shouldReceive('findOrFail')
            ->with(1)
            ->andReturn($mockInstallation);

        $this->versionRepository
            ->shouldReceive('getLatestVersion')
            ->with(1)
            ->andReturn(null);

        $result = $this->service->checkForUpdate(1);

        expect($result)->toBeArray()
            ->and($result['success'])->toBeTrue()
            ->and($result['has_update'])->toBeFalse()
            ->and($result['message'])->toBe('No versions available');
    });

    it('handles exceptions gracefully', function () {
        $this->installationRepository
            ->shouldReceive('findOrFail')
            ->with(1)
            ->andThrow(new \Exception('Database error'));

        Log::shouldReceive('error')->once();

        $result = $this->service->checkForUpdate(1);

        expect($result)->toBeArray()
            ->and($result['success'])->toBeFalse()
            ->and($result['error'])->toContain('Failed to check for update');
    });
});

describe('checkUserUpdates', function () {
    it('checks updates for all user installations', function () {
        $mockExtension = Mockery::mock();
        $mockExtension->name = 'Test Extension';
        $mockExtension->slug = 'test-extension';

        $mockVersion = Mockery::mock();
        $mockVersion->version = '1.0.0';

        $mockInstallation = Mockery::mock();
        $mockInstallation->id = 1;
        $mockInstallation->extension_id = 1;
        $mockInstallation->extension = $mockExtension;
        $mockInstallation->version = $mockVersion;

        $mockQuery = Mockery::mock();
        $mockQuery->shouldReceive('active')->andReturnSelf();
        $mockQuery->shouldReceive('with')->andReturnSelf();

        $this->installationRepository
            ->shouldReceive('resetScope')
            ->andReturnSelf();

        $this->installationRepository
            ->shouldReceive('scopeQuery')
            ->andReturnSelf();

        $this->installationRepository
            ->shouldReceive('all')
            ->andReturn(collect([$mockInstallation]));

        $this->versionRepository
            ->shouldReceive('getLatestVersion')
            ->andReturn(null);

        $result = $this->service->checkUserUpdates(1);

        expect($result)->toBeArray()
            ->and($result['success'])->toBeTrue()
            ->and($result['user_id'])->toBe(1)
            ->and($result)->toHaveKeys(['total_installations', 'total_updates', 'updates']);
    });

    it('filters active installations only', function () {
        $this->installationRepository
            ->shouldReceive('resetScope')
            ->andReturnSelf();

        $this->installationRepository
            ->shouldReceive('scopeQuery')
            ->andReturnSelf();

        $this->installationRepository
            ->shouldReceive('all')
            ->andReturn(collect());

        $result = $this->service->checkUserUpdates(1, true);

        expect($result['success'])->toBeTrue();
    });

    it('handles errors when checking user updates', function () {
        $this->installationRepository
            ->shouldReceive('resetScope')
            ->andThrow(new \Exception('Database error'));

        Log::shouldReceive('error')->once();

        $result = $this->service->checkUserUpdates(1);

        expect($result)->toBeArray()
            ->and($result['success'])->toBeFalse()
            ->and($result['error'])->toContain('Failed to check user updates');
    });
});

describe('checkAllUpdates', function () {
    it('checks updates for all installations', function () {
        $this->installationRepository
            ->shouldReceive('resetScope')
            ->andReturnSelf();

        $this->installationRepository
            ->shouldReceive('scopeQuery')
            ->andReturnSelf();

        $this->installationRepository
            ->shouldReceive('all')
            ->andReturn(collect());

        $result = $this->service->checkAllUpdates();

        expect($result)->toBeArray()
            ->and($result['success'])->toBeTrue()
            ->and($result)->toHaveKeys(['total_installations', 'total_updates', 'updates', 'by_user']);
    });

    it('groups updates by user', function () {
        $this->installationRepository
            ->shouldReceive('resetScope')
            ->andReturnSelf();

        $this->installationRepository
            ->shouldReceive('scopeQuery')
            ->andReturnSelf();

        $this->installationRepository
            ->shouldReceive('all')
            ->andReturn(collect());

        $result = $this->service->checkAllUpdates();

        expect($result['by_user'])->toBeArray();
    });
});

describe('getCachedUpdateCheck', function () {
    it('returns cached update check', function () {
        $cachedData = [
            'success' => true,
            'has_update' => false,
        ];

        Cache::shouldReceive('remember')
            ->once()
            ->andReturn($cachedData);

        $result = $this->service->getCachedUpdateCheck(1);

        expect($result)->toBeArray()
            ->and($result['success'])->toBeTrue();
    });

    it('forces cache refresh when requested', function () {
        Cache::shouldReceive('forget')
            ->once()
            ->with(UpdateNotifier::CACHE_PREFIX . 'installation:1');

        Cache::shouldReceive('remember')
            ->once()
            ->andReturn(['success' => true]);

        $result = $this->service->getCachedUpdateCheck(1, true);

        expect($result)->toBeArray();
    });
});

describe('clearCache', function () {
    it('clears cache for installation and user', function () {
        $mockInstallation = Mockery::mock();
        $mockInstallation->user_id = 5;

        $this->installationRepository
            ->shouldReceive('findOrFail')
            ->with(1)
            ->andReturn($mockInstallation);

        Cache::shouldReceive('forget')
            ->with(UpdateNotifier::CACHE_PREFIX . 'installation:1')
            ->once();

        Cache::shouldReceive('forget')
            ->with(UpdateNotifier::CACHE_PREFIX . 'user:5')
            ->once();

        $result = $this->service->clearCache(1);

        expect($result)->toBeTrue();
    });

    it('handles errors when clearing cache', function () {
        $this->installationRepository
            ->shouldReceive('findOrFail')
            ->with(1)
            ->andThrow(new \Exception('Not found'));

        Log::shouldReceive('error')->once();

        $result = $this->service->clearCache(1);

        expect($result)->toBeFalse();
    });
});

describe('getUpdateInfo', function () {
    it('returns detailed update information', function () {
        $mockExtension = Mockery::mock();
        $mockExtension->id = 1;
        $mockExtension->name = 'Test Extension';
        $mockExtension->slug = 'test-extension';
        $mockExtension->description = 'Test description';
        $mockExtension->logo = 'logo.png';

        $mockCurrentVersion = Mockery::mock();
        $mockCurrentVersion->version = '1.0.0';

        $mockLatestVersion = Mockery::mock();
        $mockLatestVersion->version = '2.0.0';
        $mockLatestVersion->shouldReceive('isApproved')->andReturn(true);

        $mockInstallation = Mockery::mock();
        $mockInstallation->extension = $mockExtension;
        $mockInstallation->version = $mockCurrentVersion;
        $mockInstallation->extension_id = 1;

        $this->installationRepository
            ->shouldReceive('findOrFail')
            ->with(1)
            ->andReturn($mockInstallation);

        $this->versionRepository
            ->shouldReceive('getLatestVersion')
            ->with(1)
            ->andReturn($mockLatestVersion);

        $this->versionRepository
            ->shouldReceive('getNewerVersions')
            ->with(1, '1.0.0')
            ->andReturn(collect());

        $result = $this->service->getUpdateInfo(1);

        expect($result)->toBeArray()
            ->and($result['success'])->toBeTrue()
            ->and($result['has_update'])->toBeTrue()
            ->and($result)->toHaveKeys(['extension', 'current_version', 'latest_version', 'newer_versions']);
    });

    it('returns no update message when up to date', function () {
        $mockExtension = Mockery::mock();
        $mockExtension->id = 1;

        $mockCurrentVersion = Mockery::mock();
        $mockCurrentVersion->version = '1.0.0';

        $mockLatestVersion = Mockery::mock();
        $mockLatestVersion->version = '1.0.0';
        $mockLatestVersion->shouldReceive('isApproved')->andReturn(true);

        $mockInstallation = Mockery::mock();
        $mockInstallation->extension = $mockExtension;
        $mockInstallation->version = $mockCurrentVersion;
        $mockInstallation->extension_id = 1;

        $this->installationRepository
            ->shouldReceive('findOrFail')
            ->with(1)
            ->andReturn($mockInstallation);

        $this->versionRepository
            ->shouldReceive('getLatestVersion')
            ->with(1)
            ->andReturn($mockLatestVersion);

        $result = $this->service->getUpdateInfo(1);

        expect($result)->toBeArray()
            ->and($result['success'])->toBeTrue()
            ->and($result['has_update'])->toBeFalse()
            ->and($result['message'])->toBe('No updates available');
    });
});

describe('getUpdateStatistics', function () {
    it('returns update statistics', function () {
        $mockInstallation = Mockery::mock();
        $mockInstallation->id = 1;
        $mockInstallation->auto_update_enabled = true;

        $this->installationRepository
            ->shouldReceive('resetScope')
            ->andReturnSelf();

        $this->installationRepository
            ->shouldReceive('scopeQuery')
            ->andReturnSelf();

        $this->installationRepository
            ->shouldReceive('all')
            ->andReturn(collect([$mockInstallation]));

        Cache::shouldReceive('remember')
            ->andReturn(['success' => true, 'has_update' => false]);

        $result = $this->service->getUpdateStatistics();

        expect($result)->toBeArray()
            ->and($result['success'])->toBeTrue()
            ->and($result['statistics'])->toHaveKeys([
                'total_installations',
                'installations_with_updates',
                'installations_up_to_date',
                'auto_update_enabled',
                'update_percentage'
            ]);
    });

    it('includes recently updated installations', function () {
        $this->installationRepository
            ->shouldReceive('resetScope')
            ->times(2)
            ->andReturnSelf();

        $this->installationRepository
            ->shouldReceive('scopeQuery')
            ->times(2)
            ->andReturnSelf();

        $this->installationRepository
            ->shouldReceive('all')
            ->times(2)
            ->andReturn(collect());

        $result = $this->service->getUpdateStatistics();

        expect($result)->toHaveKey('recently_updated')
            ->and($result['recently_updated'])->toBeArray();
    });
});

describe('formatChangelogToHtml', function () {
    it('returns default message for empty changelog', function () {
        $mockVersion = Mockery::mock();
        $mockVersion->version = '1.0.0';
        $mockVersion->changelog = null;
        $mockVersion->release_date = null;

        $this->versionRepository
            ->shouldReceive('findOrFail')
            ->with(1)
            ->andReturn($mockVersion);

        $result = $this->service->getFormattedChangelog(1);

        expect($result)->toBeArray()
            ->and($result['success'])->toBeTrue()
            ->and($result['changelog_html'])->toContain('No changelog available');
    });

    it('formats changelog with headers', function () {
        $mockVersion = Mockery::mock();
        $mockVersion->version = '1.0.0';
        $mockVersion->changelog = "# Main Header\n## Sub Header";
        $mockVersion->release_date = null;

        $this->versionRepository
            ->shouldReceive('findOrFail')
            ->with(1)
            ->andReturn($mockVersion);

        $result = $this->service->getFormattedChangelog(1);

        expect($result['changelog_html'])->toContain('<h2>')
            ->and($result['changelog_html'])->toContain('<h3>');
    });

    it('formats changelog with bullet points', function () {
        $mockVersion = Mockery::mock();
        $mockVersion->version = '1.0.0';
        $mockVersion->changelog = "- Item 1\n- Item 2";
        $mockVersion->release_date = null;

        $this->versionRepository
            ->shouldReceive('findOrFail')
            ->with(1)
            ->andReturn($mockVersion);

        $result = $this->service->getFormattedChangelog(1);

        expect($result['changelog_html'])->toContain('<li>')
            ->and($result['changelog_html'])->toContain('<ul>');
    });
});
