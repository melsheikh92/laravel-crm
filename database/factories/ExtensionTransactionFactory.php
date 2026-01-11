<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Webkul\Marketplace\Models\Extension;
use Webkul\Marketplace\Models\ExtensionTransaction;
use Webkul\User\Models\User;

class ExtensionTransactionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ExtensionTransaction::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $amount = $this->faker->randomFloat(2, 9.99, 99.99);

        return [
            'extension_id'    => Extension::factory(),
            'user_id'         => User::factory(),
            'amount'          => $amount,
            'commission'      => round($amount * 0.30, 2),
            'developer_share' => round($amount * 0.70, 2),
            'currency'        => 'USD',
            'status'          => 'pending',
            'payment_method'  => 'stripe',
            'payment_gateway_transaction_id' => null,
            'payment_gateway_response'       => null,
            'refund_amount'   => null,
            'refund_reason'   => null,
            'refunded_at'     => null,
        ];
    }

    /**
     * Indicate that the transaction is completed.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function completed()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'completed',
                'payment_gateway_transaction_id' => 'txn_' . $this->faker->uuid(),
            ];
        });
    }

    /**
     * Indicate that the transaction is pending.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function pending()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'pending',
            ];
        });
    }

    /**
     * Indicate that the transaction is refunded.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function refunded()
    {
        return $this->state(function (array $attributes) {
            return [
                'status'        => 'refunded',
                'refund_amount' => $attributes['amount'],
                'refund_reason' => $this->faker->sentence(),
                'refunded_at'   => now(),
            ];
        });
    }

    /**
     * Indicate that the transaction is cancelled.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function cancelled()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'cancelled',
            ];
        });
    }
}
