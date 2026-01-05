<?php

namespace Webkul\Admin\Http\Controllers\Mail;

use Exception;
use Illuminate\Http\JsonResponse;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Email\Services\AIEmailService;
use Webkul\Email\Services\AIEmailSummaryService;

class AIEmailController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected AIEmailService $aiEmailService,
        protected AIEmailSummaryService $aiEmailSummaryService
    ) {}

    /**
     * Generate AI reply for an email.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateReply(): JsonResponse
    {
        $this->validate(request(), [
            'email_id' => 'required|integer|exists:emails,id',
            'tone' => 'nullable|string|in:professional,casual,formal',
            'length' => 'nullable|string|in:short,medium,long',
        ]);

        try {
            $emailId = request('email_id');
            $tone = request('tone', 'professional');
            $length = request('length', 'medium');

            $result = $this->aiEmailService->generateReply($emailId, $tone, $length);

            if (isset($result['error'])) {
                return response()->json([
                    'message' => $result['error'],
                ], 400);
            }

            return response()->json([
                'data' => [
                    'reply' => $result['reply'],
                    'model' => $result['model'] ?? null,
                ],
                'message' => trans('admin::app.mail.ai-reply-generated'),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get AI summary for an email thread.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSummary($id): JsonResponse
    {
        try {
            $result = $this->aiEmailSummaryService->getSummary($id);

            if (isset($result['error'])) {
                return response()->json([
                    'message' => $result['error'],
                ], 400);
            }

            return response()->json([
                'data' => [
                    'summary' => $result['summary'],
                    'cached' => $result['cached'] ?? false,
                    'model' => $result['model'] ?? null,
                ],
                'message' => trans('admin::app.mail.ai-summary-generated'),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}


