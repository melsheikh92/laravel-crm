<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Webkul\User\Models\User;
use Webkul\User\Models\Role;

$user = User::with('role')->find(1);

if (!$user) {
    echo "User not found.\n";
    exit;
}

echo "User: " . $user->name . " (ID: " . $user->id . ")\n";
echo "Email: " . $user->email . "\n";
echo "Status: " . ($user->status ? 'Active' : 'Inactive') . "\n";
echo "Role: " . ($user->role ? $user->role->name : 'No Role') . "\n";

if ($user->role) {
    echo "Role Permission Type: " . $user->role->permission_type . "\n";
    echo "Role Permissions: \n";
    if ($user->role->permission_type == 'all') {
        echo "  - ALL (Super Admin)\n";
    } else {
        $permissions = json_decode($user->role->permissions, true) ?? [];
        if (empty($permissions)) {
            echo "  - NONE (Empty permissions array)\n";
        } else {
            foreach ($permissions as $permission) {
                echo "  - " . $permission . "\n";
            }
        }
    }
}

// Check if bouncer allows 'dashboard'
auth()->guard('user')->login($user);
$hasDashboard = bouncer()->hasPermission('dashboard');
echo "\nBouncer Check for 'dashboard': " . ($hasDashboard ? 'GRANTED' : 'DENIED') . "\n";
