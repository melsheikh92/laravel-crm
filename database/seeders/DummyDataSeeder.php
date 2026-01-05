<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;
use Webkul\Contact\Models\Person;
use Webkul\Product\Models\Product;
use Webkul\Lead\Models\Lead;
use Webkul\Lead\Models\Pipeline;
use Webkul\Lead\Models\Stage;
use Webkul\Lead\Models\Source;
use Webkul\Lead\Models\Type;
use Webkul\User\Models\User;

class DummyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();

        // 1. Create Persons
        $persons = [];
        for ($i = 0; $i < 20; $i++) {
            $person = Person::create([
                'name' => $faker->name,
                'emails' => [['value' => $faker->unique()->safeEmail, 'label' => 'work']],
                'contact_numbers' => [['value' => $faker->phoneNumber, 'label' => 'work']],
                'organization_id' => null,
            ]);
            $persons[] = $person;
        }
        $this->command->info('Seeded 20 Persons.');

        // 2. Create Products
        $products = [];
        for ($i = 0; $i < 10; $i++) {
            $product = Product::create([
                'name' => $faker->words(3, true),
                'sku' => $faker->unique()->bothify('PROD-####'),
                'description' => $faker->sentence,
                'quantity' => $faker->numberBetween(10, 100),
                'price' => $faker->randomFloat(2, 100, 1000),
            ]);
            $products[] = $product;
        }
        $this->command->info('Seeded 10 Products.');

        // 3. Get Dependencies for Leads
        $pipeline = Pipeline::first();
        if (!$pipeline) {
            $this->command->error('No Pipeline found. Please run core seeders first.');
            return;
        }

        $stages = $pipeline->stages;
        $source = Source::first();
        $type = Type::first();
        $user = User::first();

        // 4. Create Leads
        for ($i = 0; $i < 30; $i++) {
            $person = $faker->randomElement($persons);
            $stage = $stages->random();

            $lead = Lead::create([
                'title' => $faker->sentence(3),
                'description' => $faker->paragraph,
                'lead_value' => $faker->randomFloat(2, 1000, 50000),
                'status' => 1, // Active
                'lost_reason' => null,
                'expected_close_date' => $faker->dateTimeBetween('now', '+2 months'),
                'user_id' => $user->id,
                'person_id' => $person->id,
                'lead_source_id' => $source ? $source->id : null,
                'lead_type_id' => $type ? $type->id : null,
                'lead_pipeline_id' => $pipeline->id,
                'lead_pipeline_stage_id' => $stage->id,
            ]);

            // Attach products to lead (randomly 1-3 products)
            $randomProducts = $faker->randomElements($products, $faker->numberBetween(1, 3));
            foreach ($randomProducts as $prod) {
                // Determine pivot values based on implementation (assuming simplified attach for now)
                // Often CRM leads link to products via a separate table like lead_products with qty/price/amount
                // We'll skip complex pivot data for simplicity unless required.
                // Assuming standard relationship:
                DB::table('lead_products')->insert([
                    'lead_id' => $lead->id,
                    'product_id' => $prod->id,
                    'quantity' => 1,
                    'price' => $prod->price,
                    'amount' => $prod->price,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
        $this->command->info('Seeded 30 Leads.');
    }
}
