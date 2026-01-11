<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Webkul\Marketplace\Models\Extension;
use Webkul\Marketplace\Models\ExtensionCategory;
use Webkul\User\Models\User;

class ExtensionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Extension::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $name = $this->faker->words(3, true);
        $types = ['plugin', 'theme', 'integration'];

        return [
            'name'              => ucwords($name),
            'slug'              => Str::slug($name),
            'description'       => $this->faker->sentence(),
            'long_description'  => $this->faker->paragraph(5),
            'type'              => $this->faker->randomElement($types),
            'category_id'       => ExtensionCategory::factory(),
            'price'             => $this->faker->randomElement([0, 9.99, 19.99, 29.99, 49.99, 99.99]),
            'status'            => 'pending',
            'downloads_count'   => $this->faker->numberBetween(0, 10000),
            'average_rating'    => $this->faker->randomFloat(2, 0, 5),
            'featured'          => $this->faker->boolean(20),
            'logo'              => 'extensions/logos/' . $this->faker->uuid() . '.png',
            'screenshots'       => [
                'extensions/screenshots/' . $this->faker->uuid() . '.png',
                'extensions/screenshots/' . $this->faker->uuid() . '.png',
            ],
            'documentation_url' => $this->faker->url(),
            'demo_url'          => $this->faker->url(),
            'repository_url'    => 'https://github.com/' . $this->faker->userName() . '/' . Str::slug($name),
            'support_email'     => $this->faker->safeEmail(),
            'tags'              => $this->faker->words(5),
            'requirements'      => [
                'php'     => '>=8.1',
                'laravel' => '^10.0',
                'crm'     => '^1.0',
            ],
            'author_id'         => User::factory(),
        ];
    }

    /**
     * Indicate that the extension is approved.
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
     * Indicate that the extension is featured.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function featured()
    {
        return $this->state(function (array $attributes) {
            return [
                'featured' => true,
            ];
        });
    }

    /**
     * Indicate that the extension is free.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function free()
    {
        return $this->state(function (array $attributes) {
            return [
                'price' => 0,
            ];
        });
    }

    /**
     * Indicate that the extension is paid.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function paid()
    {
        return $this->state(function (array $attributes) {
            return [
                'price' => 29.99,
            ];
        });
    }
}
