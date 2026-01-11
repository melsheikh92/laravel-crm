<?php

namespace App\Services\Compliance;

use App\Models\AuditLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class AuditReportGenerator
{
    /**
     * The AuditLogger service instance.
     *
     * @var AuditLogger
     */
    protected AuditLogger $auditLogger;

    /**
     * Create a new AuditReportGenerator instance.
     *
     * @param AuditLogger $auditLogger
     */
    public function __construct(AuditLogger $auditLogger)
    {
        $this->auditLogger = $auditLogger;
    }

    /**
     * Generate an audit report in the specified format.
     *
     * @param string $format The report format (csv, json, pdf)
     * @param array $filters Filters to apply to the audit logs
     * @param array $options Additional options for report generation
     * @return array|string The generated report data or content
     * @throws \Exception If reporting is disabled or format is not supported
     */
    public function generate(string $format, array $filters = [], array $options = []): array|string
    {
        if (!$this->isReportingEnabled()) {
            throw new \Exception('Compliance reporting is disabled');
        }

        if (!$this->isFormatSupported($format)) {
            throw new \Exception("Report format '{$format}' is not supported");
        }

        try {
            // Build the audit log query with filters
            $auditLogs = $this->buildFilteredQuery($filters)->get();

            // Generate report based on format
            $report = match ($format) {
                'csv' => $this->generateCsv($auditLogs, $options),
                'json' => $this->generateJson($auditLogs, $options),
                'pdf' => $this->generatePdf($auditLogs, $options),
                default => throw new \Exception("Unsupported format: {$format}"),
            };

            // Log the report generation via AuditLogger
            $this->auditLogger->logCustomEvent(
                'audit_report_generated',
                AuditLog::class,
                null,
                [],
                [
                    'format' => $format,
                    'filters' => $filters,
                    'record_count' => $auditLogs->count(),
                ],
                ['audit', 'report', 'export']
            );

            Log::info('Audit report generated', [
                'format' => $format,
                'filters' => $filters,
                'record_count' => $auditLogs->count(),
            ]);

            return $report;
        } catch (\Exception $e) {
            Log::error('Error generating audit report', [
                'format' => $format,
                'filters' => $filters,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Generate a CSV format audit report.
     *
     * @param Collection $auditLogs The audit logs to include in the report
     * @param array $options Additional options for CSV generation
     * @return string The CSV content
     */
    public function generateCsv(Collection $auditLogs, array $options = []): string
    {
        $includeHeaders = $options['include_headers'] ?? true;
        $delimiter = $options['delimiter'] ?? ',';
        $enclosure = $options['enclosure'] ?? '"';

        $csv = [];

        // Add headers
        if ($includeHeaders) {
            $csv[] = $this->formatCsvRow([
                'ID',
                'Event',
                'User',
                'Model Type',
                'Model ID',
                'Old Values',
                'New Values',
                'IP Address',
                'User Agent',
                'Tags',
                'Created At',
            ], $delimiter, $enclosure);
        }

        // Add data rows
        foreach ($auditLogs as $log) {
            $csv[] = $this->formatCsvRow([
                $log->id,
                $log->event,
                $log->user ? $log->user->name : 'System',
                class_basename($log->auditable_type),
                $log->auditable_id,
                $this->formatArrayForCsv($log->old_values ?? []),
                $this->formatArrayForCsv($log->new_values ?? []),
                $log->ip_address ?? '',
                $log->user_agent ?? '',
                implode(', ', $log->tags ?? []),
                $log->created_at?->toIso8601String() ?? '',
            ], $delimiter, $enclosure);
        }

        Log::info('Audit report generated in CSV format', [
            'record_count' => $auditLogs->count(),
        ]);

        return implode("\n", $csv);
    }

    /**
     * Generate a JSON format audit report.
     *
     * @param Collection $auditLogs The audit logs to include in the report
     * @param array $options Additional options for JSON generation
     * @return array The structured audit report data
     */
    public function generateJson(Collection $auditLogs, array $options = []): array
    {
        $prettyPrint = $options['pretty_print'] ?? false;
        $includeMetadata = $options['include_metadata'] ?? true;

        $reportData = [
            'audit_logs' => $auditLogs->map(function ($log) {
                return [
                    'id' => $log->id,
                    'event' => $log->event,
                    'user' => [
                        'id' => $log->user_id,
                        'name' => $log->user ? $log->user->name : 'System',
                        'email' => $log->user?->email,
                    ],
                    'auditable' => [
                        'type' => $log->auditable_type,
                        'id' => $log->auditable_id,
                        'model_name' => class_basename($log->auditable_type),
                    ],
                    'changes' => [
                        'old_values' => $log->old_values ?? [],
                        'new_values' => $log->new_values ?? [],
                        'modified_fields' => $log->getChanges(),
                    ],
                    'metadata' => [
                        'ip_address' => $log->ip_address,
                        'user_agent' => $log->user_agent,
                        'tags' => $log->tags ?? [],
                    ],
                    'created_at' => $log->created_at?->toIso8601String(),
                ];
            })->toArray(),
        ];

        // Add metadata if requested
        if ($includeMetadata) {
            $reportData = array_merge([
                'report_metadata' => [
                    'generated_at' => now()->toIso8601String(),
                    'total_records' => $auditLogs->count(),
                    'format' => 'json',
                    'report_type' => 'audit_log',
                ],
            ], $reportData);
        }

        Log::info('Audit report generated in JSON format', [
            'record_count' => $auditLogs->count(),
        ]);

        return $reportData;
    }

    /**
     * Generate a PDF format audit report.
     *
     * @param Collection $auditLogs The audit logs to include in the report
     * @param array $options Additional options for PDF generation
     * @return string The PDF content
     */
    public function generatePdf(Collection $auditLogs, array $options = []): string
    {
        $title = $options['title'] ?? 'Audit Log Report';
        $includeStatistics = $options['include_statistics'] ?? true;
        $orientation = $options['orientation'] ?? 'landscape';
        $paperSize = $options['paper_size'] ?? 'a4';

        // Prepare statistics if requested
        $statistics = null;
        if ($includeStatistics) {
            $statistics = [
                'by_event' => $auditLogs->groupBy('event')->map->count()->toArray(),
                'by_model' => $auditLogs->groupBy(function ($log) {
                    return class_basename($log->auditable_type);
                })->map->count()->toArray(),
            ];
        }

        // Generate HTML content for PDF
        $html = $this->generatePdfHtml($title, $auditLogs, $statistics);

        // Generate PDF using dompdf
        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper($paperSize, $orientation);

        Log::info('Audit report generated in PDF format', [
            'record_count' => $auditLogs->count(),
        ]);

        return $pdf->output();
    }

    /**
     * Generate HTML content for the PDF report.
     *
     * @param string $title The report title
     * @param Collection $auditLogs The audit logs collection
     * @param array|null $statistics Optional statistics data
     * @return string The HTML content
     */
    protected function generatePdfHtml(string $title, Collection $auditLogs, ?array $statistics = null): string
    {
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>' . htmlspecialchars($title) . '</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 20px;
            color: #333;
        }
        h1 {
            font-size: 18px;
            color: #2c3e50;
            margin-bottom: 10px;
            border-bottom: 2px solid #3498db;
            padding-bottom: 5px;
        }
        h2 {
            font-size: 14px;
            color: #34495e;
            margin-top: 20px;
            margin-bottom: 10px;
        }
        h3 {
            font-size: 12px;
            color: #7f8c8d;
            margin-top: 15px;
            margin-bottom: 8px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 9px;
        }
        th {
            background-color: #3498db;
            color: white;
            border: 1px solid #2980b9;
            padding: 8px;
            text-align: left;
            font-weight: bold;
        }
        td {
            border: 1px solid #bdc3c7;
            padding: 6px;
            vertical-align: top;
        }
        tr:nth-child(even) {
            background-color: #ecf0f1;
        }
        .metadata {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f8f9fa;
            border-left: 4px solid #3498db;
        }
        .metadata p {
            margin: 5px 0;
            font-size: 10px;
        }
        .stats-container {
            margin-bottom: 20px;
        }
        .stat-box {
            display: inline-block;
            width: 48%;
            padding: 10px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            margin-right: 2%;
            vertical-align: top;
        }
        .stat-item {
            padding: 3px 0;
            border-bottom: 1px solid #dee2e6;
        }
        .stat-item:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>';

        // Header
        $html .= '<h1>' . htmlspecialchars($title) . '</h1>';
        $html .= '<div class="metadata">';
        $html .= '<p><strong>Generated:</strong> ' . now()->format('Y-m-d H:i:s') . '</p>';
        $html .= '<p><strong>Total Records:</strong> ' . number_format($auditLogs->count()) . '</p>';
        $html .= '</div>';

        // Statistics section
        if ($statistics) {
            $html .= '<h2>Report Statistics</h2>';
            $html .= '<div class="stats-container">';

            // Event statistics
            if (!empty($statistics['by_event'])) {
                $html .= '<div class="stat-box">';
                $html .= '<h3>Activity by Event Type</h3>';
                foreach ($statistics['by_event'] as $event => $count) {
                    $html .= '<div class="stat-item">';
                    $html .= '<strong>' . htmlspecialchars(ucfirst($event)) . ':</strong> ' . number_format($count);
                    $html .= '</div>';
                }
                $html .= '</div>';
            }

            // Model statistics
            if (!empty($statistics['by_model'])) {
                $html .= '<div class="stat-box">';
                $html .= '<h3>Activity by Model Type</h3>';
                foreach ($statistics['by_model'] as $model => $count) {
                    $html .= '<div class="stat-item">';
                    $html .= '<strong>' . htmlspecialchars($model) . ':</strong> ' . number_format($count);
                    $html .= '</div>';
                }
                $html .= '</div>';
            }

            $html .= '</div>';
        }

        // Audit logs table
        $html .= '<h2>Audit Log Details</h2>';
        $html .= '<table>';
        $html .= '<thead><tr>
            <th width="5%">ID</th>
            <th width="12%">Event</th>
            <th width="15%">Model</th>
            <th width="15%">User</th>
            <th width="13%">IP Address</th>
            <th width="15%">Tags</th>
            <th width="15%">Timestamp</th>
        </tr></thead>';
        $html .= '<tbody>';

        foreach ($auditLogs as $log) {
            $userName = $log->user ? htmlspecialchars($log->user->name) : ($log->user_id ? "User #{$log->user_id}" : 'System');
            $modelName = htmlspecialchars(class_basename($log->auditable_type));
            $tags = is_array($log->tags) && !empty($log->tags) ? htmlspecialchars(implode(', ', $log->tags)) : '-';
            $createdAt = $log->created_at ? $log->created_at->format('Y-m-d H:i:s') : 'N/A';

            $html .= '<tr>';
            $html .= '<td>' . $log->id . '</td>';
            $html .= '<td>' . htmlspecialchars(ucfirst($log->event)) . '</td>';
            $html .= '<td>' . $modelName . ' #' . $log->auditable_id . '</td>';
            $html .= '<td>' . $userName . '</td>';
            $html .= '<td>' . htmlspecialchars($log->ip_address ?? 'N/A') . '</td>';
            $html .= '<td>' . $tags . '</td>';
            $html .= '<td>' . $createdAt . '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';
        $html .= '</body></html>';

        return $html;
    }

    /**
     * Build a filtered query for audit logs.
     *
     * @param array $filters Filters to apply
     * @return Builder The filtered query builder
     */
    public function buildFilteredQuery(array $filters = []): Builder
    {
        $query = AuditLog::query()->with(['user', 'auditable']);

        // Filter by date range
        if (isset($filters['start_date'])) {
            $query->where('created_at', '>=', $filters['start_date']);
        }

        if (isset($filters['end_date'])) {
            $query->where('created_at', '<=', $filters['end_date']);
        }

        // Filter by event type
        if (isset($filters['event'])) {
            if (is_array($filters['event'])) {
                $query->byEvents($filters['event']);
            } else {
                $query->byEvent($filters['event']);
            }
        }

        // Filter by model type
        if (isset($filters['model_type'])) {
            $query->byAuditableType($filters['model_type']);
        }

        // Filter by user
        if (isset($filters['user_id'])) {
            $query->byUser($filters['user_id']);
        }

        // Filter by IP address
        if (isset($filters['ip_address'])) {
            $query->byIpAddress($filters['ip_address']);
        }

        // Filter by tags
        if (isset($filters['tags'])) {
            if (is_array($filters['tags'])) {
                $query->withTags($filters['tags']);
            } else {
                $query->withTag($filters['tags']);
            }
        }

        // Apply limit
        if (isset($filters['limit'])) {
            $query->limit($filters['limit']);
        }

        // Apply ordering
        $orderBy = $filters['order_by'] ?? 'created_at';
        $orderDirection = $filters['order_direction'] ?? 'desc';
        $query->orderBy($orderBy, $orderDirection);

        return $query;
    }

    /**
     * Get audit report summary statistics.
     *
     * @param array $filters Filters to apply
     * @return array Summary statistics
     */
    public function getSummary(array $filters = []): array
    {
        $query = $this->buildFilteredQuery($filters);
        $auditLogs = $query->get();

        $summary = [
            'total_records' => $auditLogs->count(),
            'date_range' => [
                'start' => $filters['start_date'] ?? 'N/A',
                'end' => $filters['end_date'] ?? 'N/A',
            ],
            'by_event' => [],
            'by_model' => [],
            'by_user' => [],
            'generated_at' => now()->toIso8601String(),
        ];

        // Group by event type
        $summary['by_event'] = $auditLogs->groupBy('event')->map(function ($group) {
            return $group->count();
        })->toArray();

        // Group by model type
        $summary['by_model'] = $auditLogs->groupBy('auditable_type')->map(function ($group, $type) {
            return [
                'type' => class_basename($type),
                'count' => $group->count(),
            ];
        })->values()->toArray();

        // Group by user (top 10)
        $summary['by_user'] = $auditLogs->groupBy('user_id')
            ->sortByDesc(function ($group) {
                return $group->count();
            })
            ->take(10)
            ->map(function ($group, $userId) {
                $user = $group->first()->user;
                return [
                    'user_id' => $userId,
                    'user_name' => $user ? $user->name : 'System',
                    'count' => $group->count(),
                ];
            })
            ->values()
            ->toArray();

        return $summary;
    }

    /**
     * Export audit report to a file.
     *
     * @param string $format The report format
     * @param string $filePath The file path to save the report
     * @param array $filters Filters to apply
     * @param array $options Additional options
     * @return bool True if the file was saved successfully
     * @throws \Exception If reporting is disabled or an error occurs
     */
    public function exportToFile(string $format, string $filePath, array $filters = [], array $options = []): bool
    {
        try {
            $reportContent = $this->generate($format, $filters, $options);

            // Convert array to JSON string if needed
            if (is_array($reportContent)) {
                $prettyPrint = $options['pretty_print'] ?? true;
                $reportContent = json_encode(
                    $reportContent,
                    $prettyPrint ? JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES : 0
                );
            }

            // Ensure directory exists
            $directory = dirname($filePath);
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            // Write to file
            $result = file_put_contents($filePath, $reportContent);

            if ($result === false) {
                throw new \Exception("Failed to write report to file: {$filePath}");
            }

            Log::info('Audit report exported to file', [
                'format' => $format,
                'file_path' => $filePath,
                'file_size' => $result,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Error exporting audit report to file', [
                'format' => $format,
                'file_path' => $filePath,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Protected helper methods
     */

    /**
     * Format a CSV row.
     *
     * @param array $data The row data
     * @param string $delimiter The field delimiter
     * @param string $enclosure The field enclosure
     * @return string The formatted CSV row
     */
    protected function formatCsvRow(array $data, string $delimiter = ',', string $enclosure = '"'): string
    {
        $row = [];

        foreach ($data as $field) {
            // Escape enclosure characters
            $field = str_replace($enclosure, $enclosure . $enclosure, (string) $field);

            // Enclose field if it contains delimiter, enclosure, or newline
            if (strpos($field, $delimiter) !== false ||
                strpos($field, $enclosure) !== false ||
                strpos($field, "\n") !== false ||
                strpos($field, "\r") !== false) {
                $field = $enclosure . $field . $enclosure;
            }

            $row[] = $field;
        }

        return implode($delimiter, $row);
    }

    /**
     * Format an array for CSV output.
     *
     * @param array $array The array to format
     * @return string The formatted string representation
     */
    protected function formatArrayForCsv(array $array): string
    {
        if (empty($array)) {
            return '';
        }

        $formatted = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $value = json_encode($value);
            }
            $formatted[] = "{$key}: {$value}";
        }

        return implode('; ', $formatted);
    }

    /**
     * Check if compliance reporting is enabled.
     *
     * @return bool
     */
    protected function isReportingEnabled(): bool
    {
        return Config::get('compliance.enabled', true) &&
               Config::get('compliance.reporting.enabled', true);
    }

    /**
     * Check if a report format is supported.
     *
     * @param string $format The format to check
     * @return bool
     */
    protected function isFormatSupported(string $format): bool
    {
        $formats = Config::get('compliance.reporting.formats', []);

        return isset($formats[$format]) && $formats[$format] === true;
    }
}
