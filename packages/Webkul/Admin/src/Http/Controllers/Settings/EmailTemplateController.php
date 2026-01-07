<?php

namespace Webkul\Admin\Http\Controllers\Settings;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Event;
use Illuminate\View\View;
use Webkul\Admin\DataGrids\Settings\EmailTemplateDataGrid;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Automation\Helpers\Entity;
use Webkul\EmailTemplate\Repositories\EmailTemplateRepository;

class EmailTemplateController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected EmailTemplateRepository $emailTemplateRepository,
        protected Entity $workflowEntityHelper
    ) {}

    /**
     * Display a listing of the email template.
     */
    public function index(): View|JsonResponse
    {
        if (request()->ajax()) {
            return datagrid(EmailTemplateDataGrid::class)->process();
        }

        return view('admin::settings.email-templates.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $placeholders = $this->workflowEntityHelper->getEmailTemplatePlaceholders();

        return view('admin::settings.email-templates.create', compact('placeholders'));
    }

    /**
     * Store a newly created email templates in storage.
     */
    public function store(): RedirectResponse
    {
        $this->validate(request(), [
            'name'    => 'required|unique:email_templates,name',
            'subject' => 'required',
            'content' => 'required',
        ]);

        Event::dispatch('settings.email_templates.create.before');

        $emailTemplate = $this->emailTemplateRepository->create(request()->all());

        Event::dispatch('settings.email_templates.create.after', $emailTemplate);

        session()->flash('success', trans('admin::app.settings.email-template.index.create-success'));

        return redirect()->route('admin.settings.email_templates.index');
    }

    /**
     * Show the form for editing the specified email template.
     */
    public function edit(int $id): View
    {
        $emailTemplate = $this->emailTemplateRepository->findOrFail($id);

        $placeholders = $this->workflowEntityHelper->getEmailTemplatePlaceholders();

        return view('admin::settings.email-templates.edit', compact('emailTemplate', 'placeholders'));
    }

    /**
     * Update the specified email template in storage.
     */
    public function update(int $id): RedirectResponse
    {
        $this->validate(request(), [
            'name'    => 'required|unique:email_templates,name,'.$id,
            'subject' => 'required',
            'content' => 'required',
        ]);

        Event::dispatch('settings.email_templates.update.before', $id);

        $emailTemplate = $this->emailTemplateRepository->update(request()->all(), $id);

        Event::dispatch('settings.email_templates.update.after', $emailTemplate);

        session()->flash('success', trans('admin::app.settings.email-template.index.update-success'));

        return redirect()->route('admin.settings.email_templates.index');
    }

    /**
     * Remove the specified email template from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $emailTemplate = $this->emailTemplateRepository->findOrFail($id);

        try {
            Event::dispatch('settings.email_templates.delete.before', $id);

            $emailTemplate->delete($id);

            Event::dispatch('settings.email_templates.delete.after', $id);

            return response()->json([
                'message' => trans('admin::app.settings.email-template.index.delete-success'),
            ], 200);
        } catch (\Exception $exception) {
            return response()->json([
                'message' => trans('admin::app.settings.email-template.index.delete-failed'),
            ], 400);
        }

        return response()->json([
            'message' => trans('admin::app.settings.email-template.index.delete-failed'),
        ], 400);
    }

    /**
     * Preview email template with sample data.
     */
    public function preview(): JsonResponse
    {
        $this->validate(request(), [
            'subject' => 'required|string',
            'content' => 'required|string',
        ]);

        try {
            $subject = request('subject');
            $content = request('content');

            // Replace placeholders with sample data
            $previewSubject = $this->replacePlaceholdersWithSampleData($subject);
            $previewContent = $this->replacePlaceholdersWithSampleData($content);

            return response()->json([
                'data' => [
                    'subject' => $previewSubject,
                    'content' => $previewContent,
                ],
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 400);
        }
    }

    /**
     * Send test email using configured SMTP settings.
     */
    public function sendTest(): JsonResponse
    {
        $this->validate(request(), [
            'email'   => 'required|email',
            'subject' => 'required|string',
            'content' => 'required|string',
        ]);

        try {
            $recipientEmail = request('email');
            $subject = request('subject');
            $content = request('content');

            // Replace placeholders with sample data
            $testSubject = $this->replacePlaceholdersWithSampleData($subject);
            $testContent = $this->replacePlaceholdersWithSampleData($content);

            // Get from address and name from config
            $fromAddress = core()->getConfigData('emails.smtp.from_address')
                ?? config('mail.from.address');
            $fromName = core()->getConfigData('emails.smtp.from_name')
                ?? config('mail.from.name');

            // Send test email using configured SMTP settings
            \Illuminate\Support\Facades\Mail::send([], [], function ($message) use ($recipientEmail, $testSubject, $testContent, $fromAddress, $fromName) {
                $message->to($recipientEmail)
                    ->from($fromAddress, $fromName)
                    ->subject($testSubject)
                    ->html($testContent);
            });

            return response()->json([
                'message' => trans('admin::app.settings.email-template.index.send-test-success'),
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'message' => trans('admin::app.settings.email-template.index.send-test-failed').': '.$exception->getMessage(),
            ], 400);
        }
    }

    /**
     * Replace placeholders with sample data.
     */
    protected function replacePlaceholdersWithSampleData(string $content): string
    {
        // Get all placeholder entities
        $placeholders = $this->workflowEntityHelper->getEmailTemplatePlaceholders();

        // Define sample data for each entity type
        $sampleData = $this->getSampleData();

        // Replace each placeholder with sample data
        foreach ($placeholders as $placeholder) {
            foreach ($placeholder['menu'] as $item) {
                $placeholderKey = $item['value'];

                // Extract entity type and attribute from placeholder
                // Format: {%entity_type.attribute%} or {% entity_type.attribute %}
                preg_match('/\{%\s*([^.]+)\.([^%]+)\s*%\}/', $placeholderKey, $matches);

                if (count($matches) === 3) {
                    $entityType = $matches[1];
                    $attribute = $matches[2];

                    $sampleValue = $sampleData[$entityType][$attribute] ?? '[Sample '.$item['text'].']';

                    // Replace both formats: {%...%} and {% ... %}
                    $content = str_replace($placeholderKey, $sampleValue, $content);
                    $content = str_replace(
                        str_replace(['{%', '%}'], ['{% ', ' %}'], $placeholderKey),
                        $sampleValue,
                        $content
                    );
                }
            }
        }

        return $content;
    }

    /**
     * Get sample data for all entity types.
     */
    protected function getSampleData(): array
    {
        return [
            'leads' => [
                'title'                   => 'Sample Lead - New Business Opportunity',
                'description'             => 'This is a sample lead for a potential new business opportunity.',
                'lead_value'              => '$50,000.00',
                'status'                  => 'New',
                'lead_source'             => 'Website',
                'lead_type'               => 'New Business',
                'user_id'                 => 'John Smith',
                'expected_close_date'     => 'Dec 31, 2026',
                'created_at'              => 'Jan 06, 2026 10:30 AM',
            ],
            'persons' => [
                'name'                    => 'Jane Doe',
                'email'                   => 'jane.doe@example.com',
                'phone'                   => '+1 (555) 123-4567',
                'organization'            => 'Acme Corporation',
                'job_title'               => 'Marketing Director',
            ],
            'activities' => [
                'title'                   => 'Follow-up Call',
                'type'                    => 'Call',
                'comment'                 => 'Discussed project requirements and timeline.',
                'schedule_from'           => 'Jan 10, 2026 02:00 PM',
                'schedule_to'             => 'Jan 10, 2026 03:00 PM',
                'user_id'                 => 'John Smith',
            ],
            'quotes' => [
                'subject'                 => 'Quote for Web Development Services',
                'description'             => 'Comprehensive quote for custom web application development.',
                'sub_total'               => '$45,000.00',
                'discount_amount'         => '$5,000.00',
                'tax_amount'              => '$3,600.00',
                'adjustment_amount'       => '$0.00',
                'grand_total'             => '$43,600.00',
                'expired_at'              => 'Feb 06, 2026',
            ],
        ];
    }
}
