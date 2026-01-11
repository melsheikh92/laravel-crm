<?php

namespace Database\Factories;

use App\Models\SupportTicket;
use Illuminate\Database\Eloquent\Factories\Factory;
use Webkul\Contact\Database\Factories\PersonFactory;

class SupportTicketFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SupportTicket::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        // Use PersonFactory to create a test customer
        $person = PersonFactory::new()->create();

        return [
            'ticket_number'     => SupportTicket::generateTicketNumber(),
            'subject'           => $this->faker->sentence(),
            'description'       => $this->faker->paragraph(),
            'status'            => 'open',
            'priority'          => 'medium',
            'customer_id'       => $person->id,
            'assigned_to'       => null,
            'sla_id'            => null,
            'sla_due_at'        => null,
            'sla_breached'      => false,
            'resolved_at'       => null,
            'closed_at'         => null,
            'closed_by'         => null,
        ];
    }

    /**
     * Indicate that the ticket is assigned
     */
    public function assigned($userId = null)
    {
        return $this->state(function (array $attributes) use ($userId) {
            return [
                'assigned_to' => $userId ?? \App\Models\User::factory()->create()->id,
                'status' => 'assigned',
            ];
        });
    }

    /**
     * Indicate that the ticket is closed
     */
    public function closed()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'closed',
                'closed_at' => now(),
                'closed_by' => \App\Models\User::factory()->create()->id,
            ];
        });
    }

    /**
     * Indicate that the ticket is resolved
     */
    public function resolved()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'resolved',
                'resolved_at' => now(),
            ];
        });
    }
}
