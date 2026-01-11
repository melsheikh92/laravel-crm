<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Webkul\Territory\Models\Territory;
use Webkul\User\Models\User;

class TerritoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Territory::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $types = ['geographic', 'account-based'];
        $type = $this->faker->randomElement($types);

        return [
            'name'        => $this->faker->city() . ' ' . $this->faker->randomElement(['Region', 'Territory', 'Zone', 'District']),
            'code'        => strtoupper($this->faker->unique()->lexify('???-###')),
            'description' => $this->faker->optional(0.7)->sentence(),
            'type'        => $type,
            'parent_id'   => null,
            'status'      => $this->faker->randomElement(['active', 'inactive']),
            'boundaries'  => $type === 'geographic' ? $this->generateBoundaries() : null,
            'user_id'     => function () {
                return User::inRandomOrder()->first()->id ?? 1;
            },
        ];
    }

    /**
     * Indicate that the territory is active.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function active()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'active',
            ];
        });
    }

    /**
     * Indicate that the territory is inactive.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function inactive()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'inactive',
            ];
        });
    }

    /**
     * Indicate that the territory is geographic type.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function geographic()
    {
        return $this->state(function (array $attributes) {
            return [
                'type'       => 'geographic',
                'boundaries' => $this->generateBoundaries(),
            ];
        });
    }

    /**
     * Indicate that the territory is account-based type.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function accountBased()
    {
        return $this->state(function (array $attributes) {
            return [
                'type'       => 'account-based',
                'boundaries' => null,
            ];
        });
    }

    /**
     * Indicate that the territory has a parent.
     *
     * @param  int  $parentId
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function withParent($parentId)
    {
        return $this->state(function (array $attributes) use ($parentId) {
            return [
                'parent_id' => $parentId,
            ];
        });
    }

    /**
     * Generate sample geographic boundaries.
     *
     * @return array
     */
    protected function generateBoundaries()
    {
        // Generate a simple polygon around a random coordinate
        $centerLat = $this->faker->latitude();
        $centerLng = $this->faker->longitude();
        $size = 0.5; // Degrees of latitude/longitude

        return [
            'type'        => 'Polygon',
            'coordinates' => [
                [
                    [$centerLng - $size, $centerLat - $size],
                    [$centerLng + $size, $centerLat - $size],
                    [$centerLng + $size, $centerLat + $size],
                    [$centerLng - $size, $centerLat + $size],
                    [$centerLng - $size, $centerLat - $size], // Close the polygon
                ],
            ],
        ];
    }
}
