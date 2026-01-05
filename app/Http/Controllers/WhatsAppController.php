<?php

namespace App\Http\Controllers;

use App\Services\WhatsAppService;
use Illuminate\Http\Request;
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
     * Create a new controller instance.
     */
    public function __construct(
        WhatsAppService $whatsAppService,
        PersonRepository $personRepository,
        LeadRepository $leadRepository
    ) {
        $this->whatsAppService = $whatsAppService;
        $this->personRepository = $personRepository;
        $this->leadRepository = $leadRepository;
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
            // TODO: Log this activity in the CRM
            return response()->json([
                'success' => true,
                'message' => 'WhatsApp message sent successfully!',
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
            // TODO: Log this activity in the CRM
            return response()->json([
                'success' => true,
                'message' => 'WhatsApp message sent successfully!',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'],
        ], 500);
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
