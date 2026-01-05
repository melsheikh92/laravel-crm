<?php

namespace Webkul\Installer\Database\Seeders\User;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @param  array  $parameters
     * @return void
     */
    public function run($parameters = [])
    {
        // Don't create a user during installation - let the installer handle admin creation
        // This allows the installer to show the admin creation step
        DB::table('users')->delete();

        // Create a placeholder user record with ID 1 (will be updated by installer)
        // This ensures the admin creation form can update the user properly
        DB::table('users')->insert([
            'id' => 1,
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('admin123'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'status' => 1,
            'role_id' => 1,
            'view_permission' => 'global',
        ]);
    }
}
