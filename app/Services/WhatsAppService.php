<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    /**
     * Send a WhatsApp message using the Meta Cloud API
     *
     * @param string $to The recipient's phone number (with country code, no + sign)
     * @param string $message The message text to send
     * @param string $phoneNumberId The WhatsApp Phone Number ID from Meta
     * @param string $accessToken The access token for authentication
     * @return array Response with success status and message
     */
    public function sendMessage(string $to, string $message, string $phoneNumberId, string $accessToken): array
    {
        try {
            // Clean the phone number (remove any non-digit characters)
            $to = preg_replace('/\D/', '', $to);

            // Meta Cloud API endpoint
            $url = "https://graph.facebook.com/v18.0/{$phoneNumberId}/messages";

            $response = Http::withToken($accessToken)
                ->post($url, [
                    'messaging_product' => 'whatsapp',
                    'to' => $to,
                    'type' => 'text',
                    'text' => [
                        'body' => $message,
                    ],
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Message sent successfully',
                    'data' => $response->json(),
                ];
            }

            Log::error('WhatsApp API Error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to send message: ' . $response->body(),
            ];
        } catch (\Exception $e) {
            Log::error('WhatsApp Service Exception', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Verify if the user has WhatsApp credentials configured
     *
     * @param \Webkul\User\Models\User $user
     * @return bool
     */
    public function hasCredentials($user): bool
    {
        return !empty($user->whatsapp_phone_number_id) && !empty($user->whatsapp_access_token);
    }
}
