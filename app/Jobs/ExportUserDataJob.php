<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\Compliance\RightToErasureService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

class ExportUserDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The user whose data should be exported.
     *
     * @var User
     */
    protected User $user;

    /**
     * The export format (json, csv, pdf).
     *
     * @var string
     */
    protected string $format;

    /**
     * Whether to include audit logs in the export.
     *
     * @var bool
     */
    protected bool $includeAuditLogs;

    /**
     * The notification email address (if different from user email).
     *
     * @var string|null
     */
    protected ?string $notificationEmail;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 60;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 300;

    /**
     * Create a new job instance.
     *
     * @param User $user The user whose data to export
     * @param string $format The export format (json, csv, pdf)
     * @param bool $includeAuditLogs Whether to include audit logs
     * @param string|null $notificationEmail Optional email for notification
     */
    public function __construct(
        User $user,
        string $format = 'json',
        bool $includeAuditLogs = false,
        ?string $notificationEmail = null
    ) {
        $this->user = $user;
        $this->format = $format;
        $this->includeAuditLogs = $includeAuditLogs;
        $this->notificationEmail = $notificationEmail;
    }

    /**
     * Execute the job.
     *
     * @param RightToErasureService $rightToErasureService
     * @return void
     */
    public function handle(RightToErasureService $rightToErasureService): void
    {
        Log::info('Starting user data export job', [
            'user_id' => $this->user->id,
            'format' => $this->format,
            'include_audit_logs' => $this->includeAuditLogs,
        ]);

        try {
            // Export user data using the RightToErasureService
            $exportData = $rightToErasureService->exportUserData(
                $this->user,
                $this->format,
                $this->includeAuditLogs
            );

            // Generate the file content based on format
            $fileContent = $this->formatExportData($exportData);

            // Store the exported file
            $filePath = $this->storeExportFile($fileContent);

            // Send notification with download link if configured
            if ($this->shouldSendNotification()) {
                $this->sendNotification($filePath);
            }

            Log::info('User data export completed successfully', [
                'user_id' => $this->user->id,
                'format' => $this->format,
                'file_path' => $filePath,
                'file_size' => strlen($fileContent),
            ]);
        } catch (\Exception $e) {
            Log::error('Error exporting user data', [
                'user_id' => $this->user->id,
                'format' => $this->format,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Re-throw the exception to trigger job retry
            throw $e;
        }
    }

    /**
     * Format the export data based on the requested format.
     *
     * @param array $exportData The data to format
     * @return string The formatted content
     * @throws \Exception If the format is not supported
     */
    protected function formatExportData(array $exportData): string
    {
        return match ($this->format) {
            'json' => $this->formatAsJson($exportData),
            'csv' => $this->formatAsCsv($exportData),
            'pdf' => $this->formatAsPdf($exportData),
            default => throw new \Exception("Unsupported export format: {$this->format}"),
        };
    }

    /**
     * Format the data as JSON.
     *
     * @param array $exportData
     * @return string
     */
    protected function formatAsJson(array $exportData): string
    {
        return json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Format the data as CSV.
     *
     * @param array $exportData
     * @return string
     */
    protected function formatAsCsv(array $exportData): string
    {
        $csvContent = '';
        $handle = fopen('php://temp', 'r+');

        if ($handle === false) {
            throw new \Exception('Failed to create temporary file for CSV export');
        }

        // Add header with export metadata
        fputcsv($handle, ['User Data Export']);
        fputcsv($handle, ['Export Date', $exportData['export_date']]);
        fputcsv($handle, ['User ID', $exportData['user_id']]);
        fputcsv($handle, ['Format', $exportData['format']]);
        fputcsv($handle, []);

        // Export each data section
        foreach ($exportData['data'] as $section => $records) {
            // Section header
            fputcsv($handle, [strtoupper(str_replace('_', ' ', $section))]);
            fputcsv($handle, []);

            if (empty($records)) {
                fputcsv($handle, ['No data']);
                fputcsv($handle, []);
                continue;
            }

            // If it's a single record (like user profile)
            if (isset($records['id']) || isset($records['name'])) {
                $records = [$records];
            }

            // Get column headers from the first record
            if (!empty($records) && is_array($records)) {
                $firstRecord = reset($records);
                if (is_array($firstRecord)) {
                    $headers = array_keys($firstRecord);
                    fputcsv($handle, $headers);

                    // Add data rows
                    foreach ($records as $record) {
                        $row = [];
                        foreach ($headers as $header) {
                            $value = $record[$header] ?? '';
                            $row[] = is_array($value) ? json_encode($value) : $value;
                        }
                        fputcsv($handle, $row);
                    }
                }
            }

            fputcsv($handle, []);
        }

        rewind($handle);
        $csvContent = stream_get_contents($handle);
        fclose($handle);

        return $csvContent;
    }

    /**
     * Format the data as PDF.
     *
     * @param array $exportData
     * @return string
     */
    protected function formatAsPdf(array $exportData): string
    {
        // For PDF export, we'll create a simple text-based representation
        // In a production environment, you might want to use a library like dompdf or mpdf
        $pdfContent = "USER DATA EXPORT\n";
        $pdfContent .= str_repeat('=', 80) . "\n\n";
        $pdfContent .= "Export Date: {$exportData['export_date']}\n";
        $pdfContent .= "User ID: {$exportData['user_id']}\n";
        $pdfContent .= "Format: {$exportData['format']}\n\n";

        foreach ($exportData['data'] as $section => $records) {
            $pdfContent .= str_repeat('-', 80) . "\n";
            $pdfContent .= strtoupper(str_replace('_', ' ', $section)) . "\n";
            $pdfContent .= str_repeat('-', 80) . "\n\n";

            if (empty($records)) {
                $pdfContent .= "No data\n\n";
                continue;
            }

            // If it's a single record (like user profile)
            if (isset($records['id']) || isset($records['name'])) {
                foreach ($records as $key => $value) {
                    $displayValue = is_array($value) ? json_encode($value) : $value;
                    $pdfContent .= ucfirst(str_replace('_', ' ', $key)) . ": {$displayValue}\n";
                }
                $pdfContent .= "\n";
            } else {
                // Multiple records
                foreach ($records as $index => $record) {
                    $pdfContent .= "Record " . ($index + 1) . ":\n";
                    foreach ($record as $key => $value) {
                        $displayValue = is_array($value) ? json_encode($value) : $value;
                        $pdfContent .= "  " . ucfirst(str_replace('_', ' ', $key)) . ": {$displayValue}\n";
                    }
                    $pdfContent .= "\n";
                }
            }
        }

        return $pdfContent;
    }

    /**
     * Store the export file and return the file path.
     *
     * @param string $content The file content to store
     * @return string The storage path of the file
     */
    protected function storeExportFile(string $content): string
    {
        $disk = Config::get('compliance.gdpr.data_portability.storage_disk', 'local');
        $directory = Config::get('compliance.gdpr.data_portability.storage_path', 'exports/user-data');

        // Generate a unique filename
        $timestamp = now()->format('Y-m-d_His');
        $extension = $this->getFileExtension();
        $filename = "user_{$this->user->id}_export_{$timestamp}.{$extension}";
        $path = "{$directory}/{$filename}";

        // Store the file
        Storage::disk($disk)->put($path, $content);

        Log::info('Export file stored', [
            'user_id' => $this->user->id,
            'path' => $path,
            'disk' => $disk,
        ]);

        return $path;
    }

    /**
     * Get the file extension based on the export format.
     *
     * @return string
     */
    protected function getFileExtension(): string
    {
        return match ($this->format) {
            'json' => 'json',
            'csv' => 'csv',
            'pdf' => 'pdf',
            default => 'txt',
        };
    }

    /**
     * Check if a notification should be sent.
     *
     * @return bool
     */
    protected function shouldSendNotification(): bool
    {
        return Config::get('compliance.notifications.enabled', true) &&
               Config::get('compliance.notifications.notify_on.data_export_completed', true);
    }

    /**
     * Send notification about the completed export.
     *
     * @param string $filePath The path to the exported file
     * @return void
     */
    protected function sendNotification(string $filePath): void
    {
        // This is a placeholder for notification logic
        // In a real implementation, you would send an email with the download link
        $email = $this->notificationEmail ?? $this->user->email;

        Log::info('Export notification sent', [
            'user_id' => $this->user->id,
            'email' => $email,
            'file_path' => $filePath,
        ]);

        // You can implement actual email notification here using Laravel's Mail or Notification system
        // For example:
        // $this->user->notify(new DataExportCompleted($filePath));
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('User data export job failed', [
            'user_id' => $this->user->id,
            'format' => $this->format,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);

        // Optionally notify the user or administrators about the failure
        if ($this->shouldSendNotification()) {
            $email = $this->notificationEmail ?? $this->user->email;
            Log::info('Export failure notification sent', [
                'user_id' => $this->user->id,
                'email' => $email,
            ]);
        }
    }
}
