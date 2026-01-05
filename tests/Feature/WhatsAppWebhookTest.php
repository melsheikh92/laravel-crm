<?php

use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Webkul\Activity\Models\Activity;
use Webkul\Collaboration\Services\NotificationService;
use Webkul\Contact\Models\Person;
use Webkul\Lead\Models\Lead;
use Webkul\User\Models\User;

beforeEach(function () {
    // Set up test verify token
    Config::set('services.whatsapp.verify_token', 'test_verify_token_123');

    // Prevent actual logging during tests
    Log::shouldReceive('info')->andReturn(null);
    Log::shouldReceive('warning')->andReturn(null);
    Log::shouldReceive('error')->andReturn(null);
});

describe('Webhook Verification (GET)', function () {
    it('successfully verifies webhook with correct token', function () {
        $response = test()->get('/api/whatsapp/webhook', [
            'hub_mode' => 'subscribe',
            'hub_verify_token' => 'test_verify_token_123',
            'hub_challenge' => 'test_challenge_string',
        ]);

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8');

        expect($response->getContent())->toBe('test_challenge_string');
    });

    it('returns 403 when verify token does not match', function () {
        $response = test()->get('/api/whatsapp/webhook', [
            'hub_mode' => 'subscribe',
            'hub_verify_token' => 'wrong_token',
            'hub_challenge' => 'test_challenge_string',
        ]);

        $response->assertStatus(403)
            ->assertJson(['error' => 'Forbidden']);
    });

    it('returns 403 when hub_mode is not subscribe', function () {
        $response = test()->get('/api/whatsapp/webhook', [
            'hub_mode' => 'invalid_mode',
            'hub_verify_token' => 'test_verify_token_123',
            'hub_challenge' => 'test_challenge_string',
        ]);

        $response->assertStatus(403)
            ->assertJson(['error' => 'Forbidden']);
    });

    it('returns 403 when hub_mode is missing', function () {
        $response = test()->get('/api/whatsapp/webhook', [
            'hub_verify_token' => 'test_verify_token_123',
            'hub_challenge' => 'test_challenge_string',
        ]);

        $response->assertStatus(403)
            ->assertJson(['error' => 'Forbidden']);
    });

    it('returns 403 when hub_verify_token is missing', function () {
        $response = test()->get('/api/whatsapp/webhook', [
            'hub_mode' => 'subscribe',
            'hub_challenge' => 'test_challenge_string',
        ]);

        $response->assertStatus(403)
            ->assertJson(['error' => 'Forbidden']);
    });
});

describe('Incoming Message Processing (POST)', function () {
    beforeEach(function () {
        // Create a test user with WhatsApp credentials
        $this->user = User::factory()->create([
            'whatsapp_phone_number_id' => 'test_phone_number_id_123',
            'whatsapp_access_token' => 'test_access_token_123',
        ]);

        // Mock NotificationService to avoid actual notification creation
        $this->notificationService = Mockery::mock(NotificationService::class);
        $this->notificationService->shouldReceive('create')->andReturn((object) ['id' => 1]);
        app()->instance(NotificationService::class, $this->notificationService);
    });

    it('successfully processes incoming text message from existing person', function () {
        // Create a person with a phone number
        $person = Person::factory()->create([
            'name' => 'John Doe',
            'contact_numbers' => [
                ['value' => '1234567890', 'label' => 'mobile'],
            ],
        ]);

        $payload = [
            'entry' => [
                [
                    'changes' => [
                        [
                            'value' => [
                                'metadata' => [
                                    'phone_number_id' => 'test_phone_number_id_123',
                                ],
                                'messages' => [
                                    [
                                        'id' => 'wamid.test123',
                                        'from' => '1234567890',
                                        'type' => 'text',
                                        'timestamp' => '1234567890',
                                        'text' => [
                                            'body' => 'Hello, this is a test message',
                                        ],
                                    ],
                                ],
                                'contacts' => [
                                    [
                                        'profile' => [
                                            'name' => 'John Doe',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $response = test()->postJson('/api/whatsapp/webhook', $payload);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        // Verify activity was created
        $activity = Activity::where('type', 'whatsapp')
            ->where('user_id', $this->user->id)
            ->latest()
            ->first();

        expect($activity)->not->toBeNull()
            ->and($activity->title)->toBe('WhatsApp message received')
            ->and($activity->comment)->toBe('Hello, this is a test message')
            ->and($activity->is_done)->toBe(1);

        $additional = json_decode($activity->additional, true);
        expect($additional)->toBeArray()
            ->and($additional['phone_number'])->toBe('1234567890')
            ->and($additional['direction'])->toBe('inbound')
            ->and($additional['message_id'])->toBe('wamid.test123');

        // Verify activity is associated with person
        expect($activity->persons()->where('persons.id', $person->id)->exists())->toBeTrue();
    });

    it('creates new person for unknown phone number', function () {
        $initialPersonCount = Person::count();

        $payload = [
            'entry' => [
                [
                    'changes' => [
                        [
                            'value' => [
                                'metadata' => [
                                    'phone_number_id' => 'test_phone_number_id_123',
                                ],
                                'messages' => [
                                    [
                                        'id' => 'wamid.new123',
                                        'from' => '9876543210',
                                        'type' => 'text',
                                        'timestamp' => '1234567890',
                                        'text' => [
                                            'body' => 'Message from new contact',
                                        ],
                                    ],
                                ],
                                'contacts' => [
                                    [
                                        'profile' => [
                                            'name' => 'Jane Smith',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $response = test()->postJson('/api/whatsapp/webhook', $payload);

        $response->assertStatus(200);

        // Verify new person was created
        expect(Person::count())->toBe($initialPersonCount + 1);

        $newPerson = Person::latest()->first();
        expect($newPerson->name)->toBe('Jane Smith')
            ->and($newPerson->contact_numbers)->toBeArray()
            ->and($newPerson->contact_numbers[0]['value'])->toBe('9876543210')
            ->and($newPerson->contact_numbers[0]['label'])->toBe('whatsapp');

        // Verify activity was created and associated with new person
        $activity = Activity::where('type', 'whatsapp')
            ->where('user_id', $this->user->id)
            ->latest()
            ->first();

        expect($activity)->not->toBeNull()
            ->and($activity->persons()->where('persons.id', $newPerson->id)->exists())->toBeTrue();
    });

    it('creates person with default name when sender name not provided', function () {
        $payload = [
            'entry' => [
                [
                    'changes' => [
                        [
                            'value' => [
                                'metadata' => [
                                    'phone_number_id' => 'test_phone_number_id_123',
                                ],
                                'messages' => [
                                    [
                                        'id' => 'wamid.new456',
                                        'from' => '5555551234',
                                        'type' => 'text',
                                        'timestamp' => '1234567890',
                                        'text' => [
                                            'body' => 'Message without sender name',
                                        ],
                                    ],
                                ],
                                'contacts' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $response = test()->postJson('/api/whatsapp/webhook', $payload);

        $response->assertStatus(200);

        $newPerson = Person::latest()->first();
        expect($newPerson->name)->toBe('WhatsApp Contact 1234');
    });

    it('associates activity with lead when person has a lead', function () {
        $person = Person::factory()->create([
            'name' => 'Lead Person',
            'contact_numbers' => [
                ['value' => '1111222233', 'label' => 'mobile'],
            ],
        ]);

        $lead = Lead::factory()->create([
            'person_id' => $person->id,
            'user_id' => $this->user->id,
        ]);

        $payload = [
            'entry' => [
                [
                    'changes' => [
                        [
                            'value' => [
                                'metadata' => [
                                    'phone_number_id' => 'test_phone_number_id_123',
                                ],
                                'messages' => [
                                    [
                                        'id' => 'wamid.lead123',
                                        'from' => '1111222233',
                                        'type' => 'text',
                                        'timestamp' => '1234567890',
                                        'text' => [
                                            'body' => 'Message for lead',
                                        ],
                                    ],
                                ],
                                'contacts' => [
                                    [
                                        'profile' => [
                                            'name' => 'Lead Person',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $response = test()->postJson('/api/whatsapp/webhook', $payload);

        $response->assertStatus(200);

        // Verify activity is associated with both person and lead
        $activity = Activity::where('type', 'whatsapp')
            ->where('user_id', $this->user->id)
            ->latest()
            ->first();

        expect($activity)->not->toBeNull()
            ->and($activity->persons()->where('persons.id', $person->id)->exists())->toBeTrue()
            ->and($activity->leads()->where('leads.id', $lead->id)->exists())->toBeTrue();
    });

    it('creates notification for WhatsApp account owner', function () {
        $person = Person::factory()->create([
            'name' => 'Notification Test',
            'contact_numbers' => [
                ['value' => '9998887777', 'label' => 'mobile'],
            ],
        ]);

        // Expect notification to be created
        $this->notificationService
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($data) {
                return $data['user_id'] === $this->user->id
                    && $data['type'] === 'whatsapp'
                    && str_contains($data['title'], 'Notification Test')
                    && $data['message'] === 'Test notification message'
                    && isset($data['data']['person_id'])
                    && isset($data['data']['activity_id'])
                    && isset($data['data']['phone_number'])
                    && isset($data['data']['message_length']);
            }))
            ->andReturn((object) ['id' => 1]);

        $payload = [
            'entry' => [
                [
                    'changes' => [
                        [
                            'value' => [
                                'metadata' => [
                                    'phone_number_id' => 'test_phone_number_id_123',
                                ],
                                'messages' => [
                                    [
                                        'id' => 'wamid.notif123',
                                        'from' => '9998887777',
                                        'type' => 'text',
                                        'timestamp' => '1234567890',
                                        'text' => [
                                            'body' => 'Test notification message',
                                        ],
                                    ],
                                ],
                                'contacts' => [
                                    [
                                        'profile' => [
                                            'name' => 'Notification Test',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $response = test()->postJson('/api/whatsapp/webhook', $payload);

        $response->assertStatus(200);
    });

    it('creates notification for lead owner when different from WhatsApp account owner', function () {
        $leadOwner = User::factory()->create([
            'email' => 'leadowner@example.com',
        ]);

        $person = Person::factory()->create([
            'name' => 'Lead Notification Test',
            'contact_numbers' => [
                ['value' => '6665554444', 'label' => 'mobile'],
            ],
        ]);

        $lead = Lead::factory()->create([
            'person_id' => $person->id,
            'user_id' => $leadOwner->id, // Different from WhatsApp account owner
        ]);

        // Expect two notifications: one for WhatsApp owner, one for lead owner
        $this->notificationService
            ->shouldReceive('create')
            ->twice()
            ->andReturn((object) ['id' => 1]);

        $payload = [
            'entry' => [
                [
                    'changes' => [
                        [
                            'value' => [
                                'metadata' => [
                                    'phone_number_id' => 'test_phone_number_id_123',
                                ],
                                'messages' => [
                                    [
                                        'id' => 'wamid.leadnotif123',
                                        'from' => '6665554444',
                                        'type' => 'text',
                                        'timestamp' => '1234567890',
                                        'text' => [
                                            'body' => 'Message for lead owner',
                                        ],
                                    ],
                                ],
                                'contacts' => [
                                    [
                                        'profile' => [
                                            'name' => 'Lead Notification Test',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $response = test()->postJson('/api/whatsapp/webhook', $payload);

        $response->assertStatus(200);
    });

    it('ignores webhook with invalid payload structure', function () {
        $payload = [
            'invalid' => 'structure',
        ];

        $response = test()->postJson('/api/whatsapp/webhook', $payload);

        // Should still return 200 to acknowledge receipt
        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        // No activity should be created
        $activity = Activity::where('type', 'whatsapp')
            ->where('user_id', $this->user->id)
            ->latest()
            ->first();

        expect($activity)->toBeNull();
    });

    it('ignores webhook without messages array', function () {
        $payload = [
            'entry' => [
                [
                    'changes' => [
                        [
                            'value' => [
                                'metadata' => [
                                    'phone_number_id' => 'test_phone_number_id_123',
                                ],
                                'statuses' => [ // Status update, not message
                                    [
                                        'id' => 'wamid.status123',
                                        'status' => 'sent',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $response = test()->postJson('/api/whatsapp/webhook', $payload);

        $response->assertStatus(200);

        // No activity should be created for status updates
        $activity = Activity::where('type', 'whatsapp')
            ->where('user_id', $this->user->id)
            ->latest()
            ->first();

        expect($activity)->toBeNull();
    });

    it('ignores webhook when user not found for phone_number_id', function () {
        $payload = [
            'entry' => [
                [
                    'changes' => [
                        [
                            'value' => [
                                'metadata' => [
                                    'phone_number_id' => 'nonexistent_phone_number_id',
                                ],
                                'messages' => [
                                    [
                                        'id' => 'wamid.nouser123',
                                        'from' => '1234567890',
                                        'type' => 'text',
                                        'timestamp' => '1234567890',
                                        'text' => [
                                            'body' => 'Message for nonexistent user',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $response = test()->postJson('/api/whatsapp/webhook', $payload);

        $response->assertStatus(200);

        // No activity should be created
        $activity = Activity::where('type', 'whatsapp')
            ->where('comment', 'Message for nonexistent user')
            ->first();

        expect($activity)->toBeNull();
    });

    it('ignores non-text message types', function () {
        $person = Person::factory()->create([
            'name' => 'Image Sender',
            'contact_numbers' => [
                ['value' => '3334445555', 'label' => 'mobile'],
            ],
        ]);

        $payload = [
            'entry' => [
                [
                    'changes' => [
                        [
                            'value' => [
                                'metadata' => [
                                    'phone_number_id' => 'test_phone_number_id_123',
                                ],
                                'messages' => [
                                    [
                                        'id' => 'wamid.image123',
                                        'from' => '3334445555',
                                        'type' => 'image', // Non-text type
                                        'timestamp' => '1234567890',
                                        'image' => [
                                            'id' => 'image_id_123',
                                        ],
                                    ],
                                ],
                                'contacts' => [
                                    [
                                        'profile' => [
                                            'name' => 'Image Sender',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $response = test()->postJson('/api/whatsapp/webhook', $payload);

        $response->assertStatus(200);

        // No activity should be created for non-text messages
        $activity = Activity::where('type', 'whatsapp')
            ->where('user_id', $this->user->id)
            ->latest()
            ->first();

        expect($activity)->toBeNull();
    });

    it('handles missing phone number or message text gracefully', function () {
        $payload = [
            'entry' => [
                [
                    'changes' => [
                        [
                            'value' => [
                                'metadata' => [
                                    'phone_number_id' => 'test_phone_number_id_123',
                                ],
                                'messages' => [
                                    [
                                        'id' => 'wamid.incomplete123',
                                        'type' => 'text',
                                        'timestamp' => '1234567890',
                                        // Missing 'from' and 'text' fields
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $response = test()->postJson('/api/whatsapp/webhook', $payload);

        $response->assertStatus(200);

        // No activity should be created for incomplete messages
        $activity = Activity::where('type', 'whatsapp')
            ->where('user_id', $this->user->id)
            ->latest()
            ->first();

        expect($activity)->toBeNull();
    });

    it('normalizes phone numbers for matching existing persons', function () {
        // Create person with formatted phone number
        $person = Person::factory()->create([
            'name' => 'Formatted Number Person',
            'contact_numbers' => [
                ['value' => '+1 (555) 123-4567', 'label' => 'mobile'],
            ],
        ]);

        $payload = [
            'entry' => [
                [
                    'changes' => [
                        [
                            'value' => [
                                'metadata' => [
                                    'phone_number_id' => 'test_phone_number_id_123',
                                ],
                                'messages' => [
                                    [
                                        'id' => 'wamid.normalized123',
                                        'from' => '15551234567', // Same number, different format
                                        'type' => 'text',
                                        'timestamp' => '1234567890',
                                        'text' => [
                                            'body' => 'Test normalization',
                                        ],
                                    ],
                                ],
                                'contacts' => [
                                    [
                                        'profile' => [
                                            'name' => 'Formatted Number Person',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $initialPersonCount = Person::count();

        $response = test()->postJson('/api/whatsapp/webhook', $payload);

        $response->assertStatus(200);

        // Should not create a new person (phone number should match)
        expect(Person::count())->toBe($initialPersonCount);

        // Activity should be associated with existing person
        $activity = Activity::where('type', 'whatsapp')
            ->where('comment', 'Test normalization')
            ->latest()
            ->first();

        expect($activity)->not->toBeNull()
            ->and($activity->persons()->where('persons.id', $person->id)->exists())->toBeTrue();
    });

    it('always returns 200 even when exceptions occur', function () {
        // Send a payload that would cause an exception (e.g., missing metadata)
        $payload = [
            'entry' => [
                [
                    'changes' => [
                        [
                            'value' => [
                                // Missing metadata
                                'messages' => [
                                    [
                                        'id' => 'wamid.error123',
                                        'from' => '1234567890',
                                        'type' => 'text',
                                        'text' => [
                                            'body' => 'Error test',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $response = test()->postJson('/api/whatsapp/webhook', $payload);

        // Should still return 200 to prevent Meta from retrying
        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    });
});
