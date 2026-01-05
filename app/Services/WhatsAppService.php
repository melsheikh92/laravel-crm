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
     * Send a WhatsApp template message using the Meta Cloud API
     *
     * @param string $to The recipient's phone number (with country code, no + sign)
     * @param string $templateName The name of the template to send
     * @param string $languageCode The language code of the template (e.g., 'en_US', 'en')
     * @param array $parameters Template parameters organized by component type
     *   Example: [
     *     'header' => [['type' => 'text', 'text' => 'John']],
     *     'body' => [['type' => 'text', 'text' => 'Doe'], ['type' => 'text', 'text' => '123']]
     *   ]
     * @param string $phoneNumberId The WhatsApp Phone Number ID from Meta
     * @param string $accessToken The access token for authentication
     * @return array Response with success status and message
     */
    public function sendTemplateMessage(
        string $to,
        string $templateName,
        string $languageCode,
        array $parameters,
        string $phoneNumberId,
        string $accessToken
    ): array {
        try {
            // Clean the phone number (remove any non-digit characters)
            $to = preg_replace('/\D/', '', $to);

            // Meta Cloud API endpoint
            $url = "https://graph.facebook.com/v18.0/{$phoneNumberId}/messages";

            // Build components array for template parameters
            $components = [];

            // Add header parameters if provided
            if (!empty($parameters['header'])) {
                $components[] = [
                    'type' => 'header',
                    'parameters' => $parameters['header'],
                ];
            }

            // Add body parameters if provided
            if (!empty($parameters['body'])) {
                $components[] = [
                    'type' => 'body',
                    'parameters' => $parameters['body'],
                ];
            }

            // Add button parameters if provided
            if (!empty($parameters['buttons'])) {
                foreach ($parameters['buttons'] as $index => $buttonParams) {
                    $components[] = [
                        'type' => 'button',
                        'sub_type' => $buttonParams['sub_type'] ?? 'quick_reply',
                        'index' => (string) $index,
                        'parameters' => $buttonParams['parameters'] ?? [],
                    ];
                }
            }

            $payload = [
                'messaging_product' => 'whatsapp',
                'to' => $to,
                'type' => 'template',
                'template' => [
                    'name' => $templateName,
                    'language' => [
                        'code' => $languageCode,
                    ],
                ],
            ];

            // Only add components if we have any
            if (!empty($components)) {
                $payload['template']['components'] = $components;
            }

            $response = Http::withToken($accessToken)
                ->post($url, $payload);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Template message sent successfully',
                    'data' => $response->json(),
                ];
            }

            Log::error('WhatsApp Template API Error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to send template message: ' . $response->body(),
            ];
        } catch (\Exception $e) {
            Log::error('WhatsApp Template Service Exception', [
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
