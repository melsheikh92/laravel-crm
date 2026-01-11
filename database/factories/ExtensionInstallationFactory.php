<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Webkul\Marketplace\Models\Extension;
use Webkul\Marketplace\Models\ExtensionInstallation;
use Webkul\Marketplace\Models\ExtensionVersion;
use Webkul\User\Models\User;

class ExtensionInstallationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ExtensionInstallation::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'extension_id'        => Extension::factory(),
            'user_id'             => User::factory(),
            'version_id'          => ExtensionVersion::factory(),
            'status'              => 'active',
            'auto_update_enabled' => false,
            'installation_notes'  => null,
            'settings'            => [],
            'installed_at'        => now(),
            'updated_at_version'  => null,
        ];
    }

    /**
     * Indicate that the installation is active.
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
     * Indicate that the installation is inactive.
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
     * Indicate that auto-update is enabled.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function autoUpdateEnabled()
    {
        return $this->state(function (array $attributes) {
            return [
                'auto_update_enabled' => true,
            ];
        });
    }
}
