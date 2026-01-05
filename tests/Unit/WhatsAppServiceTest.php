<?php

use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Webkul\Collaboration\Services\NotificationService;
use Webkul\User\Models\User;
use Webkul\Contact\Models\Person;
use Webkul\Activity\Models\Activity;

beforeEach(function () {
    $this->notificationService = Mockery::mock(NotificationService::class);
    $this->service = new WhatsAppService($this->notificationService);
});

afterEach(function () {
    Mockery::close();
});

describe('sendMessage', function () {
    it('successfully sends a WhatsApp message', function () {
        Http::fake([
            'graph.facebook.com/*' => Http::response([
                'messaging_product' => 'whatsapp',
                'contacts' => [
                    ['input' => '1234567890', 'wa_id' => '1234567890']
                ],
                'messages' => [
                    ['id' => 'wamid.test123']
                ]
            ], 200)
        ]);

        $result = $this->service->sendMessage(
            '1234567890',
            'Test message',
            'test_phone_number_id',
            'test_access_token'
        );

        expect($result)->toBeArray()
            ->and($result['success'])->toBeTrue()
            ->and($result['message'])->toBe('Message sent successfully')
            ->and($result['data'])->toBeArray()
            ->and($result['data']['messaging_product'])->toBe('whatsapp');

        Http::assertSent(function ($request) {
            return $request->url() === 'https://graph.facebook.com/v18.0/test_phone_number_id/messages'
                && $request['messaging_product'] === 'whatsapp'
                && $request['to'] === '1234567890'
                && $request['type'] === 'text'
                && $request['text']['body'] === 'Test message';
        });
    });

    it('cleans phone number by removing non-digit characters', function () {
        Http::fake([
            'graph.facebook.com/*' => Http::response([
                'messaging_product' => 'whatsapp',
                'messages' => [['id' => 'wamid.test123']]
            ], 200)
        ]);

        $this->service->sendMessage(
            '+1 (234) 567-8900',
            'Test message',
            'test_phone_number_id',
            'test_access_token'
        );

        Http::assertSent(function ($request) {
            return $request['to'] === '12345678900';
        });
    });

    it('handles API error responses', function () {
        Log::shouldReceive('error')
            ->once()
            ->with('WhatsApp API Error', Mockery::type('array'));

        Http::fake([
            'graph.facebook.com/*' => Http::response([
                'error' => [
                    'message' => 'Invalid phone number',
                    'code' => 100
                ]
            ], 400)
        ]);

        $result = $this->service->sendMessage(
            'invalid',
            'Test message',
            'test_phone_number_id',
            'test_access_token'
        );

        expect($result)->toBeArray()
            ->and($result['success'])->toBeFalse()
            ->and($result['message'])->toContain('Failed to send message');
    });

    it('handles exceptions during message sending', function () {
        Log::shouldReceive('error')
            ->once()
            ->with('WhatsApp Service Exception', Mockery::type('array'));

        Http::fake(function () {
            throw new \Exception('Network error');
        });

        $result = $this->service->sendMessage(
            '1234567890',
            'Test message',
            'test_phone_number_id',
            'test_access_token'
        );

        expect($result)->toBeArray()
            ->and($result['success'])->toBeFalse()
            ->and($result['message'])->toContain('Error: Network error');
    });

    it('sends message with correct authorization header', function () {
        Http::fake([
            'graph.facebook.com/*' => Http::response(['messages' => [['id' => 'test']]], 200)
        ]);

        $this->service->sendMessage(
            '1234567890',
            'Test message',
            'test_phone_number_id',
            'my_access_token'
        );

        Http::assertSent(function ($request) {
            return $request->hasHeader('Authorization', 'Bearer my_access_token');
        });
    });
});

describe('sendTemplateMessage', function () {
    it('successfully sends a template message without parameters', function () {
        Http::fake([
            'graph.facebook.com/*' => Http::response([
                'messaging_product' => 'whatsapp',
                'messages' => [['id' => 'wamid.template123']]
            ], 200)
        ]);

        $result = $this->service->sendTemplateMessage(
            '1234567890',
            'welcome_template',
            'en_US',
            [],
            'test_phone_number_id',
            'test_access_token'
        );

        expect($result)->toBeArray()
            ->and($result['success'])->toBeTrue()
            ->and($result['message'])->toBe('Template message sent successfully')
            ->and($result['data'])->toBeArray();

        Http::assertSent(function ($request) {
            return $request->url() === 'https://graph.facebook.com/v18.0/test_phone_number_id/messages'
                && $request['type'] === 'template'
                && $request['template']['name'] === 'welcome_template'
                && $request['template']['language']['code'] === 'en_US'
                && !isset($request['template']['components']);
        });
    });

    it('successfully sends a template message with body parameters', function () {
        Http::fake([
            'graph.facebook.com/*' => Http::response([
                'messaging_product' => 'whatsapp',
                'messages' => [['id' => 'wamid.template123']]
            ], 200)
        ]);

        $parameters = [
            'body' => [
                ['type' => 'text', 'text' => 'John'],
                ['type' => 'text', 'text' => 'Doe']
            ]
        ];

        $result = $this->service->sendTemplateMessage(
            '1234567890',
            'greeting_template',
            'en',
            $parameters,
            'test_phone_number_id',
            'test_access_token'
        );

        expect($result)->toBeArray()
            ->and($result['success'])->toBeTrue();

        Http::assertSent(function ($request) use ($parameters) {
            return isset($request['template']['components'])
                && count($request['template']['components']) === 1
                && $request['template']['components'][0]['type'] === 'body'
                && $request['template']['components'][0]['parameters'] === $parameters['body'];
        });
    });

    it('successfully sends a template message with header and body parameters', function () {
        Http::fake([
            'graph.facebook.com/*' => Http::response([
                'messaging_product' => 'whatsapp',
                'messages' => [['id' => 'wamid.template123']]
            ], 200)
        ]);

        $parameters = [
            'header' => [
                ['type' => 'text', 'text' => 'Special Offer']
            ],
            'body' => [
                ['type' => 'text', 'text' => 'John'],
                ['type' => 'text', 'text' => '50%']
            ]
        ];

        $result = $this->service->sendTemplateMessage(
            '1234567890',
            'promo_template',
            'en_US',
            $parameters,
            'test_phone_number_id',
            'test_access_token'
        );

        expect($result)->toBeArray()
            ->and($result['success'])->toBeTrue();

        Http::assertSent(function ($request) use ($parameters) {
            $components = $request['template']['components'] ?? [];
            return count($components) === 2
                && $components[0]['type'] === 'header'
                && $components[0]['parameters'] === $parameters['header']
                && $components[1]['type'] === 'body'
                && $components[1]['parameters'] === $parameters['body'];
        });
    });

    it('successfully sends a template message with button parameters', function () {
        Http::fake([
            'graph.facebook.com/*' => Http::response([
                'messaging_product' => 'whatsapp',
                'messages' => [['id' => 'wamid.template123']]
            ], 200)
        ]);

        $parameters = [
            'buttons' => [
                [
                    'sub_type' => 'quick_reply',
                    'parameters' => [['type' => 'payload', 'payload' => 'yes']]
                ],
                [
                    'sub_type' => 'quick_reply',
                    'parameters' => [['type' => 'payload', 'payload' => 'no']]
                ]
            ]
        ];

        $result = $this->service->sendTemplateMessage(
            '1234567890',
            'confirm_template',
            'en',
            $parameters,
            'test_phone_number_id',
            'test_access_token'
        );

        expect($result)->toBeArray()
            ->and($result['success'])->toBeTrue();

        Http::assertSent(function ($request) {
            $components = $request['template']['components'] ?? [];
            return count($components) === 2
                && $components[0]['type'] === 'button'
                && $components[0]['sub_type'] === 'quick_reply'
                && $components[0]['index'] === '0'
                && $components[1]['type'] === 'button'
                && $components[1]['sub_type'] === 'quick_reply'
                && $components[1]['index'] === '1';
        });
    });

    it('cleans phone number in template messages', function () {
        Http::fake([
            'graph.facebook.com/*' => Http::response([
                'messaging_product' => 'whatsapp',
                'messages' => [['id' => 'wamid.template123']]
            ], 200)
        ]);

        $this->service->sendTemplateMessage(
            '+1 (555) 123-4567',
            'test_template',
            'en',
            [],
            'test_phone_number_id',
            'test_access_token'
        );

        Http::assertSent(function ($request) {
            return $request['to'] === '15551234567';
        });
    });

    it('handles template API error responses', function () {
        Log::shouldReceive('error')
            ->once()
            ->with('WhatsApp Template API Error', Mockery::type('array'));

        Http::fake([
            'graph.facebook.com/*' => Http::response([
                'error' => [
                    'message' => 'Template not found',
                    'code' => 132000
                ]
            ], 404)
        ]);

        $result = $this->service->sendTemplateMessage(
            '1234567890',
            'nonexistent_template',
            'en',
            [],
            'test_phone_number_id',
            'test_access_token'
        );

        expect($result)->toBeArray()
            ->and($result['success'])->toBeFalse()
            ->and($result['message'])->toContain('Failed to send template message');
    });

    it('handles exceptions during template message sending', function () {
        Log::shouldReceive('error')
            ->once()
            ->with('WhatsApp Template Service Exception', Mockery::type('array'));

        Http::fake(function () {
            throw new \Exception('Connection timeout');
        });

        $result = $this->service->sendTemplateMessage(
            '1234567890',
            'test_template',
            'en',
            [],
            'test_phone_number_id',
            'test_access_token'
        );

        expect($result)->toBeArray()
            ->and($result['success'])->toBeFalse()
            ->and($result['message'])->toContain('Error: Connection timeout');
    });

    it('sends template with correct authorization header', function () {
        Http::fake([
            'graph.facebook.com/*' => Http::response(['messages' => [['id' => 'test']]], 200)
        ]);

        $this->service->sendTemplateMessage(
            '1234567890',
            'test_template',
            'en',
            [],
            'test_phone_number_id',
            'secure_token_123'
        );

        Http::assertSent(function ($request) {
            return $request->hasHeader('Authorization', 'Bearer secure_token_123');
        });
    });

    it('handles all component types together', function () {
        Http::fake([
            'graph.facebook.com/*' => Http::response([
                'messaging_product' => 'whatsapp',
                'messages' => [['id' => 'wamid.template123']]
            ], 200)
        ]);

        $parameters = [
            'header' => [
                ['type' => 'text', 'text' => 'Welcome']
            ],
            'body' => [
                ['type' => 'text', 'text' => 'John'],
                ['type' => 'text', 'text' => 'Premium']
            ],
            'buttons' => [
                [
                    'sub_type' => 'url',
                    'parameters' => [['type' => 'text', 'text' => 'promo-code-123']]
                ]
            ]
        ];

        $result = $this->service->sendTemplateMessage(
            '1234567890',
            'full_template',
            'en_US',
            $parameters,
            'test_phone_number_id',
            'test_access_token'
        );

        expect($result)->toBeArray()
            ->and($result['success'])->toBeTrue();

        Http::assertSent(function ($request) {
            $components = $request['template']['components'] ?? [];
            return count($components) === 3
                && $components[0]['type'] === 'header'
                && $components[1]['type'] === 'body'
                && $components[2]['type'] === 'button';
        });
    });
});

describe('hasCredentials', function () {
    it('returns true when user has both phone number id and access token', function () {
        $user = new User();
        $user->whatsapp_phone_number_id = 'test_phone_id';
        $user->whatsapp_access_token = 'test_token';

        expect($this->service->hasCredentials($user))->toBeTrue();
    });

    it('returns false when user has no phone number id', function () {
        $user = new User();
        $user->whatsapp_phone_number_id = null;
        $user->whatsapp_access_token = 'test_token';

        expect($this->service->hasCredentials($user))->toBeFalse();
    });

    it('returns false when user has no access token', function () {
        $user = new User();
        $user->whatsapp_phone_number_id = 'test_phone_id';
        $user->whatsapp_access_token = null;

        expect($this->service->hasCredentials($user))->toBeFalse();
    });

    it('returns false when user has empty strings for credentials', function () {
        $user = new User();
        $user->whatsapp_phone_number_id = '';
        $user->whatsapp_access_token = '';

        expect($this->service->hasCredentials($user))->toBeFalse();
    });

    it('returns false when user has no credentials set', function () {
        $user = new User();

        expect($this->service->hasCredentials($user))->toBeFalse();
    });
});

describe('createIncomingMessageNotification', function () {
    it('creates a notification with correct data', function () {
        $user = Mockery::mock(User::class);
        $user->id = 1;

        $person = Mockery::mock(Person::class);
        $person->id = 10;
        $person->name = 'John Doe';

        $activity = Mockery::mock(Activity::class);
        $activity->id = 100;

        $messageText = 'Hello, this is a test message';
        $phoneNumber = '+1234567890';

        $this->notificationService
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($data) use ($user, $person, $activity, $messageText, $phoneNumber) {
                return $data['user_id'] === $user->id
                    && $data['type'] === 'whatsapp'
                    && $data['title'] === 'New WhatsApp message from John Doe'
                    && $data['message'] === $messageText
                    && $data['data']['person_id'] === $person->id
                    && $data['data']['activity_id'] === $activity->id
                    && $data['data']['phone_number'] === $phoneNumber
                    && $data['data']['message_length'] === strlen($messageText);
            }))
            ->andReturn((object) ['id' => 1000]);

        $result = $this->service->createIncomingMessageNotification(
            $user,
            $person,
            $activity,
            $messageText,
            $phoneNumber
        );

        expect($result)->toBeObject()
            ->and($result->id)->toBe(1000);
    });

    it('truncates long messages to 100 characters', function () {
        $user = Mockery::mock(User::class);
        $user->id = 1;

        $person = Mockery::mock(Person::class);
        $person->id = 10;
        $person->name = 'Jane Smith';

        $activity = Mockery::mock(Activity::class);
        $activity->id = 100;

        $longMessage = str_repeat('A', 150);
        $phoneNumber = '+1234567890';

        $this->notificationService
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($data) use ($longMessage) {
                return $data['message'] === substr($longMessage, 0, 100) . '...'
                    && $data['data']['message_length'] === 150;
            }))
            ->andReturn((object) ['id' => 1000]);

        $this->service->createIncomingMessageNotification(
            $user,
            $person,
            $activity,
            $longMessage,
            $phoneNumber
        );
    });

    it('does not truncate messages shorter than 100 characters', function () {
        $user = Mockery::mock(User::class);
        $user->id = 1;

        $person = Mockery::mock(Person::class);
        $person->id = 10;
        $person->name = 'Bob Johnson';

        $activity = Mockery::mock(Activity::class);
        $activity->id = 100;

        $shortMessage = 'Short message';
        $phoneNumber = '+1234567890';

        $this->notificationService
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($data) use ($shortMessage) {
                return $data['message'] === $shortMessage
                    && !str_contains($data['message'], '...');
            }))
            ->andReturn((object) ['id' => 1000]);

        $this->service->createIncomingMessageNotification(
            $user,
            $person,
            $activity,
            $shortMessage,
            $phoneNumber
        );
    });

    it('includes all required data fields', function () {
        $user = Mockery::mock(User::class);
        $user->id = 5;

        $person = Mockery::mock(Person::class);
        $person->id = 25;
        $person->name = 'Test Person';

        $activity = Mockery::mock(Activity::class);
        $activity->id = 250;

        $messageText = 'Test notification message';
        $phoneNumber = '+9876543210';

        $this->notificationService
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($data) {
                return isset($data['user_id'])
                    && isset($data['type'])
                    && isset($data['title'])
                    && isset($data['message'])
                    && isset($data['data'])
                    && isset($data['data']['person_id'])
                    && isset($data['data']['activity_id'])
                    && isset($data['data']['phone_number'])
                    && isset($data['data']['message_length']);
            }))
            ->andReturn((object) ['id' => 1000]);

        $this->service->createIncomingMessageNotification(
            $user,
            $person,
            $activity,
            $messageText,
            $phoneNumber
        );
    });
});
