<?php

namespace App\Services\Compliance;

use App\Models\ConsentRecord;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;

class ConsentManager
{
    /**
     * Record a new consent for a user.
     *
     * @param string $consentType The type of consent (e.g., 'marketing', 'terms_of_service')
     * @param int|User|null $user The user giving consent (defaults to current authenticated user)
     * @param string|null $purpose The purpose of the consent
     * @param array $metadata Additional metadata about the consent
     * @param string|null $ipAddress IP address (auto-captured if not provided)
     * @param string|null $userAgent User agent (auto-captured if not provided)
     * @return ConsentRecord|null The created consent record or null if consent management is disabled
     */
    public function recordConsent(
        string $consentType,
        int|User|null $user = null,
        ?string $purpose = null,
        array $metadata = [],
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): ?ConsentRecord {
        if (!$this->isConsentManagementEnabled()) {
            return null;
        }

        $userId = $this->resolveUserId($user);

        if (!$userId) {
            throw new \InvalidArgumentException('User ID is required to record consent');
        }

        // Get purpose from config if not provided
        if (!$purpose) {
            $purpose = $this->getConsentPurpose($consentType);
        }

        // Auto-capture IP and user agent if not provided
        $ipAddress = $ipAddress ?? Request::ip();
        $userAgent = $userAgent ?? Request::userAgent();

        return ConsentRecord::create([
            'user_id' => $userId,
            'consent_type' => $consentType,
            'purpose' => $purpose,
            'given_at' => now(),
            'withdrawn_at' => null,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Withdraw an existing consent for a user.
     *
     * @param string $consentType The type of consent to withdraw
     * @param int|User|null $user The user withdrawing consent (defaults to current authenticated user)
     * @param array $metadata Additional metadata about the withdrawal
     * @return bool True if consent was withdrawn, false if no active consent found
     */
    public function withdrawConsent(
        string $consentType,
        int|User|null $user = null,
        array $metadata = []
    ): bool {
        if (!$this->isConsentManagementEnabled()) {
            return false;
        }

        $userId = $this->resolveUserId($user);

        if (!$userId) {
            throw new \InvalidArgumentException('User ID is required to withdraw consent');
        }

        // Find the most recent active consent of this type for the user
        $consent = ConsentRecord::where('user_id', $userId)
            ->where('consent_type', $consentType)
            ->whereNull('withdrawn_at')
            ->latest('given_at')
            ->first();

        if (!$consent) {
            return false;
        }

        // Add withdrawal metadata if provided
        if (!empty($metadata)) {
            $existingMetadata = $consent->metadata ?? [];
            $consent->metadata = array_merge($existingMetadata, [
                'withdrawal_metadata' => $metadata,
                'withdrawn_ip' => Request::ip(),
                'withdrawn_user_agent' => Request::userAgent(),
            ]);
        }

        return $consent->withdraw();
    }

    /**
     * Check if a user has given active consent for a specific type.
     *
     * @param string $consentType The type of consent to check
     * @param int|User|null $user The user to check consent for (defaults to current authenticated user)
     * @return bool True if user has active consent, false otherwise
     */
    public function checkConsent(
        string $consentType,
        int|User|null $user = null
    ): bool {
        if (!$this->isConsentManagementEnabled()) {
            // If consent management is disabled, assume consent is given
            return true;
        }

        $userId = $this->resolveUserId($user);

        if (!$userId) {
            return false;
        }

        return ConsentRecord::where('user_id', $userId)
            ->where('consent_type', $consentType)
            ->whereNull('withdrawn_at')
            ->exists();
    }

    /**
     * Get consent history for a user.
     *
     * @param int|User|null $user The user to get consent history for (defaults to current authenticated user)
     * @param string|null $consentType Optional filter by consent type
     * @param bool $activeOnly Whether to return only active consents
     * @return Collection Collection of ConsentRecord models
     */
    public function getConsentHistory(
        int|User|null $user = null,
        ?string $consentType = null,
        bool $activeOnly = false
    ): Collection {
        if (!$this->isConsentManagementEnabled()) {
            return new Collection();
        }

        $userId = $this->resolveUserId($user);

        if (!$userId) {
            return new Collection();
        }

        $query = ConsentRecord::where('user_id', $userId);

        if ($consentType) {
            $query->where('consent_type', $consentType);
        }

        if ($activeOnly) {
            $query->whereNull('withdrawn_at');
        }

        return $query->orderBy('given_at', 'desc')->get();
    }

    /**
     * Get all active consents for a user.
     *
     * @param int|User|null $user The user to get active consents for (defaults to current authenticated user)
     * @return Collection Collection of active ConsentRecord models
     */
    public function getActiveConsents(int|User|null $user = null): Collection
    {
        return $this->getConsentHistory($user, null, true);
    }

    /**
     * Check if a user has all required consents.
     *
     * @param int|User|null $user The user to check (defaults to current authenticated user)
     * @return bool True if user has all required consents, false otherwise
     */
    public function hasRequiredConsents(int|User|null $user = null): bool
    {
        if (!$this->isConsentManagementEnabled()) {
            return true;
        }

        $userId = $this->resolveUserId($user);

        if (!$userId) {
            return false;
        }

        $requiredConsents = $this->getRequiredConsentTypes();

        foreach ($requiredConsents as $consentType) {
            if (!$this->checkConsent($consentType, $userId)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get missing required consents for a user.
     *
     * @param int|User|null $user The user to check (defaults to current authenticated user)
     * @return array Array of missing required consent types
     */
    public function getMissingRequiredConsents(int|User|null $user = null): array
    {
        if (!$this->isConsentManagementEnabled()) {
            return [];
        }

        $userId = $this->resolveUserId($user);

        if (!$userId) {
            return $this->getRequiredConsentTypes();
        }

        $requiredConsents = $this->getRequiredConsentTypes();
        $missingConsents = [];

        foreach ($requiredConsents as $consentType) {
            if (!$this->checkConsent($consentType, $userId)) {
                $missingConsents[] = $consentType;
            }
        }

        return $missingConsents;
    }

    /**
     * Bulk record multiple consents for a user.
     *
     * @param array $consents Array of consent types to record
     * @param int|User|null $user The user giving consent (defaults to current authenticated user)
     * @param array $metadata Additional metadata for all consents
     * @return Collection Collection of created ConsentRecord models
     */
    public function recordMultipleConsents(
        array $consents,
        int|User|null $user = null,
        array $metadata = []
    ): Collection {
        $records = new Collection();

        foreach ($consents as $consentType) {
            $record = $this->recordConsent($consentType, $user, null, $metadata);
            if ($record) {
                $records->push($record);
            }
        }

        return $records;
    }

    /**
     * Withdraw all consents for a user (useful for account deletion).
     *
     * @param int|User|null $user The user to withdraw all consents for
     * @param array $metadata Additional metadata about the bulk withdrawal
     * @return int Number of consents withdrawn
     */
    public function withdrawAllConsents(int|User|null $user = null, array $metadata = []): int
    {
        if (!$this->isConsentManagementEnabled()) {
            return 0;
        }

        $userId = $this->resolveUserId($user);

        if (!$userId) {
            return 0;
        }

        $activeConsents = ConsentRecord::where('user_id', $userId)
            ->whereNull('withdrawn_at')
            ->get();

        $count = 0;
        foreach ($activeConsents as $consent) {
            if (!empty($metadata)) {
                $existingMetadata = $consent->metadata ?? [];
                $consent->metadata = array_merge($existingMetadata, [
                    'withdrawal_metadata' => $metadata,
                    'withdrawn_ip' => Request::ip(),
                    'withdrawn_user_agent' => Request::userAgent(),
                ]);
            }

            if ($consent->withdraw()) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Check if consent management is enabled.
     *
     * @return bool
     */
    protected function isConsentManagementEnabled(): bool
    {
        return Config::get('compliance.enabled', true) &&
               Config::get('compliance.consent.enabled', true);
    }

    /**
     * Resolve the user ID from the given parameter.
     *
     * @param int|User|null $user
     * @return int|null
     */
    protected function resolveUserId(int|User|null $user = null): ?int
    {
        if ($user instanceof User) {
            return $user->id;
        }

        if (is_int($user)) {
            return $user;
        }

        return Auth::id();
    }

    /**
     * Get the purpose for a consent type from config.
     *
     * @param string $consentType
     * @return string
     */
    protected function getConsentPurpose(string $consentType): string
    {
        $types = Config::get('compliance.consent.types', []);
        $typeConfig = $types[$consentType] ?? null;

        if ($typeConfig && isset($typeConfig['purpose'])) {
            return $typeConfig['purpose'];
        }

        return "Consent for {$consentType}";
    }

    /**
     * Get all required consent types from config.
     *
     * @return array
     */
    protected function getRequiredConsentTypes(): array
    {
        $types = Config::get('compliance.consent.types', []);
        $required = [];

        foreach ($types as $type => $config) {
            if (isset($config['required']) && $config['required'] === true) {
                $required[] = $type;
            }
        }

        return $required;
    }
}
