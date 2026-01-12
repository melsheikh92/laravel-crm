<?php

use App\Models\OnboardingProgress;
use Illuminate\Support\Facades\DB;
use Webkul\User\Models\User;

try {
    echo "DB Default: " . config('database.default') . "\n";
    echo "DB Host: " . config('database.connections.mysql.host') . "\n";
    echo "DB Database: " . config('database.connections.mysql.database') . "\n";

    $user = User::find(1);
    if ($user) {
        echo "User 1 found: " . $user->email . "\n";
    } else {
        echo "User 1 NOT found!\n";
    }

    $count = DB::table('users')->count();
    echo "Total users: $count\n";

    echo "Attempting to create OnboardingProgress for user 1...\n";
    $progress = OnboardingProgress::create(['user_id' => 1]);
    echo "Success! Created ID: " . $progress->id . "\n";

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
