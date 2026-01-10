<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Webkul\User\Models\User;

$users = User::all();

if ($users->isEmpty()) {
    echo "NO USERS FOUND IN DATABASE.\n";
} else {
    foreach ($users as $user) {
        echo "User ID: " . $user->id . "\n";
        echo "Name: " . $user->name . "\n";
        echo "Email: " . $user->email . "\n";
        echo "Role: " . ($user->role ? $user->role->name : 'No Role') . "\n";
        echo "------------------------\n";
    }
}
