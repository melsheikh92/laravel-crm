<?php

namespace Webkul\Marketplace\Http\Controllers\Marketplace;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Marketplace\Repositories\ExtensionRepository;
use Webkul\Marketplace\Repositories\ExtensionVersionRepository;
use Webkul\Marketplace\Repositories\ExtensionInstallationRepository;
use Webkul\Marketplace\Repositories\ExtensionTransactionRepository;
use Webkul\Marketplace\Services\CompatibilityChecker;
use Webkul\Marketplace\Services\ExtensionInstaller;
use Webkul\Marketplace\Services\ExtensionUninstaller;

class InstallationController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected ExtensionRepository $extensionRepository,
        protected ExtensionVersionRepository $versionRepository,
        protected ExtensionInstallationRepository $installationRepository,
        protected ExtensionTransactionRepository $transactionRepository,
        protected CompatibilityChecker $compatibilityChecker,
        protected ExtensionInstaller $installer,
        protected ExtensionUninstaller $uninstaller
    ) {}

    /**
     * Install an extension.
     */
    public function install(int $id): JsonResponse|RedirectResponse
    {
        try {
            $extension = $this->extensionRepository->findOrFail($id);

            // Check if extension is approved
            if (!$extension->isApproved()) {
                return $this->errorResponse(
                    trans('marketplace::app.marketplace.install.not-approved'),
                    403
                );
            }

            // Get latest version
            $latestVersion = $this->versionRepository->getLatestVersion($extension->id);

            if (!$latestVersion) {
                return $this->errorResponse(
                    trans('marketplace::app.marketplace.install.no-version-available'),
                    404
                );
            }

            // Check if already installed
            $existingInstallation = $this->installationRepository->findOneWhere([
                'extension_id' => $extension->id,
                'user_id'      => Auth::id(),
            ]);

            if ($existingInstallation && $existingInstallation->status === 'active') {
                return $this->errorResponse(
                    trans('marketplace::app.marketplace.install.already-installed'),
                    400
                );
            }

            // Check compatibility
            $compatibilityResult = $this->compatibilityChecker->checkVersionCompatibility($latestVersion->id);

            if (!$compatibilityResult['compatible']) {
                return $this->errorResponse(
                    trans('marketplace::app.marketplace.install.incompatible'),
                    400,
                    ['compatibility' => $compatibilityResult]
                );
            }

            // Handle payment for paid extensions
            if (!$extension->isFree()) {
                $paymentResult = $this->handlePayment($extension, Auth::id());

                if (!$paymentResult['success']) {
                    return $this->errorResponse(
                        $paymentResult['error'] ?? trans('marketplace::app.marketplace.install.payment-failed'),
                        400,
                        ['payment' => $paymentResult]
                    );
                }
            }

            // Trigger installation process
            $installationResult = $this->installer->install($latestVersion->id, Auth::id());

            if (!$installationResult['success']) {
                $statusCode = isset($installationResult['retry_possible']) && $installationResult['retry_possible'] ? 503 : 500;

                return $this->errorResponse(
                    $installationResult['error'] ?? trans('marketplace::app.marketplace.install.failed'),
                    $statusCode,
                    [
                        'installation' => $installationResult,
                        'retry_possible' => $installationResult['retry_possible'] ?? false,
                        'error_code' => $installationResult['error_code'] ?? null,
                    ]
                );
            }

            if (request()->ajax()) {
                return new JsonResponse([
                    'success' => true,
                    'message' => trans('marketplace::app.marketplace.install.success'),
                    'data'    => [
                        'installation' => $installationResult['installation'],
                        'extension'    => $extension,
                    ],
                ]);
            }

            return redirect()->route('marketplace.my_extensions.index')
                ->with('success', trans('marketplace::app.marketplace.install.success'));
        } catch (Exception $e) {
            Log::error('Extension installation failed', [
                'extension_id' => $id,
                'user_id'      => Auth::id(),
                'error'        => $e->getMessage(),
                'trace'        => $e->getTraceAsString(),
            ]);

            return $this->errorResponse(
                trans('marketplace::app.marketplace.install.failed') . ': ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Check compatibility before installation.
     */
    public function checkCompatibility(int $id): JsonResponse
    {
        try {
            $extension = $this->extensionRepository->findOrFail($id);

            // Get latest version or requested version
            $versionId = request()->get('version_id');
            if ($versionId) {
                $version = $this->versionRepository->findOrFail($versionId);
            } else {
                $version = $this->versionRepository->getLatestVersion($extension->id);
            }

            if (!$version) {
                return new JsonResponse([
                    'success' => false,
                    'message' => trans('marketplace::app.marketplace.install.no-version-available'),
                ], 404);
            }

            // Check compatibility
            $compatibilityResult = $this->compatibilityChecker->checkVersionCompatibility($version->id);

            // Get system info
            $systemInfo = $this->compatibilityChecker->getSystemInfo();

            return new JsonResponse([
                'success' => true,
                'data'    => [
                    'compatible'   => $compatibilityResult['compatible'],
                    'checks'       => $compatibilityResult['checks'],
                    'errors'       => $compatibilityResult['errors'],
                    'warnings'     => $compatibilityResult['warnings'],
                    'system_info'  => $systemInfo,
                    'version'      => $version,
                ],
            ]);
        } catch (Exception $e) {
            Log::error('Compatibility check failed', [
                'extension_id' => $id,
                'error'        => $e->getMessage(),
            ]);

            return new JsonResponse([
                'success' => false,
                'message' => trans('marketplace::app.marketplace.install.compatibility-check-failed'),
            ], 500);
        }
    }

    /**
     * Get installation status.
     */
    public function installationStatus(int $id): JsonResponse
    {
        try {
            $extension = $this->extensionRepository->findOrFail($id);

            $installation = $this->installationRepository->findOneWhere([
                'extension_id' => $extension->id,
                'user_id'      => Auth::id(),
            ]);

            if (!$installation) {
                return new JsonResponse([
                    'success' => true,
                    'data'    => [
                        'installed'        => false,
                        'status'           => null,
                        'can_update'       => false,
                        'current_version'  => null,
                        'latest_version'   => null,
                    ],
                ]);
            }

            // Check if update is available
            $latestVersion = $this->versionRepository->getLatestVersion($extension->id);
            $canUpdate = false;

            if ($latestVersion && $installation->version) {
                $canUpdate = version_compare(
                    $latestVersion->version,
                    $installation->version->version,
                    '>'
                );
            }

            return new JsonResponse([
                'success' => true,
                'data'    => [
                    'installed'        => true,
                    'status'           => $installation->status,
                    'can_update'       => $canUpdate,
                    'current_version'  => $installation->version,
                    'latest_version'   => $latestVersion,
                    'installation'     => $installation,
                    'auto_update'      => $installation->auto_update_enabled,
                ],
            ]);
        } catch (Exception $e) {
            Log::error('Failed to get installation status', [
                'extension_id' => $id,
                'user_id'      => Auth::id(),
                'error'        => $e->getMessage(),
            ]);

            return new JsonResponse([
                'success' => false,
                'message' => trans('marketplace::app.marketplace.install.status-check-failed'),
            ], 500);
        }
    }

    /**
     * Update an installed extension.
     */
    public function updateExtension(int $installation_id): JsonResponse|RedirectResponse
    {
        try {
            $installation = $this->installationRepository->with(['extension', 'version', 'user'])->findOrFail($installation_id);

            // Verify ownership
            if ($installation->user_id !== Auth::id()) {
                return $this->errorResponse(
                    trans('marketplace::app.marketplace.install.unauthorized'),
                    403
                );
            }

            // Get latest version
            $latestVersion = $this->versionRepository->getLatestVersion($installation->extension_id);

            if (!$latestVersion) {
                return $this->errorResponse(
                    trans('marketplace::app.marketplace.install.no-version-available'),
                    404
                );
            }

            // Check if update is needed
            if (!version_compare($latestVersion->version, $installation->version->version, '>')) {
                return $this->errorResponse(
                    trans('marketplace::app.marketplace.install.already-latest'),
                    400
                );
            }

            // Check compatibility
            $compatibilityResult = $this->compatibilityChecker->checkVersionCompatibility($latestVersion->id);

            if (!$compatibilityResult['compatible']) {
                return $this->errorResponse(
                    trans('marketplace::app.marketplace.install.update-incompatible'),
                    400,
                    ['compatibility' => $compatibilityResult]
                );
            }

            // Perform update
            $updateResult = $this->installer->update($installation_id, $latestVersion->id);

            if (!$updateResult['success']) {
                $statusCode = isset($updateResult['retry_possible']) && $updateResult['retry_possible'] ? 503 : 500;

                return $this->errorResponse(
                    $updateResult['error'] ?? trans('marketplace::app.marketplace.install.update-failed'),
                    $statusCode,
                    [
                        'retry_possible' => $updateResult['retry_possible'] ?? false,
                        'error_code' => $updateResult['error_code'] ?? null,
                    ]
                );
            }

            if (request()->ajax()) {
                return new JsonResponse([
                    'success' => true,
                    'message' => trans('marketplace::app.marketplace.install.update-success'),
                    'data'    => [
                        'installation' => $updateResult['installation'],
                    ],
                ]);
            }

            return redirect()->back()
                ->with('success', trans('marketplace::app.marketplace.install.update-success'));
        } catch (Exception $e) {
            Log::error('Extension update failed', [
                'installation_id' => $installation_id,
                'user_id'         => Auth::id(),
                'error'           => $e->getMessage(),
            ]);

            return $this->errorResponse(
                trans('marketplace::app.marketplace.install.update-failed') . ': ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Uninstall an extension.
     */
    public function uninstall(int $installation_id): JsonResponse|RedirectResponse
    {
        try {
            $installation = $this->installationRepository->with(['extension', 'user'])->findOrFail($installation_id);

            // Verify ownership
            if ($installation->user_id !== Auth::id()) {
                return $this->errorResponse(
                    trans('marketplace::app.marketplace.install.unauthorized'),
                    403
                );
            }

            // Perform uninstallation
            $uninstallResult = $this->uninstaller->uninstall($installation_id);

            if (!$uninstallResult['success']) {
                return $this->errorResponse(
                    $uninstallResult['error'] ?? trans('marketplace::app.marketplace.install.uninstall-failed'),
                    500
                );
            }

            if (request()->ajax()) {
                return new JsonResponse([
                    'success' => true,
                    'message' => trans('marketplace::app.marketplace.install.uninstall-success'),
                ]);
            }

            return redirect()->route('marketplace.my_extensions.index')
                ->with('success', trans('marketplace::app.marketplace.install.uninstall-success'));
        } catch (Exception $e) {
            Log::error('Extension uninstall failed', [
                'installation_id' => $installation_id,
                'user_id'         => Auth::id(),
                'error'           => $e->getMessage(),
            ]);

            return $this->errorResponse(
                trans('marketplace::app.marketplace.install.uninstall-failed') . ': ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Enable an installed extension.
     */
    public function enable(int $installation_id): JsonResponse|RedirectResponse
    {
        try {
            $installation = $this->installationRepository->findOrFail($installation_id);

            // Verify ownership
            if ($installation->user_id !== Auth::id()) {
                return $this->errorResponse(
                    trans('marketplace::app.marketplace.install.unauthorized'),
                    403
                );
            }

            // Activate installation
            $installation->activate();

            if (request()->ajax()) {
                return new JsonResponse([
                    'success' => true,
                    'message' => trans('marketplace::app.marketplace.install.enabled'),
                    'data'    => [
                        'installation' => $installation->fresh(),
                    ],
                ]);
            }

            return redirect()->back()
                ->with('success', trans('marketplace::app.marketplace.install.enabled'));
        } catch (Exception $e) {
            Log::error('Extension enable failed', [
                'installation_id' => $installation_id,
                'error'           => $e->getMessage(),
            ]);

            return $this->errorResponse(
                trans('marketplace::app.marketplace.install.enable-failed'),
                500
            );
        }
    }

    /**
     * Disable an installed extension.
     */
    public function disable(int $installation_id): JsonResponse|RedirectResponse
    {
        try {
            $installation = $this->installationRepository->findOrFail($installation_id);

            // Verify ownership
            if ($installation->user_id !== Auth::id()) {
                return $this->errorResponse(
                    trans('marketplace::app.marketplace.install.unauthorized'),
                    403
                );
            }

            // Deactivate installation
            $installation->deactivate();

            if (request()->ajax()) {
                return new JsonResponse([
                    'success' => true,
                    'message' => trans('marketplace::app.marketplace.install.disabled'),
                    'data'    => [
                        'installation' => $installation->fresh(),
                    ],
                ]);
            }

            return redirect()->back()
                ->with('success', trans('marketplace::app.marketplace.install.disabled'));
        } catch (Exception $e) {
            Log::error('Extension disable failed', [
                'installation_id' => $installation_id,
                'error'           => $e->getMessage(),
            ]);

            return $this->errorResponse(
                trans('marketplace::app.marketplace.install.disable-failed'),
                500
            );
        }
    }

    /**
     * Toggle auto-update setting.
     */
    public function toggleAutoUpdate(int $installation_id): JsonResponse|RedirectResponse
    {
        try {
            $installation = $this->installationRepository->findOrFail($installation_id);

            // Verify ownership
            if ($installation->user_id !== Auth::id()) {
                return $this->errorResponse(
                    trans('marketplace::app.marketplace.install.unauthorized'),
                    403
                );
            }

            // Toggle auto-update
            $installation->auto_update_enabled = !$installation->auto_update_enabled;
            $installation->save();

            $message = $installation->auto_update_enabled
                ? trans('marketplace::app.marketplace.install.auto-update-enabled')
                : trans('marketplace::app.marketplace.install.auto-update-disabled');

            if (request()->ajax()) {
                return new JsonResponse([
                    'success' => true,
                    'message' => $message,
                    'data'    => [
                        'installation'         => $installation->fresh(),
                        'auto_update_enabled'  => $installation->auto_update_enabled,
                    ],
                ]);
            }

            return redirect()->back()->with('success', $message);
        } catch (Exception $e) {
            Log::error('Toggle auto-update failed', [
                'installation_id' => $installation_id,
                'error'           => $e->getMessage(),
            ]);

            return $this->errorResponse(
                trans('marketplace::app.marketplace.install.auto-update-toggle-failed'),
                500
            );
        }
    }

    /**
     * Handle payment for paid extension.
     *
     * @param  \Webkul\Marketplace\Models\Extension  $extension
     * @param  int  $userId
     * @return array
     */
    protected function handlePayment($extension, int $userId): array
    {
        try {
            // Check if user has already purchased this extension
            $existingTransaction = $this->transactionRepository->findOneWhere([
                'extension_id' => $extension->id,
                'buyer_id'     => $userId,
                'status'       => 'completed',
            ]);

            if ($existingTransaction) {
                // User has already purchased this extension
                return [
                    'success'     => true,
                    'message'     => 'Extension already purchased',
                    'transaction' => $existingTransaction,
                ];
            }

            // Get payment details from request
            $paymentMethod = request()->get('payment_method', 'stripe');
            $paymentToken = request()->get('payment_token');

            if (!$paymentToken) {
                return [
                    'success' => false,
                    'error'   => trans('marketplace::app.marketplace.install.payment-token-required'),
                ];
            }

            // Calculate platform fee (e.g., 30% of price)
            $platformFeePercentage = config('marketplace.platform_fee_percentage', 30);
            $amount = $extension->price;
            $platformFee = ($amount * $platformFeePercentage) / 100;
            $sellerRevenue = $amount - $platformFee;

            // Create transaction record
            $transaction = $this->transactionRepository->create([
                'transaction_id' => 'TXN_' . strtoupper(uniqid()),
                'extension_id'   => $extension->id,
                'buyer_id'       => $userId,
                'seller_id'      => $extension->author_id,
                'amount'         => $amount,
                'platform_fee'   => $platformFee,
                'seller_revenue' => $sellerRevenue,
                'payment_method' => $paymentMethod,
                'status'         => 'pending',
                'metadata'       => [
                    'payment_token' => $paymentToken,
                ],
            ]);

            // Process payment (this would integrate with actual payment gateway)
            // For now, we'll simulate a successful payment
            $paymentResult = $this->processPayment($transaction, $paymentToken, $paymentMethod);

            if ($paymentResult['success']) {
                $transaction->markAsCompleted();

                return [
                    'success'     => true,
                    'message'     => 'Payment processed successfully',
                    'transaction' => $transaction,
                ];
            }

            $transaction->markAsFailed($paymentResult['error'] ?? 'Payment failed');

            return [
                'success' => false,
                'error'   => $paymentResult['error'] ?? trans('marketplace::app.marketplace.install.payment-failed'),
            ];
        } catch (Exception $e) {
            Log::error('Payment processing failed', [
                'extension_id' => $extension->id,
                'user_id'      => $userId,
                'error'        => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error'   => 'Payment processing failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Process payment through payment gateway.
     *
     * @param  mixed  $transaction
     * @param  string  $paymentToken
     * @param  string  $paymentMethod
     * @return array
     */
    protected function processPayment($transaction, string $paymentToken, string $paymentMethod): array
    {
        // This is a placeholder for actual payment gateway integration
        // In production, this would integrate with Stripe, PayPal, etc.

        try {
            // Simulate payment processing
            // In real implementation, this would:
            // 1. Validate payment token
            // 2. Charge the customer
            // 3. Handle payment gateway response
            // 4. Return success/failure

            // For now, we'll just return success
            return [
                'success' => true,
                'message' => 'Payment processed successfully',
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error'   => $e->getMessage(),
            ];
        }
    }

    /**
     * Return error response based on request type.
     *
     * @param  string  $message
     * @param  int  $statusCode
     * @param  array  $data
     * @return JsonResponse|RedirectResponse
     */
    protected function errorResponse(string $message, int $statusCode = 400, array $data = []): JsonResponse|RedirectResponse
    {
        if (request()->ajax()) {
            return new JsonResponse(array_merge([
                'success' => false,
                'message' => $message,
            ], $data), $statusCode);
        }

        return redirect()->back()
            ->with('error', $message)
            ->withInput();
    }
}
