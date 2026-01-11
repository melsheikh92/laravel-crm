<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Webkul\Marketplace\Models\Extension;
use Webkul\Marketplace\Models\ExtensionReview;
use Webkul\User\Models\User;

class ExtensionReviewFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ExtensionReview::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'extension_id'         => Extension::factory(),
            'user_id'              => User::factory(),
            'rating'               => $this->faker->numberBetween(1, 5),
            'title'                => $this->faker->sentence(6),
            'review_text'          => $this->faker->paragraph(3),
            'helpful_count'        => $this->faker->numberBetween(0, 100),
            'status'               => 'pending',
            'is_verified_purchase' => false,
        ];
    }

    /**
     * Indicate that the review is approved.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function approved()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'approved',
            ];
        });
    }

    /**
     * Indicate that the review is from a verified purchase.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function verifiedPurchase()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_verified_purchase' => true,
            ];
        });
    }
}
