<?php

namespace App\Http\Controllers;

use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Webkul\Activity\Repositories\ActivityRepository;
use Webkul\Contact\Repositories\PersonRepository;
use Webkul\Lead\Repositories\LeadRepository;

class WhatsAppController extends Controller
{
    /**
     * @var WhatsAppService
     */
    protected $whatsAppService;

    /**
     * @var PersonRepository
     */
    protected $personRepository;

    /**
     * @var LeadRepository
     */
    protected $leadRepository;

    /**
     * @var ActivityRepository
     */
    protected $activityRepository;

    /**
     * @var \App\Repositories\WhatsAppTemplateRepository
     */
    protected $whatsAppTemplateRepository;

    /**
     * Create a new controller instance.
     */
    public function __construct(
        WhatsAppService $whatsAppService,
        PersonRepository $personRepository,
        LeadRepository $leadRepository,
        ActivityRepository $activityRepository,
        \App\Repositories\WhatsAppTemplateRepository $whatsAppTemplateRepository
    ) {
        $this->whatsAppService = $whatsAppService;
        $this->personRepository = $personRepository;
        $this->leadRepository = $leadRepository;
        $this->activityRepository = $activityRepository;
        $this->whatsAppTemplateRepository = $whatsAppTemplateRepository;
    }

    /**
     * Get WhatsApp data for Vue component
     * Returns templates and hasBusinessAPI status
     */
    public function getData()
    {
        $user = auth()->guard('user')->user();

        $templates = $this->whatsAppTemplateRepository
            ->where('user_id', $user->id)
            ->where('status', 'approved')
            ->orderBy('name', 'asc')
            ->get(['id', 'name', 'body']);

        $hasBusinessAPI = !empty($user->whatsapp_phone_number_id) && !empty($user->whatsapp_access_token);

        return response()->json([
            'templates' => $templates,
            'hasBusinessAPI' => $hasBusinessAPI,
        ]);
    }

    /**
     * Send WhatsApp message from Person profile
     */
    public function sendFromPerson(Request $request, $personId)
    {
        $user = auth()->guard('user')->user();

        // Check if user has WhatsApp credentials
        if (!$this->whatsAppService->hasCredentials($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Please configure your WhatsApp credentials in your account settings first.',
            ], 400);
        }

        $request->validate([
            'message' => 'required|string|max:4096',
        ]);

        $person = $this->personRepository->findOrFail($personId);

        // Get phone number from person
        $phoneNumber = $this->extractPhoneNumber($person->contact_numbers);

        if (!$phoneNumber) {
            return response()->json([
                'success' => false,
                'message' => 'No valid phone number found for this person.',
            ], 400);
        }

        // Send message using user's credentials
        $result = $this->whatsAppService->sendMessage(
            $phoneNumber,
            $request->message,
            $user->whatsapp_phone_number_id,
            $user->whatsapp_access_token
        );

        if ($result['success']) {
            // Log this activity in the CRM
            $activity = $this->activityRepository->create([
                'type'          => 'whatsapp',
                'title'         => 'WhatsApp message sent',
                'comment'       => $request->message,
                'is_done'       => 1,
                'user_id'       => $user->id,
                'schedule_from' => now(),
                'schedule_to'   => now(),
                'additional'    => json_encode([
                    'phone_number' => $phoneNumber,
                    'direction'    => 'outbound',
                ]),
            ]);

            // Associate activity with the person
            $activity->persons()->attach($personId);

            return response()->json([
                'success' => true,
                'message' => 'WhatsApp message sent successfully!',
                'data' => $activity->load('persons'),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'],
        ], 500);
    }

    /**
     * Send WhatsApp message from Lead profile
     */
    public function sendFromLead(Request $request, $leadId)
    {
        $user = auth()->guard('user')->user();

        // Check if user has WhatsApp credentials
        if (!$this->whatsAppService->hasCredentials($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Please configure your WhatsApp credentials in your account settings first.',
            ], 400);
        }

        $request->validate([
            'message' => 'required|string|max:4096',
        ]);

        $lead = $this->leadRepository->findOrFail($leadId);

        if (!$lead->person) {
            return response()->json([
                'success' => false,
                'message' => 'This lead has no associated person.',
            ], 400);
        }

        // Get phone number from person
        $phoneNumber = $this->extractPhoneNumber($lead->person->contact_numbers);

        if (!$phoneNumber) {
            return response()->json([
                'success' => false,
                'message' => 'No valid phone number found for this lead\'s contact.',
            ], 400);
        }

        // Send message using user's credentials
        $result = $this->whatsAppService->sendMessage(
            $phoneNumber,
            $request->message,
            $user->whatsapp_phone_number_id,
            $user->whatsapp_access_token
        );

        if ($result['success']) {
            // Log this activity in the CRM
            $activity = $this->activityRepository->create([
                'type'          => 'whatsapp',
                'title'         => 'WhatsApp message sent',
                'comment'       => $request->message,
                'is_done'       => 1,
                'user_id'       => $user->id,
                'schedule_from' => now(),
                'schedule_to'   => now(),
                'additional'    => json_encode([
                    'phone_number' => $phoneNumber,
                    'direction'    => 'outbound',
                ]),
            ]);

            // Associate activity with the lead and person
            $activity->leads()->attach($leadId);
            $activity->persons()->attach($lead->person->id);

            return response()->json([
                'success' => true,
                'message' => 'WhatsApp message sent successfully!',
                'data' => $activity->load('persons', 'leads'),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'],
        ], 500);
    }

    /**
     * Handle WhatsApp webhook (verification and incoming messages)
     */
    public function webhook(Request $request)
    {
        // Handle GET request for webhook verification
        if ($request->isMethod('get')) {
            return $this->verifyWebhook($request);
        }

        // Handle POST request for incoming messages
        try {
            $this->processIncomingMessage($request);

            // Always return 200 OK to acknowledge receipt
            return response()->json(['success' => true], 200);
        } catch (\Exception $e) {
            // Log error but still return 200 to prevent Meta from retrying
            \Log::error('WhatsApp webhook processing error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['success' => true], 200);
        }
    }

    /**
     * Process incoming WhatsApp message from webhook
     */
    private function processIncomingMessage(Request $request)
    {
        $data = $request->all();

        // Validate webhook structure
        if (!isset($data['entry'][0]['changes'][0]['value'])) {
            \Log::warning('Invalid WhatsApp webhook payload structure', ['data' => $data]);
            return;
        }

        $value = $data['entry'][0]['changes'][0]['value'];

        // Only process message events (not status updates)
        if (!isset($value['messages'][0])) {
            return;
        }

        $message = $value['messages'][0];
        $metadata = $value['metadata'] ?? [];
        $contact = $value['contacts'][0] ?? [];

        // Find the user who owns this WhatsApp phone number
        $phoneNumberId = $metadata['phone_number_id'] ?? null;
        if (!$phoneNumberId) {
            \Log::warning('No phone_number_id in WhatsApp webhook', ['metadata' => $metadata]);
            return;
        }

        $user = \Webkul\User\Models\User::where('whatsapp_phone_number_id', $phoneNumberId)->first();
        if (!$user) {
            \Log::warning('No user found for WhatsApp phone_number_id', ['phone_number_id' => $phoneNumberId]);
            return;
        }

        // Extract message details
        $fromPhoneNumber = $message['from'] ?? null;
        $messageText = $message['text']['body'] ?? '';
        $messageType = $message['type'] ?? 'text';
        $timestamp = $message['timestamp'] ?? time();
        $senderName = $contact['profile']['name'] ?? null;

        if (!$fromPhoneNumber || !$messageText) {
            \Log::warning('Missing phone number or message text in webhook', ['message' => $message]);
            return;
        }

        // Only process text messages for now
        if ($messageType !== 'text') {
            \Log::info('Skipping non-text message type', ['type' => $messageType]);
            return;
        }

        // Find or create person by phone number
        $person = $this->findOrCreatePersonByPhone($fromPhoneNumber, $senderName);

        // Create activity for incoming message
        $activity = $this->activityRepository->create([
            'type'          => 'whatsapp',
            'title'         => 'WhatsApp message received',
            'comment'       => $messageText,
            'is_done'       => 1,
            'user_id'       => $user->id,
            'schedule_from' => now(),
            'schedule_to'   => now(),
            'additional'    => json_encode([
                'phone_number' => $fromPhoneNumber,
                'direction'    => 'inbound',
                'message_id'   => $message['id'] ?? null,
                'timestamp'    => $timestamp,
            ]),
        ]);

        // Associate activity with the person
        $activity->persons()->attach($person->id);

        // Find and associate with lead if exists
        $lead = $person->leads()->first();
        if ($lead) {
            $activity->leads()->attach($lead->id);
        }

        // Create notification for the WhatsApp account owner
        $this->whatsAppService->createIncomingMessageNotification(
            $user,
            $person,
            $activity,
            $messageText,
            $fromPhoneNumber
        );

        // If there's a lead with a different assigned user, notify them too
        if ($lead && $lead->user_id && $lead->user_id !== $user->id) {
            $leadUser = \Webkul\User\Models\User::find($lead->user_id);
            if ($leadUser) {
                $this->whatsAppService->createIncomingMessageNotification(
                    $leadUser,
                    $person,
                    $activity,
                    $messageText,
                    $fromPhoneNumber
                );
            }
        }

        \Log::info('WhatsApp message processed', [
            'person_id' => $person->id,
            'activity_id' => $activity->id,
            'from' => $fromPhoneNumber,
        ]);
    }

    /**
     * Find or create a person by phone number
     */
    private function findOrCreatePersonByPhone(string $phoneNumber, ?string $name = null)
    {
        // Normalize phone number for comparison
        $normalizedPhone = $this->normalizePhoneNumber($phoneNumber);

        // Try to find person by phone number in contact_numbers JSON field
        $person = $this->personRepository->getModel()
            ->whereRaw("JSON_SEARCH(contact_numbers, 'one', ?) IS NOT NULL", [$normalizedPhone])
            ->first();

        // Also try searching with original phone number format
        if (!$person && $normalizedPhone !== $phoneNumber) {
            $person = $this->personRepository->getModel()
                ->whereRaw("JSON_SEARCH(contact_numbers, 'one', ?) IS NOT NULL", [$phoneNumber])
                ->first();
        }

        // Create new person if not found
        if (!$person) {
            $person = $this->personRepository->create([
                'name' => $name ?: 'WhatsApp Contact ' . substr($phoneNumber, -4),
                'contact_numbers' => [
                    [
                        'value' => $phoneNumber,
                        'label' => 'whatsapp',
                    ],
                ],
            ]);

            \Log::info('Created new person from WhatsApp', [
                'person_id' => $person->id,
                'phone' => $phoneNumber,
            ]);
        }

        return $person;
    }

    /**
     * Normalize phone number for comparison
     * Removes all non-digit characters
     */
    private function normalizePhoneNumber(string $phoneNumber): string
    {
        return preg_replace('/\D/', '', $phoneNumber);
    }

    /**
     * Verify WhatsApp webhook with Meta
     */
    private function verifyWebhook(Request $request)
    {
        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        $verifyToken = config('services.whatsapp.verify_token');

        // Check if a token and mode were sent
        if ($mode && $token) {
            // Check the mode and token sent are correct
            if ($mode === 'subscribe' && $token === $verifyToken) {
                // Respond with 200 OK and challenge token from the request
                return response($challenge, 200)
                    ->header('Content-Type', 'text/plain');
            }
        }

        // Responds with '403 Forbidden' if verify tokens do not match
        return response()->json(['error' => 'Forbidden'], 403);
    }

    /**
     * Extract phone number from contact_numbers array
     * Prioritizes 'mobile' or 'whatsapp' labeled numbers
     */
    private function extractPhoneNumber($contactNumbers): ?string
    {
        if (!is_array($contactNumbers) || empty($contactNumbers)) {
            return null;
        }

        // Try to find mobile or whatsapp number first
        foreach ($contactNumbers as $contact) {
            if (
                isset($contact['label']) &&
                in_array(strtolower($contact['label']), ['mobile', 'whatsapp']) &&
                !empty($contact['value'])
            ) {
                return $contact['value'];
            }
        }

        // Fallback to first available number
        if (isset($contactNumbers[0]['value'])) {
            return $contactNumbers[0]['value'];
        }

        return null;
    }
}
