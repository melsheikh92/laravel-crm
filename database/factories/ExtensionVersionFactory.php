<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Webkul\Marketplace\Models\Extension;
use Webkul\Marketplace\Models\ExtensionVersion;

class ExtensionVersionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ExtensionVersion::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'extension_id'    => Extension::factory(),
            'version'         => $this->faker->semver(),
            'changelog'       => $this->faker->paragraph(),
            'laravel_version' => '^10.0',
            'crm_version'     => '^1.0',
            'php_version'     => '>=8.1',
            'dependencies'    => null,
            'file_path'       => 'extensions/versions/' . $this->faker->uuid() . '.zip',
            'file_size'       => $this->faker->numberBetween(100000, 5000000),
            'checksum'        => $this->faker->sha256(),
            'status'          => 'pending',
            'downloads_count' => 0,
            'release_date'    => null,
        ];
    }

    /**
     * Indicate that the version is approved.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function approved()
    {
        return $this->state(function (array $attributes) {
            return [
                'status'       => 'approved',
                'release_date' => now(),
            ];
        });
    }

    /**
     * Indicate that the version is released.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function released()
    {
        return $this->state(function (array $attributes) {
            return [
                'status'       => 'approved',
                'release_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            ];
        });
    }
}
