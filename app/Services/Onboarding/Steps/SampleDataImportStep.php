<?php

namespace App\Services\Onboarding\Steps;

use App\Services\Onboarding\AbstractWizardStep;
use Database\Seeders\OnboardingSampleDataSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Webkul\Contact\Models\Organization;
use Webkul\Contact\Models\Person;
use Webkul\Core\Repositories\CoreConfigRepository;
use Webkul\Lead\Models\Lead;
use Webkul\Lead\Models\Pipeline;
use Webkul\Lead\Models\Source;
use Webkul\Lead\Models\Stage;
use Webkul\Lead\Models\Type;
use Webkul\User\Models\User;
use Faker\Factory as Faker;

/**
 * Sample Data Import Step
 *
 * This step allows the administrator to import sample data including companies,
 * contacts, and deals. This helps new users understand how the CRM works before
 * adding their own data. The import includes customization options to choose
 * which types of data to import.
 *
 * @package App\Services\Onboarding\Steps
 */
class SampleDataImportStep extends AbstractWizardStep
{
    /**
     * Core config repository instance.
     *
     * @var CoreConfigRepository
     */
    protected CoreConfigRepository $coreConfigRepository;

    /**
     * Create a new SampleDataImportStep instance.
     *
     * @param CoreConfigRepository $coreConfigRepository
     */
    public function __construct(CoreConfigRepository $coreConfigRepository)
    {
        $this->coreConfigRepository = $coreConfigRepository;

        // Load configuration from onboarding config
        $config = config('onboarding.steps.sample_data', []);
        $validation = config('onboarding.validation.sample_data', []);

        // Set step properties from config
        $this->stepId = 'sample_data';
        $this->title = $config['title'] ?? 'Import Sample Data';
        $this->description = $config['description'] ?? 'Load sample data to explore CRM features';
        $this->icon = $config['icon'] ?? 'database';
        $this->estimatedMinutes = $config['estimated_minutes'] ?? 2;
        $this->skippable = $config['skippable'] ?? true;
        $this->helpText = $config['help_text'] ?? '';
        $this->helpTips = $config['help_tips'] ?? [];
        $this->validationRules = $validation;
        $this->viewPath = 'onboarding.steps.sample_data';
    }

    /**
     * Execute the sample data import step.
     *
     * This method imports sample data based on user preferences. If import_sample_data
     * is true, it will create sample organizations, persons, and leads based on the
     * customization options selected by the user.
     *
     * @param array $data The validated step data
     * @param mixed $user The user completing the step
     * @return bool True if execution was successful
     * @throws \Exception
     */
    public function execute(array $data, $user): bool
    {
        try {
            DB::beginTransaction();

            // Store the user's preferences
            $importRequested = $data['import_sample_data'] ?? false;
            $includeCompanies = $data['include_companies'] ?? true;
            $includeContacts = $data['include_contacts'] ?? true;
            $includeDeals = $data['include_deals'] ?? true;

            // Save preferences to config
            $this->saveConfigValue('onboarding.sample_data.import_requested', $importRequested ? '1' : '0');
            $this->saveConfigValue('onboarding.sample_data.include_companies', $includeCompanies ? '1' : '0');
            $this->saveConfigValue('onboarding.sample_data.include_contacts', $includeContacts ? '1' : '0');
            $this->saveConfigValue('onboarding.sample_data.include_deals', $includeDeals ? '1' : '0');

            // Track what was actually imported
            $importCounts = [
                'organizations' => 0,
                'persons' => 0,
                'leads' => 0,
            ];

            // Only import if requested
            if ($importRequested) {
                Log::info('Starting sample data import', [
                    'step_id' => $this->getStepId(),
                    'user_id' => $this->getUserId($user),
                    'include_companies' => $includeCompanies,
                    'include_contacts' => $includeContacts,
                    'include_deals' => $includeDeals,
                ]);

                // Import the sample data
                $importCounts = $this->importSampleData(
                    $this->getUserId($user),
                    $includeCompanies,
                    $includeContacts,
                    $includeDeals
                );

                // Save import counts
                $this->saveConfigValue('onboarding.sample_data.organizations_imported', (string) $importCounts['organizations']);
                $this->saveConfigValue('onboarding.sample_data.persons_imported', (string) $importCounts['persons']);
                $this->saveConfigValue('onboarding.sample_data.leads_imported', (string) $importCounts['leads']);

                Log::info('Sample data import completed', [
                    'step_id' => $this->getStepId(),
                    'user_id' => $this->getUserId($user),
                    'counts' => $importCounts,
                ]);
            }

            // Store completion timestamp
            $this->saveConfigValue('onboarding.sample_data.completed_at', now()->toDateTimeString());
            $this->saveConfigValue('onboarding.sample_data.completed_by', $this->getUserId($user));

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError('Failed to execute sample data import step', $e, $user);

            throw $e;
        }
    }

    /**
     * Import sample data based on customization options.
     *
     * @param int $userId The user ID to associate with the sample data
     * @param bool $includeCompanies Whether to import sample companies
     * @param bool $includeContacts Whether to import sample contacts
     * @param bool $includeDeals Whether to import sample deals
     * @return array Counts of imported items
     */
    protected function importSampleData(
        int $userId,
        bool $includeCompanies = true,
        bool $includeContacts = true,
        bool $includeDeals = true
    ): array {
        $faker = Faker::create();
        $user = User::find($userId);

        if (!$user) {
            throw new \Exception('User not found for sample data import');
        }

        $counts = [
            'organizations' => 0,
            'persons' => 0,
            'leads' => 0,
        ];

        $organizations = [];
        $persons = [];

        // Import sample organizations
        if ($includeCompanies) {
            $organizations = $this->createSampleOrganizations($faker, $user);
            $counts['organizations'] = count($organizations);
        }

        // Import sample persons (requires organizations)
        if ($includeContacts && !empty($organizations)) {
            $persons = $this->createSamplePersons($faker, $user, $organizations);
            $counts['persons'] = count($persons);
        }

        // Import sample leads (requires persons and pipeline)
        if ($includeDeals && !empty($persons)) {
            $pipeline = $this->ensurePipeline();
            $stages = $pipeline->stages;

            if (!$stages->isEmpty()) {
                $source = $this->ensureSource();
                $type = $this->ensureType();

                $counts['leads'] = $this->createSampleLeads($faker, $user, $persons, $pipeline, $stages, $source, $type);
            }
        }

        return $counts;
    }

    /**
     * Ensure a pipeline exists or create a sample one.
     *
     * @return Pipeline
     */
    protected function ensurePipeline(): Pipeline
    {
        $pipeline = Pipeline::where('is_default', 1)->first();

        if (!$pipeline) {
            $pipeline = Pipeline::create([
                'name' => 'Sales Pipeline',
                'rotten_days' => 30,
                'is_default' => 1,
            ]);

            // Create default stages
            $defaultStages = [
                ['code' => 'new', 'name' => 'New', 'probability' => 10, 'sort_order' => 1],
                ['code' => 'qualified', 'name' => 'Qualified', 'probability' => 25, 'sort_order' => 2],
                ['code' => 'proposal', 'name' => 'Proposal', 'probability' => 50, 'sort_order' => 3],
                ['code' => 'negotiation', 'name' => 'Negotiation', 'probability' => 75, 'sort_order' => 4],
                ['code' => 'won', 'name' => 'Won', 'probability' => 100, 'sort_order' => 5],
                ['code' => 'lost', 'name' => 'Lost', 'probability' => 0, 'sort_order' => 6],
            ];

            foreach ($defaultStages as $stage) {
                Stage::create(array_merge($stage, ['lead_pipeline_id' => $pipeline->id]));
            }
        }

        return $pipeline;
    }

    /**
     * Ensure a source exists or create a default one.
     *
     * @return Source
     */
    protected function ensureSource(): Source
    {
        $source = Source::first();

        if (!$source) {
            $source = Source::create(['name' => 'Website']);
            Source::create(['name' => 'Referral']);
            Source::create(['name' => 'Email Campaign']);
        }

        return $source;
    }

    /**
     * Ensure a type exists or create a default one.
     *
     * @return Type
     */
    protected function ensureType(): Type
    {
        $type = Type::first();

        if (!$type) {
            $type = Type::create(['name' => 'New Business']);
            Type::create(['name' => 'Existing Customer']);
        }

        return $type;
    }

    /**
     * Create sample organizations.
     *
     * @param \Faker\Generator $faker
     * @param User $user
     * @return array
     */
    protected function createSampleOrganizations($faker, User $user): array
    {
        $organizations = [];

        $sampleCompanies = [
            ['name' => 'Acme Corporation', 'city' => 'New York', 'state' => 'NY'],
            ['name' => 'TechStart Solutions', 'city' => 'San Francisco', 'state' => 'CA'],
            ['name' => 'Global Enterprises', 'city' => 'Chicago', 'state' => 'IL'],
            ['name' => 'Innovation Labs', 'city' => 'Austin', 'state' => 'TX'],
            ['name' => 'Blue Ocean Industries', 'city' => 'Seattle', 'state' => 'WA'],
        ];

        foreach ($sampleCompanies as $company) {
            $organization = Organization::create([
                'name' => $company['name'],
                'address' => [
                    'address' => $faker->streetAddress,
                    'city' => $company['city'],
                    'state' => $company['state'],
                    'country' => 'United States',
                    'postcode' => $faker->postcode,
                ],
                'user_id' => $user->id,
            ]);

            $organizations[] = $organization;
        }

        return $organizations;
    }

    /**
     * Create sample persons (contacts).
     *
     * @param \Faker\Generator $faker
     * @param User $user
     * @param array $organizations
     * @return array
     */
    protected function createSamplePersons($faker, User $user, array $organizations): array
    {
        $persons = [];

        $jobTitles = [
            'CEO', 'CTO', 'VP of Sales', 'Director of Marketing',
            'Product Manager', 'Sales Manager', 'Account Manager',
            'Business Development Manager', 'Operations Manager',
        ];

        // Create 2-3 contacts per organization
        foreach ($organizations as $organization) {
            $contactCount = $faker->numberBetween(2, 3);

            for ($i = 0; $i < $contactCount; $i++) {
                $firstName = $faker->firstName;
                $lastName = $faker->lastName;

                $person = Person::create([
                    'name' => $firstName . ' ' . $lastName,
                    'emails' => [
                        [
                            'value' => strtolower($firstName . '.' . $lastName . '@' . str_replace(' ', '', strtolower($organization->name)) . '.com'),
                            'label' => 'work'
                        ]
                    ],
                    'contact_numbers' => [
                        ['value' => $faker->phoneNumber, 'label' => 'work'],
                    ],
                    'job_title' => $faker->randomElement($jobTitles),
                    'organization_id' => $organization->id,
                    'user_id' => $user->id,
                ]);

                $persons[] = $person;
            }
        }

        return $persons;
    }

    /**
     * Create sample leads (deals).
     *
     * @param \Faker\Generator $faker
     * @param User $user
     * @param array $persons
     * @param Pipeline $pipeline
     * @param \Illuminate\Support\Collection $stages
     * @param Source $source
     * @param Type $type
     * @return int Number of leads created
     */
    protected function createSampleLeads($faker, User $user, array $persons, Pipeline $pipeline, $stages, Source $source, Type $type): int
    {
        $dealTitles = [
            'Q1 Enterprise License',
            'Cloud Migration Project',
            'Annual Support Renewal',
            'Custom Integration Package',
            'Professional Services Agreement',
            'Platform Upgrade',
            'Multi-Year Partnership',
            'Consulting Services',
            'Training and Onboarding',
            'Premium Support Package',
        ];

        // Create leads across different stages
        $leadCount = 0;
        foreach ($dealTitles as $index => $title) {
            if ($index >= count($persons)) {
                break;
            }

            $person = $persons[$index];

            // Distribute leads across different stages
            $stageIndex = $index % ($stages->count() - 2); // Exclude won/lost for most
            $stage = $stages[$stageIndex];

            // Occasionally mark some as won or lost
            if ($index % 5 === 0 && $index > 0) {
                $stage = $stages->where('code', 'won')->first() ?? $stage;
            } elseif ($index % 7 === 0 && $index > 0) {
                $stage = $stages->where('code', 'lost')->first() ?? $stage;
            }

            $leadValue = $faker->randomFloat(2, 5000, 100000);
            $expectedCloseDate = $stage->code === 'won' || $stage->code === 'lost'
                ? $faker->dateTimeBetween('-1 month', 'now')
                : $faker->dateTimeBetween('now', '+3 months');

            Lead::create([
                'title' => $title,
                'description' => $faker->paragraph(2),
                'lead_value' => $leadValue,
                'status' => $stage->code === 'lost' ? 0 : 1,
                'lost_reason' => $stage->code === 'lost' ? 'Budget constraints' : null,
                'expected_close_date' => $expectedCloseDate,
                'closed_at' => in_array($stage->code, ['won', 'lost']) ? now() : null,
                'user_id' => $user->id,
                'person_id' => $person->id,
                'lead_source_id' => $source->id,
                'lead_type_id' => $type->id,
                'lead_pipeline_id' => $pipeline->id,
                'lead_pipeline_stage_id' => $stage->id,
            ]);

            $leadCount++;
        }

        return $leadCount;
    }

    /**
     * Save a configuration value to the database.
     *
     * This method updates the config if it exists, or creates a new one.
     *
     * @param string $code The config code/key
     * @param mixed $value The config value
     * @return void
     */
    protected function saveConfigValue(string $code, mixed $value): void
    {
        $existingConfig = $this->coreConfigRepository->findOneWhere(['code' => $code]);

        if ($existingConfig) {
            $this->coreConfigRepository->update([
                'code' => $code,
                'value' => $value,
            ], $existingConfig->id);
        } else {
            $this->coreConfigRepository->create([
                'code' => $code,
                'value' => $value,
            ]);
        }
    }

    /**
     * Get default data for this step.
     *
     * Returns pre-filled data if the sample data import has been completed before.
     *
     * @param mixed $user The current user
     * @return array
     */
    public function getDefaultData($user): array
    {
        $defaultData = [];

        // Define the mapping of config keys to form fields
        $configMapping = [
            'onboarding.sample_data.import_requested' => 'import_sample_data',
            'onboarding.sample_data.include_companies' => 'include_companies',
            'onboarding.sample_data.include_contacts' => 'include_contacts',
            'onboarding.sample_data.include_deals' => 'include_deals',
        ];

        // Retrieve saved values from config
        foreach ($configMapping as $configKey => $field) {
            $config = $this->coreConfigRepository->findOneWhere(['code' => $configKey]);

            if ($config && !empty($config->value)) {
                $defaultData[$field] = $config->value === '1';
            }
        }

        // If no data exists, set defaults from config
        if (empty($defaultData)) {
            $defaultData['import_sample_data'] = false;
            $defaultData['include_companies'] = true;
            $defaultData['include_contacts'] = true;
            $defaultData['include_deals'] = true;
        }

        return $defaultData;
    }

    /**
     * Check if this step has been previously completed.
     *
     * Returns true if sample data configuration has been saved.
     *
     * @param mixed $user The current user
     * @return bool
     */
    public function hasBeenCompleted($user): bool
    {
        $completedAt = $this->coreConfigRepository->findOneWhere([
            'code' => 'onboarding.sample_data.completed_at'
        ]);

        return $completedAt && !empty($completedAt->value);
    }

    /**
     * Handle step completion.
     *
     * Logs the completion and can be extended for additional actions.
     *
     * @param array $data The step data
     * @param mixed $user The user completing the step
     * @return void
     */
    public function onComplete(array $data, $user): void
    {
        parent::onComplete($data, $user);

        $importRequested = $data['import_sample_data'] ?? false;

        Log::info('Sample data import step completed', [
            'import_requested' => $importRequested,
            'include_companies' => $data['include_companies'] ?? true,
            'include_contacts' => $data['include_contacts'] ?? true,
            'include_deals' => $data['include_deals'] ?? true,
        ]);
    }

    /**
     * Handle step skip.
     *
     * This step can be skipped if the administrator doesn't want sample data.
     *
     * @param mixed $user The user skipping the step
     * @return void
     */
    public function onSkip($user): void
    {
        parent::onSkip($user);

        Log::info('Sample data import step skipped', [
            'user_id' => $this->getUserId($user),
        ]);
    }
}
