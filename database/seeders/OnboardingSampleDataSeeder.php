<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;
use Webkul\Contact\Models\Organization;
use Webkul\Contact\Models\Person;
use Webkul\Lead\Models\Lead;
use Webkul\Lead\Models\Pipeline;
use Webkul\Lead\Models\Stage;
use Webkul\Lead\Models\Source;
use Webkul\Lead\Models\Type;
use Webkul\User\Models\User;

class OnboardingSampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();

        // Get or create required dependencies
        $user = User::first();
        if (!$user) {
            $this->command->error('No user found. Please create a user first.');
            return;
        }

        $pipeline = $this->ensurePipeline();
        $stages = $pipeline->stages;

        if ($stages->isEmpty()) {
            $this->command->error('No pipeline stages found. Please set up pipeline stages first.');
            return;
        }

        $source = $this->ensureSource();
        $type = $this->ensureType();

        // Create sample organizations
        $organizations = $this->createSampleOrganizations($faker, $user);
        $this->command->info('Created ' . count($organizations) . ' sample organizations.');

        // Create sample persons (contacts)
        $persons = $this->createSamplePersons($faker, $user, $organizations);
        $this->command->info('Created ' . count($persons) . ' sample persons.');

        // Create sample leads (deals)
        $this->createSampleLeads($faker, $user, $persons, $pipeline, $stages, $source, $type);
        $this->command->info('Created sample leads.');

        $this->command->info('Onboarding sample data seeded successfully!');
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
     * @return void
     */
    protected function createSampleLeads($faker, User $user, array $persons, Pipeline $pipeline, $stages, Source $source, Type $type): void
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

            $lead = Lead::create([
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

        $this->command->info("Created {$leadCount} sample leads distributed across pipeline stages.");
    }
}
