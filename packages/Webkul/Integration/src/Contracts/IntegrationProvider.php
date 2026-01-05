<?php

namespace Webkul\Integration\Contracts;

interface IntegrationProvider
{
    public function install(array $config): bool;

    public function uninstall(): bool;

    public function configure(array $config): bool;

    public function sync(): array;

    public function testConnection(): bool;
}

