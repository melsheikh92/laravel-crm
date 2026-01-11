<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Webkul\Marketplace\Models\ExtensionCategory;

class ExtensionCategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ExtensionCategory::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $name = $this->faker->words(2, true);

        return [
            'name'        => ucwords($name),
            'slug'        => Str::slug($name),
            'description' => $this->faker->sentence(),
            'icon'        => 'icon-' . $this->faker->word(),
            'parent_id'   => null,
            'sort_order'  => $this->faker->numberBetween(1, 100),
        ];
    }
}
