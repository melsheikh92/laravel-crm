<?php

namespace Webkul\Admin\Http\Controllers\Marketing;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Webkul\Admin\DataGrids\Marketing\CampaignDataGrid;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Marketing\Repositories\EmailCampaignRepository;
use Webkul\Marketing\Services\CampaignService;
use Webkul\Marketing\Services\RecipientService;

class CampaignController extends Controller
{
    public function __construct(
        protected EmailCampaignRepository $campaignRepository,
        protected CampaignService $campaignService,
        protected RecipientService $recipientService
    ) {}

    /**
     * Display a listing of campaigns.
     */
    public function index(): View|JsonResponse
    {
        if (request()->ajax()) {
            return datagrid(CampaignDataGrid::class)->process();
        }

        return view('admin::marketing.campaigns.index');
    }

    /**
     * Show the form for creating a new campaign.
     */
    public function create(): View
    {
        return view('admin::marketing.campaigns.create');
    }

    /**
     * Store a newly created campaign.
     */
    public function store(): JsonResponse
    {
        $rules = [
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'status' => 'nullable|in:draft,scheduled',
            'scheduled_at' => 'nullable|date|after:now',
            'sender_name' => 'nullable|string|max:255',
            'sender_email' => 'nullable|email',
            'reply_to' => 'nullable|email',
            'template_id' => 'nullable|exists:email_templates,id',
            'recipients' => 'nullable|array',
        ];

        // Require scheduled_at when status is scheduled
        if (request('status') === 'scheduled') {
            $rules['scheduled_at'] = 'required|date|after:now';
        }

        $validator = validator(request()->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $campaign = $this->campaignService->create(request()->all());

            return response()->json([
                'data' => $campaign,
                'message' => trans('admin::app.marketing.campaigns.index.create-success') ?: 'Campaign created successfully.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => ['general' => [$e->getMessage()]],
            ], 422);
        }
    }

    /**
     * Display the specified campaign.
     */
    public function show(int $id): View|JsonResponse
    {
        $campaign = $this->campaignRepository->with(['recipients', 'template', 'user'])->findOrFail($id);

        if (request()->ajax()) {
            $statistics = $this->campaignService->getStatistics($id);

            return response()->json([
                'data' => [
                    'campaign' => $campaign,
                    'statistics' => $statistics,
                ],
            ]);
        }

        return view('admin::marketing.campaigns.view', compact('campaign'));
    }

    /**
     * Show the form for editing the specified campaign.
     */
    public function edit(int $id): View|JsonResponse
    {
        $campaign = $this->campaignRepository->with(['recipients', 'template'])->findOrFail($id);

        if (request()->ajax()) {
            return response()->json([
                'data' => $campaign,
            ]);
        }

        return view('admin::marketing.campaigns.edit', compact('campaign'));
    }

    /**
     * Update the specified campaign.
     */
    public function update(int $id): JsonResponse
    {
        $rules = [
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'status' => 'nullable|in:draft,scheduled',
            'scheduled_at' => 'nullable|date|after:now',
            'sender_name' => 'nullable|string|max:255',
            'sender_email' => 'nullable|email',
            'reply_to' => 'nullable|email',
            'template_id' => 'nullable|exists:email_templates,id',
            'recipients' => 'nullable|array',
        ];

        // Require scheduled_at when status is scheduled
        if (request('status') === 'scheduled') {
            $rules['scheduled_at'] = 'required|date|after:now';
        }

        $validator = validator(request()->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $campaign = $this->campaignService->update(request()->all(), $id);

            return response()->json([
                'data' => $campaign,
                'message' => trans('admin::app.marketing.campaigns.update-success') ?: 'Campaign updated successfully.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => ['general' => [$e->getMessage()]],
            ], 422);
        }
    }

    /**
     * Remove the specified campaign.
     */
    public function destroy(int $id): JsonResponse
    {
        $campaign = $this->campaignRepository->findOrFail($id);

        if (in_array($campaign->status, ['sending', 'completed'])) {
            return response()->json([
                'message' => trans('admin::app.marketing.campaigns.cannot-delete'),
            ], 400);
        }

        $this->campaignRepository->delete($id);

        return response()->json([
            'message' => trans('admin::app.marketing.campaigns.delete-success'),
        ]);
    }

    /**
     * Schedule a campaign.
     */
    public function schedule(int $id): JsonResponse
    {
        $this->validate(request(), [
            'scheduled_at' => 'nullable|date|after:now',
        ]);

        try {
            $campaign = $this->campaignService->schedule($id, request('scheduled_at'));

            return response()->json([
                'data' => $campaign,
                'message' => trans('admin::app.marketing.campaigns.schedule-success'),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Send a campaign immediately.
     */
    public function send(int $id): JsonResponse
    {
        try {
            $campaign = $this->campaignService->send($id);

            return response()->json([
                'data' => $campaign,
                'message' => trans('admin::app.marketing.campaigns.send-success'),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Cancel a campaign.
     */
    public function cancel(int $id): JsonResponse
    {
        try {
            $campaign = $this->campaignService->cancel($id);

            return response()->json([
                'data' => $campaign,
                'message' => trans('admin::app.marketing.campaigns.cancel-success'),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get campaign statistics.
     */
    public function statistics(int $id): JsonResponse
    {
        $statistics = $this->campaignService->getStatistics($id);

        return response()->json([
            'data' => $statistics,
        ]);
    }

    /**
     * Add recipients to campaign.
     */
    public function addRecipients(int $id): JsonResponse
    {
        $this->validate(request(), [
            'recipients' => 'required|array',
            'recipients.*.email' => 'required|email',
            'recipients.*.person_id' => 'nullable|exists:persons,id',
            'recipients.*.lead_id' => 'nullable|exists:leads,id',
        ]);

        try {
            $this->campaignService->addRecipients($id, request('recipients'));

            return response()->json([
                'message' => trans('admin::app.marketing.campaigns.recipients-added'),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Parse CSV and return recipients.
     */
    public function parseCsv(): JsonResponse
    {
        $this->validate(request(), [
            'csv' => 'required|string',
        ]);

        try {
            $recipients = $this->recipientService->parseCsvRecipients(request('csv'));

            return response()->json([
                'data' => $recipients,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}

