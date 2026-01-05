<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SystemAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Check if System Admin role already exists
        $systemAdminRole = DB::table('roles')->where('name', 'System Admin')->first();

        if (!$systemAdminRole) {
            // Create System Admin role
            $roleId = DB::table('roles')->insertGetId([
                'name' => 'System Admin',
                'description' => 'System Administrator with full access',
                'permission_type' => 'all',
                'permissions' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $roleId = $systemAdminRole->id;
        }

        // Check if user already exists
        $existingUser = DB::table('users')->where('email', 'admin1@example.com')->first();

        if (!$existingUser) {
            // Create System Admin user
            DB::table('users')->insert([
                'name' => 'System Admin',
                'email' => 'admin1@example.com',
                'password' => Hash::make('admin123'), // Default password: admin123
                'status' => 1,
                'role_id' => $roleId,
                'view_permission' => 'global',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->command->info('System Admin role and user created successfully!');
            $this->command->info('Email: admin1@example.com');
            $this->command->info('Password: admin123');
        } else {
            // Update existing user to System Admin role
            DB::table('users')
                ->where('email', 'admin1@example.com')
                ->update([
                    'role_id' => $roleId,
                    'view_permission' => 'global',
                    'status' => 1,
                    'updated_at' => now(),
                ]);

            $this->command->info('User admin1@example.com updated to System Admin role!');
        }
    }
}

