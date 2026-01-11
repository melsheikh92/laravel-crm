<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Compliance\ConsentManager;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class ConsentController extends Controller
{
    /**
     * @var ConsentManager
     */
    protected $consentManager;

    /**
     * Create a new controller instance.
     */
    public function __construct(ConsentManager $consentManager)
    {
        $this->consentManager = $consentManager;
    }

    /**
     * Get all consent records for the authenticated user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $consentType = $request->query('type');
        $activeOnly = $request->query('active_only', false);

        $consents = $this->consentManager->getConsentHistory(
            $user,
            $consentType,
            filter_var($activeOnly, FILTER_VALIDATE_BOOLEAN)
        );

        return response()->json([
            'success' => true,
            'data' => $consents,
        ]);
    }

    /**
     * Record a new consent for the authenticated user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        try {
            $validated = $request->validate([
                'consent_type' => 'required|string|max:255',
                'purpose' => 'nullable|string|max:1000',
                'metadata' => 'nullable|array',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        try {
            $consent = $this->consentManager->recordConsent(
                $validated['consent_type'],
                $user,
                $validated['purpose'] ?? null,
                $validated['metadata'] ?? []
            );

            if (!$consent) {
                return response()->json([
                    'success' => false,
                    'message' => 'Consent management is disabled',
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Consent recorded successfully',
                'data' => $consent,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to record consent: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Record multiple consents for the authenticated user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function storeMultiple(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        try {
            $validated = $request->validate([
                'consent_types' => 'required|array|min:1',
                'consent_types.*' => 'required|string|max:255',
                'metadata' => 'nullable|array',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        try {
            $consents = $this->consentManager->recordMultipleConsents(
                $validated['consent_types'],
                $user,
                $validated['metadata'] ?? []
            );

            return response()->json([
                'success' => true,
                'message' => 'Consents recorded successfully',
                'data' => $consents,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to record consents: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Withdraw a consent for the authenticated user.
     *
     * @param Request $request
     * @param string $consentType
     * @return JsonResponse
     */
    public function destroy(Request $request, string $consentType): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $metadata = $request->input('metadata', []);

        try {
            $withdrawn = $this->consentManager->withdrawConsent(
                $consentType,
                $user,
                $metadata
            );

            if (!$withdrawn) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active consent found for this type',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Consent withdrawn successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to withdraw consent: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Withdraw all consents for the authenticated user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function destroyAll(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $metadata = $request->input('metadata', []);

        try {
            $count = $this->consentManager->withdrawAllConsents($user, $metadata);

            return response()->json([
                'success' => true,
                'message' => "Successfully withdrew {$count} consent(s)",
                'count' => $count,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to withdraw consents: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all active consents for the authenticated user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function active(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $consents = $this->consentManager->getActiveConsents($user);

        return response()->json([
            'success' => true,
            'data' => $consents,
        ]);
    }

    /**
     * Check if the authenticated user has given consent for a specific type.
     *
     * @param Request $request
     * @param string $consentType
     * @return JsonResponse
     */
    public function check(Request $request, string $consentType): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $hasConsent = $this->consentManager->checkConsent($consentType, $user);

        return response()->json([
            'success' => true,
            'has_consent' => $hasConsent,
            'consent_type' => $consentType,
        ]);
    }

    /**
     * Check if the authenticated user has all required consents.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function checkRequired(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $hasRequired = $this->consentManager->hasRequiredConsents($user);
        $missing = $this->consentManager->getMissingRequiredConsents($user);

        return response()->json([
            'success' => true,
            'has_required_consents' => $hasRequired,
            'missing_consents' => $missing,
        ]);
    }

    /**
     * Get available consent types and their configuration.
     *
     * @return JsonResponse
     */
    public function types(): JsonResponse
    {
        $types = config('compliance.consent.types', []);

        // Format the types for API response
        $formatted = [];
        foreach ($types as $key => $config) {
            $formatted[] = [
                'type' => $key,
                'required' => $config['required'] ?? false,
                'description' => $config['description'] ?? '',
                'purpose' => $config['purpose'] ?? '',
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $formatted,
        ]);
    }
}
