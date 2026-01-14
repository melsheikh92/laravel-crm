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

        // 5. Create Support Tickets
        $customers = \Webkul\Contact\Models\Person::all();
        $users = \Webkul\User\Models\User::all();

        for ($i = 0; $i < 15; $i++) {
            \Webkul\Support\Models\SupportTicket::create([
                'ticket_number' => '#' . $faker->unique()->randomNumber(6),
                'subject' => 'Issue: ' . $faker->sentence(4),
                'description' => $faker->paragraph,
                'status' => $faker->randomElement(['open', 'pending', 'closed', 'resolved']),
                'priority' => $faker->randomElement(['low', 'medium', 'high', 'urgent']),
                'customer_id' => $customers->random()->id,
                'assigned_to' => $users->random()->id,
                'created_at' => $faker->dateTimeBetween('-1 month', 'now'),
            ]);
        }
        $this->command->info('Seeded 15 Support Tickets.');

        // 6. Create Email Templates (Marketing)
        $templates = [];
        for ($i = 0; $i < 5; $i++) {
            $templates[] = \Webkul\Marketing\Models\EmailTemplate::create([
                'name' => 'Template ' . $faker->word,
                'subject' => $faker->sentence,
                'content' => '<p>' . $faker->paragraph . '</p>',
                'is_active' => 1,
                'user_id' => $users->random()->id,
            ]);
        }
        $this->command->info('Seeded 5 Email Templates.');

        // 7. Create Campaigns (Marketing)
        for ($i = 0; $i < 5; $i++) {
            \Webkul\Marketing\Models\Campaign::create([
                'name' => 'Campaign ' . $faker->word,
                'subject' => $faker->sentence,
                'status' => $faker->randomElement([0, 1]), // Active/Inactive
                'marketing_template_id' => $faker->randomElement($templates)->id,
                'marketing_event_id' => null, // Making optional for now
                'spooling' => false,
            ]);
        }
        $this->command->info('Seeded 5 Marketing Campaigns.');

        // 8. Create Activities (Collaboration)
        $leads = \Webkul\Lead\Models\Lead::all();
        for ($i = 0; $i < 20; $i++) {
            $activity = \Webkul\Activity\Models\Activity::create([
                'title' => $faker->randomElement(['Meeting with Client', 'Call', 'Lunch', 'Follow up']),
                'type' => $faker->randomElement(['call', 'meeting', 'lunch']),
                'location' => $faker->address,
                'comment' => $faker->sentence,
                'schedule_from' => $faker->dateTimeBetween('now', '+1 week'),
                'schedule_to' => $faker->dateTimeBetween('+1 week', '+2 weeks'),
                'is_done' => $faker->boolean,
                'user_id' => $users->random()->id,
            ]);

            // Attach to a random lead
            if ($leads->count() > 0) {
                // Using DB insert for pivot to avoid potential proxy/relation issues in raw seeder
                DB::table('lead_activities')->insert([
                    'lead_id' => $leads->random()->id,
                    'activity_id' => $activity->id,
                ]);
            }

            // Attach to a random person
            if ($customers->count() > 0) {
                DB::table('person_activities')->insert([
                    'person_id' => $customers->random()->id,
                    'activity_id' => $activity->id,
                ]);
            }
        }
        $this->command->info('Seeded 20 Activities.');

        // 9. Create Quotes
        $products = \Webkul\Product\Models\Product::all();

        for ($i = 0; $i < 10; $i++) {
            $subTotal = $faker->randomFloat(2, 500, 5000);
            $taxAmount = $subTotal * 0.1;
            $grandTotal = $subTotal + $taxAmount;

            $quote = \Webkul\Quote\Models\Quote::create([
                'subject' => 'Quote: ' . $faker->sentence(3),
                'description' => $faker->paragraph,
                'billing_address' => [
                    'address' => $faker->address,
                    'city' => $faker->city,
                    'state' => $faker->state,
                    'country' => $faker->country,
                    'zip_code' => $faker->postcode,
                ],
                'shipping_address' => [
                    'address' => $faker->address,
                    'city' => $faker->city,
                    'state' => $faker->state,
                    'country' => $faker->country,
                    'zip_code' => $faker->postcode,
                ],
                'discount_percent' => 0,
                'discount_amount' => 0,
                'tax_amount' => $taxAmount,
                'adjustment_amount' => 0,
                'sub_total' => $subTotal,
                'grand_total' => $grandTotal,
                'expired_at' => $faker->dateTimeBetween('+1 week', '+1 month'),
                'user_id' => $users->random()->id,
                'person_id' => $customers->random()->id,
            ]);

            // Add Items to Quote
            if ($products->count() > 0) {
                $randomProducts = $faker->randomElements($products, $faker->numberBetween(1, 4));
                foreach ($randomProducts as $prod) {
                    $qty = $faker->numberBetween(1, 5);
                    // Quote items may use different column names, checking model below if invalid
                    $total = $prod->price * $qty;

                    \Webkul\Quote\Models\QuoteItem::create([
                        'sku' => $prod->sku,
                        'name' => $prod->name,
                        'quantity' => $qty,
                        'price' => $prod->price,
                        'tax_amount' => $total * 0.1,
                        'total' => $total,
                        'product_id' => $prod->id,
                        'quote_id' => $quote->id,
                    ]);
                }
            }

            // Link to a random lead
            if ($leads->count() > 0) {
                DB::table('lead_quotes')->insert([
                    'lead_id' => $leads->random()->id,
                    'quote_id' => $quote->id,
                ]);
            }
        }
        $this->command->info('Seeded 10 Quotes.');
    }
}
