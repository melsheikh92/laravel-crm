<?php
try {
    $user = auth()->user();
    if (!$user) {
        echo "No authenticated user.\n";
    } else {
        echo "User Class: " . get_class($user) . "\n";
        echo "User ID: " . $user->id . "\n";
        echo "User Table: " . $user->getTable() . "\n";

        $exists = \Illuminate\Support\Facades\DB::table($user->getTable())->where('id', $user->id)->exists();
        echo "User Exists in DB: " . ($exists ? 'Yes' : 'No') . "\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
