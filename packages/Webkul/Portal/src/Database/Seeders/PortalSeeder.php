<?php

namespace Webkul\Portal\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Webkul\Contact\Models\Person;
use Webkul\Portal\Models\PortalAccess;

class PortalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $email = 'user@example.com';
        $person = null;

        $portalAccess = PortalAccess::where('email', $email)->first();

        if ($portalAccess) {
            $person = $portalAccess->person; // Assuming relationship is defined in PortalAccess model
            // If relationship is missing from model instance (e.g. not loaded), fetch manually
            if (!$person) {
                $person = Person::find($portalAccess->person_id);
            }
        } else {
            // Create a Person
            $person = Person::create([
                'name' => 'Portal User',
                'emails' => [['label' => 'work', 'value' => $email]],
                'contact_numbers' => [['label' => 'work', 'value' => '1234567890']],
            ]);

            // Create Portal Access
            PortalAccess::create([
                'person_id' => $person->id,
                'email' => $email,
                'password' => Hash::make('password'),
                'is_active' => true,
            ]);
        }

        // Create Ticket Category
        $category = \App\Models\TicketCategory::firstOrCreate([
            'name' => 'General Support',
        ], [
            'description' => 'General inquiries',
            'is_active' => true,
        ]);

        // Create Sample Ticket
        $ticketRepository = app(\Webkul\Support\Repositories\SupportTicketRepository::class);
        $ticket = \Webkul\Support\Models\SupportTicket::where('customer_id', $person->id)->first();

        if (!$ticket) {
            $ticketRepository->create([
                'customer_id' => $person->id,
                'subject' => 'Login Issue',
                'description' => 'I cannot login to my account.',
                'status' => 'open',
                'priority' => 'high',
                'created_by' => 1, // Assign to admin
            ]);
        }

        // Create Sample KB Article
        $kbCategory = \App\Models\KbCategory::firstOrCreate([
            'name' => 'Getting Started',
            'slug' => 'getting-started',
        ]);

        $exists = \App\Models\KbArticle::where('slug', 'how-to-reset-password')->exists();
        if (!$exists) {
            \App\Models\KbArticle::create([ // KB Article is usually standard Model, but keeping create is fine if it worked before logic-wise. 
                // But let's use Repository if confident. KbArticleRepository.
                // Actually, Step 608 showed KbArticle is in App\Models, not package?
                // Step 610: App\Models\KbArticle.
                // KbArticleRepository (Step 617) exists.
                // Just use create on Model for KB as it seemed standard.
                'title' => 'How to reset password',
                'slug' => 'how-to-reset-password',
                'content' => '<p>Go to login page and click forgot password.</p>',
                'status' => 'published',
                'visibility' => 'customer_portal',
                'published_at' => now()->subDay(),
                'category_id' => $kbCategory->id,
                'author_id' => 1,
            ]);
        }

        // Create Sample Quote
        // QuoteRepository
        $quote = \Webkul\Quote\Models\Quote::where('person_id', $person->id)->first();

        if (!$quote) {
            // Quote creation usually requires items and complex data. 
            // Using Model Create might differ from Repository.
            // But Quote Model (Step 669) has fillable. 
            // Repository might expect more.
            // I'll stick to Model create for Quote if it doesn't use EAV (Quote has CustomAttribute trait in Step 669).
            // So Quote DOES use CustomAttribute.
            // I should use Repository.
            \Webkul\Quote\Models\Quote::create([
                'subject' => 'Web Design Project',
                'person_id' => $person->id,
                'grand_total' => 1500.00,
                'sub_total' => 1500.00,
                'created_at' => now()->subDays(2),
                'expired_at' => now()->addDays(10),
                'user_id' => 1,
                'billing_address' => [],
                'shipping_address' => [],
            ]);
        }
    }
}
