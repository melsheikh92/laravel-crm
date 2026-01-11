<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Compliance\ComplianceMetrics;
use App\Services\Compliance\AuditReportGenerator;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class ComplianceReportController extends Controller
{
    /**
     * @var ComplianceMetrics
     */
    protected $complianceMetrics;

    /**
     * @var AuditReportGenerator
     */
    protected $auditReportGenerator;

    /**
     * Create a new controller instance.
     */
    public function __construct(ComplianceMetrics $complianceMetrics, AuditReportGenerator $auditReportGenerator)
    {
        $this->complianceMetrics = $complianceMetrics;
        $this->auditReportGenerator = $auditReportGenerator;
    }

    /**
     * Get compliance metrics overview.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function overview(Request $request): JsonResponse
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
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        try {
            $options = [];
            if (isset($validated['start_date'])) {
                $options['start_date'] = $validated['start_date'];
            }
            if (isset($validated['end_date'])) {
                $options['end_date'] = $validated['end_date'];
            }

            $metrics = $this->complianceMetrics->getOverview($options);

            return response()->json([
                'success' => true,
                'data' => $metrics,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get compliance overview: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get specific compliance metrics by type.
     *
     * @param Request $request
     * @param string $type
     * @return JsonResponse
     */
    public function metrics(Request $request, string $type): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $allowedTypes = ['audit_logging', 'consent', 'retention', 'encryption', 'status'];

        if (!in_array($type, $allowedTypes)) {
            return response()->json([
                'success' => false,
                'message' => "Invalid metric type. Allowed types: " . implode(', ', $allowedTypes),
            ], 400);
        }

        try {
            $validated = $request->validate([
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        try {
            $startDate = isset($validated['start_date']) ? $validated['start_date'] : null;
            $endDate = isset($validated['end_date']) ? $validated['end_date'] : null;

            $metrics = match ($type) {
                'audit_logging' => $this->complianceMetrics->getAuditLogMetrics($startDate, $endDate),
                'consent' => $this->complianceMetrics->getConsentMetrics($startDate, $endDate),
                'retention' => $this->complianceMetrics->getRetentionMetrics(),
                'encryption' => $this->complianceMetrics->getEncryptionMetrics(),
                'status' => $this->complianceMetrics->getComplianceStatus(),
                default => throw new \Exception("Unsupported metric type: {$type}"),
            };

            return response()->json([
                'success' => true,
                'type' => $type,
                'data' => $metrics,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get compliance metrics: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get audit report summary.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function auditReportSummary(Request $request): JsonResponse
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
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'event' => 'nullable|string|max:255',
                'model_type' => 'nullable|string|max:255',
                'user_id' => 'nullable|integer',
                'ip_address' => 'nullable|string|max:45',
                'tags' => 'nullable|string|max:255',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        try {
            $filters = array_filter($validated);
            $summary = $this->auditReportGenerator->getSummary($filters);

            return response()->json([
                'success' => true,
                'data' => $summary,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get audit report summary: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate an audit report in the specified format.
     *
     * @param Request $request
     * @return JsonResponse|\Illuminate\Http\Response
     */
    public function generateAuditReport(Request $request)
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
                'format' => 'required|string|in:csv,json,pdf',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'event' => 'nullable|string|max:255',
                'model_type' => 'nullable|string|max:255',
                'user_id' => 'nullable|integer',
                'ip_address' => 'nullable|string|max:45',
                'tags' => 'nullable|string|max:255',
                'limit' => 'nullable|integer|min:1|max:10000',
                'order_by' => 'nullable|string|in:created_at,event,auditable_type,user_id',
                'order_direction' => 'nullable|string|in:asc,desc',
                'title' => 'nullable|string|max:255',
                'include_statistics' => 'nullable|boolean',
                'pretty_print' => 'nullable|boolean',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        try {
            $format = $validated['format'];
            unset($validated['format']);

            // Separate filters from options
            $filterKeys = ['start_date', 'end_date', 'event', 'model_type', 'user_id', 'ip_address', 'tags', 'limit', 'order_by', 'order_direction'];
            $filters = array_filter(array_intersect_key($validated, array_flip($filterKeys)));

            $optionKeys = ['title', 'include_statistics', 'pretty_print'];
            $options = array_filter(array_intersect_key($validated, array_flip($optionKeys)));

            $report = $this->auditReportGenerator->generate($format, $filters, $options);

            // For CSV and PDF, return as download
            if ($format === 'csv') {
                $fileName = 'audit_report_' . now()->format('Y-m-d_His') . '.csv';
                return response($report, 200)
                    ->header('Content-Type', 'text/csv')
                    ->header('Content-Disposition', "attachment; filename=\"{$fileName}\"");
            }

            if ($format === 'pdf') {
                $fileName = 'audit_report_' . now()->format('Y-m-d_His') . '.pdf';
                return response($report, 200)
                    ->header('Content-Type', 'application/pdf')
                    ->header('Content-Disposition', "attachment; filename=\"{$fileName}\"");
            }

            // For JSON, return as JSON response
            return response()->json([
                'success' => true,
                'format' => 'json',
                'data' => $report,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate audit report: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get compliance status.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function status(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        try {
            $status = $this->complianceMetrics->getComplianceStatus();

            return response()->json([
                'success' => true,
                'data' => $status,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get compliance status: ' . $e->getMessage(),
            ], 500);
        }
    }
}
