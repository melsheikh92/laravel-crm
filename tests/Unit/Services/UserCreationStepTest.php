<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Services\Onboarding\Steps\UserCreationStep;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use Webkul\Admin\Notifications\User\Create as UserCreatedNotification;
use Webkul\Core\Repositories\CoreConfigRepository;
use Webkul\User\Models\Role;
use Webkul\User\Repositories\RoleRepository;
use Webkul\User\Repositories\UserRepository;

class UserCreationStepTest extends TestCase
{
    use RefreshDatabase;

    protected UserCreationStep $userCreationStep;
    protected CoreConfigRepository $coreConfigRepository;
    protected UserRepository $userRepository;
    protected RoleRepository $roleRepository;
    protected User $testUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Enable onboarding
        Config::set('onboarding.enabled', true);

        // Instantiate repositories and step
        $this->coreConfigRepository = app(CoreConfigRepository::class);
        $this->userRepository = app(UserRepository::class);
        $this->roleRepository = app(RoleRepository::class);

        $this->userCreationStep = new UserCreationStep(
            $this->coreConfigRepository,
            $this->userRepository,
            $this->roleRepository
        );

        // Create a test user (the admin completing onboarding)
        $this->testUser = User::factory()->create();

        // Create test roles
        Role::factory()->create(['name' => 'Administrator', 'description' => 'Admin role']);
        Role::factory()->create(['name' => 'Manager', 'description' => 'Manager role']);
        Role::factory()->create(['name' => 'Sales Representative', 'description' => 'Sales role']);
    }

    /** @test */
    public function it_has_correct_step_configuration()
    {
        $this->assertEquals('user_creation', $this->userCreationStep->getStepId());
        $this->assertEquals('Add Team Members', $this->userCreationStep->getTitle());
        $this->assertTrue($this->userCreationStep->canSkip());
        $this->assertEquals(2, $this->userCreationStep->getEstimatedMinutes());
    }

    /** @test */
    public function it_validates_user_data()
    {
        $validData = [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'role' => 'admin',
            'send_invitation' => true,
        ];

        $this->assertTrue($this->userCreationStep->validate($validData));
    }

    /** @test */
    public function it_fails_validation_without_required_name()
    {
        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $invalidData = [
            'email' => 'test@example.com',
            'role' => 'admin',
        ];

        $this->userCreationStep->validate($invalidData);
    }

    /** @test */
    public function it_fails_validation_without_required_email()
    {
        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $invalidData = [
            'name' => 'John Doe',
            'role' => 'admin',
        ];

        $this->userCreationStep->validate($invalidData);
    }

    /** @test */
    public function it_fails_validation_with_invalid_email()
    {
        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $invalidData = [
            'name' => 'John Doe',
            'email' => 'invalid-email',
            'role' => 'admin',
        ];

        $this->userCreationStep->validate($invalidData);
    }

    /** @test */
    public function it_executes_and_creates_user()
    {
        Mail::fake();

        $userData = [
            'name' => 'Jane Smith',
            'email' => 'jane.smith@example.com',
            'role' => 'Manager',
            'send_invitation' => true,
        ];

        $initialUserCount = User::count();

        $result = $this->userCreationStep->execute($userData, $this->testUser);

        $this->assertTrue($result);

        // Verify user was created
        $this->assertEquals($initialUserCount + 1, User::count());

        $newUser = User::where('email', 'jane.smith@example.com')->first();
        $this->assertNotNull($newUser);
        $this->assertEquals('Jane Smith', $newUser->name);
        $this->assertNotNull($newUser->password);
        $this->assertEquals(1, $newUser->status);

        // Verify invitation email was queued
        Mail::assertQueued(UserCreatedNotification::class, function ($mail) use ($newUser) {
            return $mail->user->id === $newUser->id;
        });
    }

    /** @test */
    public function it_creates_user_with_minimal_data()
    {
        Mail::fake();

        $userData = [
            'name' => 'Minimal User',
            'email' => 'minimal@example.com',
        ];

        $result = $this->userCreationStep->execute($userData, $this->testUser);

        $this->assertTrue($result);

        $newUser = User::where('email', 'minimal@example.com')->first();
        $this->assertNotNull($newUser);
        $this->assertEquals('Minimal User', $newUser->name);
    }

    /** @test */
    public function it_assigns_correct_role_to_user()
    {
        Mail::fake();

        $managerRole = Role::where('name', 'Manager')->first();

        $userData = [
            'name' => 'Manager User',
            'email' => 'manager@example.com',
            'role' => 'Manager',
        ];

        $result = $this->userCreationStep->execute($userData, $this->testUser);

        $this->assertTrue($result);

        $newUser = User::where('email', 'manager@example.com')->first();
        $this->assertEquals($managerRole->id, $newUser->role_id);
    }

    /** @test */
    public function it_sends_invitation_email_when_requested()
    {
        Mail::fake();

        $userData = [
            'name' => 'Invited User',
            'email' => 'invited@example.com',
            'send_invitation' => true,
        ];

        $this->userCreationStep->execute($userData, $this->testUser);

        Mail::assertQueued(UserCreatedNotification::class);
    }

    /** @test */
    public function it_does_not_send_invitation_email_when_not_requested()
    {
        Mail::fake();

        $userData = [
            'name' => 'Not Invited User',
            'email' => 'notinvited@example.com',
            'send_invitation' => false,
        ];

        $this->userCreationStep->execute($userData, $this->testUser);

        Mail::assertNotQueued(UserCreatedNotification::class);
    }

    /** @test */
    public function it_stores_completion_metadata()
    {
        Mail::fake();

        $userData = [
            'name' => 'Meta User',
            'email' => 'meta@example.com',
        ];

        $this->userCreationStep->execute($userData, $this->testUser);

        $newUser = User::where('email', 'meta@example.com')->first();

        // Verify completion metadata
        $this->assertDatabaseHas('core_config', [
            'code' => 'onboarding.user_creation.user_id',
            'value' => (string) $newUser->id,
        ]);

        $this->assertDatabaseHas('core_config', [
            'code' => 'onboarding.user_creation.completed_by',
            'value' => (string) $this->testUser->id,
        ]);

        $completedAt = $this->coreConfigRepository->findOneWhere([
            'code' => 'onboarding.user_creation.completed_at'
        ]);
        $this->assertNotNull($completedAt);
        $this->assertNotEmpty($completedAt->value);
    }

    /** @test */
    public function it_retrieves_default_data_when_previously_completed()
    {
        Mail::fake();

        // Create a user first
        $userData = [
            'name' => 'Existing User',
            'email' => 'existing@example.com',
            'role' => 'Manager',
        ];

        $this->userCreationStep->execute($userData, $this->testUser);

        // Get default data
        $defaultData = $this->userCreationStep->getDefaultData($this->testUser);

        $this->assertArrayHasKey('name', $defaultData);
        $this->assertEquals('Existing User', $defaultData['name']);
        $this->assertEquals('existing@example.com', $defaultData['email']);
        $this->assertArrayHasKey('role', $defaultData);
        $this->assertFalse($defaultData['send_invitation']); // Should not send again by default
    }

    /** @test */
    public function it_returns_empty_default_data_when_not_completed()
    {
        $defaultData = $this->userCreationStep->getDefaultData($this->testUser);

        $this->assertIsArray($defaultData);
        $this->assertEmpty($defaultData);
    }

    /** @test */
    public function it_detects_completion_status()
    {
        Mail::fake();

        // Initially not completed
        $this->assertFalse($this->userCreationStep->hasBeenCompleted($this->testUser));

        // Execute the step
        $userData = [
            'name' => 'Completed User',
            'email' => 'completed@example.com',
        ];

        $this->userCreationStep->execute($userData, $this->testUser);

        // Now should be completed
        $this->assertTrue($this->userCreationStep->hasBeenCompleted($this->testUser));
    }

    /** @test */
    public function it_has_correct_validation_rules()
    {
        $rules = $this->userCreationStep->getValidationRules();

        $this->assertArrayHasKey('name', $rules);
        $this->assertStringContainsString('required', $rules['name']);
        $this->assertArrayHasKey('email', $rules);
        $this->assertStringContainsString('required', $rules['email']);
        $this->assertStringContainsString('email', $rules['email']);
        $this->assertStringContainsString('unique:users,email', $rules['email']);
    }

    /** @test */
    public function it_renders_step_view()
    {
        $view = $this->userCreationStep->render([
            'testData' => 'test value',
        ]);

        $this->assertNotNull($view);
        $this->assertEquals('onboarding.steps.user_creation', $view->name());
    }

    /** @test */
    public function it_handles_rollback_on_error()
    {
        Mail::fake();

        // Create a user with an email that will exist
        User::factory()->create(['email' => 'duplicate@example.com']);

        $initialUserCount = User::count();

        // Try to create a user with duplicate email (should fail)
        $duplicateData = [
            'name' => 'Duplicate User',
            'email' => 'duplicate@example.com',
        ];

        try {
            $this->userCreationStep->execute($duplicateData, $this->testUser);
            $this->fail('Expected exception was not thrown');
        } catch (\Exception $e) {
            // Expected exception
        }

        // Verify user count didn't change (rollback succeeded)
        $this->assertEquals($initialUserCount, User::count());

        // Verify no metadata was stored
        $this->assertDatabaseMissing('core_config', [
            'code' => 'onboarding.user_creation.user_id',
        ]);
    }

    /** @test */
    public function it_uses_default_role_when_role_not_specified()
    {
        Mail::fake();

        $userData = [
            'name' => 'Default Role User',
            'email' => 'defaultrole@example.com',
        ];

        $result = $this->userCreationStep->execute($userData, $this->testUser);

        $this->assertTrue($result);

        $newUser = User::where('email', 'defaultrole@example.com')->first();
        $this->assertNotNull($newUser->role_id);
    }

    /** @test */
    public function it_continues_when_email_fails_but_user_is_created()
    {
        // Mock Mail to throw an exception
        Mail::shouldReceive('queue')
            ->once()
            ->andThrow(new \Exception('SMTP error'));

        $userData = [
            'name' => 'Email Failed User',
            'email' => 'emailfailed@example.com',
            'send_invitation' => true,
        ];

        // Should not throw exception - user creation should succeed
        $result = $this->userCreationStep->execute($userData, $this->testUser);

        $this->assertTrue($result);

        // Verify user was created despite email failure
        $newUser = User::where('email', 'emailfailed@example.com')->first();
        $this->assertNotNull($newUser);
    }
}
