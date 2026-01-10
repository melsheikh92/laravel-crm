<?php

namespace Webkul\Marketing\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Webkul\Marketing\Repositories\CampaignRecipientRepository;

class TrackController extends Controller
{
    public function __construct(protected CampaignRecipientRepository $recipientRepository)
    {
    }

    /**
     * Track email open
     */
    public function open($id)
    {
        try {
            // Find recipient
            $recipient = $this->recipientRepository->findOrFail($id);

            // Update opened_at if not already set
            if (!$recipient->opened_at) {
                $recipient->update([
                    'opened_at' => now(),
                    'status' => 'opened'
                ]);
            }
        } catch (\Exception $e) {
            // Log error but don't break the image load
            \Log::error("Tracking Open Failed: " . $e->getMessage());
        }

        // Return 1x1 transparent GIF
        $content = base64_decode('R0lGODlhAQABAJAAAP8AAAAAACH5BAUQAAAALAAAAAABAAEAAAICBAEAOw==');
        return response($content)->header('Content-Type', 'image/gif');
    }

    /**
     * Track link click and redirect
     */
    public function click(Request $request, $id)
    {
        $url = $request->get('url');

        if (!$url) {
            abort(404);
        }

        try {
            $recipient = $this->recipientRepository->findOrFail($id);

            // Update clicked_at if not already set
            if (!$recipient->clicked_at) {
                // If status is sent, update to opened as well (since they clicked)
                $data = ['clicked_at' => now()];

                if (!$recipient->opened_at) {
                    $data['opened_at'] = now();
                    $data['status'] = 'clicked';
                }

                $recipient->update($data);
            }
        } catch (\Exception $e) {
            \Log::error("Tracking Click Failed: " . $e->getMessage());
        }

        return redirect()->away($url);
    }
}
