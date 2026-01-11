<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Services\Compliance\AuditReportGenerator;
use App\Services\Compliance\ComplianceMetrics;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class ComplianceController extends Controller
{
    /**
     * @var ComplianceMetrics
     */
    protected $complianceMetrics;

    /**
     * @var AuditReportGenerator
     */
    protected $reportGenerator;

    /**
     * Create a new controller instance.
     */
    public function __construct(ComplianceMetrics $complianceMetrics, AuditReportGenerator $reportGenerator)
    {
        $this->complianceMetrics = $complianceMetrics;
        $this->reportGenerator = $reportGenerator;

        // Add auth middleware to protect compliance endpoints
        $this->middleware('auth');
    }

    /**
     * Display the compliance dashboard.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function dashboard(Request $request)
    {
        // Get date range from request or use defaults (last 30 days)
        $startDate = $request->query('start_date', now()->subDays(30));
        $endDate = $request->query('end_date', now());

        // Get comprehensive compliance metrics
        $metrics = $this->complianceMetrics->getOverview([
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        return view('compliance.dashboard', [
            'metrics' => $metrics,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    /**
     * Display audit logs with filtering and pagination.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function auditLogs(Request $request)
    {
        // Build filters from request
        $filters = [];

        if ($request->has('start_date')) {
            $filters['start_date'] = $request->input('start_date');
        }

        if ($request->has('end_date')) {
            $filters['end_date'] = $request->input('end_date');
        }

        if ($request->has('event')) {
            $filters['event'] = $request->input('event');
        }

        if ($request->has('model_type')) {
            $filters['model_type'] = $request->input('model_type');
        }

        if ($request->has('user_id')) {
            $filters['user_id'] = $request->input('user_id');
        }

        if ($request->has('ip_address')) {
            $filters['ip_address'] = $request->input('ip_address');
        }

        if ($request->has('tags')) {
            $filters['tags'] = $request->input('tags');
        }

        // Get paginated audit logs
        $query = $this->reportGenerator->buildFilteredQuery($filters);
        $auditLogs = $query->paginate($request->input('per_page', 25));

        // Get summary statistics
        $summary = $this->reportGenerator->getSummary($filters);

        return view('compliance.audit-logs', [
            'auditLogs' => $auditLogs,
            'summary' => $summary,
            'filters' => $filters,
        ]);
    }

    /**
     * Export audit report in specified format.
     *
     * @param Request $request
     * @return Response
     */
    public function exportAuditReport(Request $request)
    {
        // Validate request
        $validated = $request->validate([
            'format' => 'required|in:csv,json,pdf',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'event' => 'nullable|string',
            'model_type' => 'nullable|string',
            'user_id' => 'nullable|integer',
            'ip_address' => 'nullable|ip',
            'tags' => 'nullable|string',
            'limit' => 'nullable|integer|min:1|max:10000',
        ]);

        // Build filters
        $filters = array_filter([
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'event' => $validated['event'] ?? null,
            'model_type' => $validated['model_type'] ?? null,
            'user_id' => $validated['user_id'] ?? null,
            'ip_address' => $validated['ip_address'] ?? null,
            'tags' => $validated['tags'] ?? null,
            'limit' => $validated['limit'] ?? 1000,
        ]);

        try {
            // Generate report
            $format = $validated['format'];
            $reportContent = $this->reportGenerator->generate($format, $filters);

            // Convert array to JSON string if needed
            if (is_array($reportContent)) {
                $reportContent = json_encode($reportContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            }

            // Determine content type and file extension
            $contentTypes = [
                'csv' => 'text/csv',
                'json' => 'application/json',
                'pdf' => 'application/pdf',
            ];

            $extensions = [
                'csv' => 'csv',
                'json' => 'json',
                'pdf' => 'pdf',
            ];

            $contentType = $contentTypes[$format] ?? 'application/octet-stream';
            $extension = $extensions[$format] ?? 'txt';
            $filename = 'audit-report-' . now()->format('Y-m-d-His') . '.' . $extension;

            // Return download response
            return response($reportContent, 200)
                ->header('Content-Type', $contentType)
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to export audit report: ' . $e->getMessage());
        }
    }

    /**
     * Get compliance metrics (for AJAX requests).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function metrics(Request $request)
    {
        // Validate request
        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'metric_type' => 'nullable|in:overview,audit_logging,consent,retention,encryption,status',
        ]);

        // Get date range
        $startDate = $validated['start_date'] ?? now()->subDays(30);
        $endDate = $validated['end_date'] ?? now();
        $metricType = $validated['metric_type'] ?? 'overview';

        try {
            // Get requested metrics
            $metrics = match ($metricType) {
                'audit_logging' => $this->complianceMetrics->getAuditLogMetrics($startDate, $endDate),
                'consent' => $this->complianceMetrics->getConsentMetrics($startDate, $endDate),
                'retention' => $this->complianceMetrics->getRetentionMetrics(),
                'encryption' => $this->complianceMetrics->getEncryptionMetrics(),
                'status' => $this->complianceMetrics->getComplianceStatus(),
                'overview' => $this->complianceMetrics->getOverview([
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ]),
                default => throw new \Exception("Invalid metric type: {$metricType}"),
            };

            return response()->json([
                'success' => true,
                'data' => $metrics,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve metrics: ' . $e->getMessage(),
            ], 500);
        }
    }
}
