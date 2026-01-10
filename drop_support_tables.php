<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Drop the partial support tables so we can recreate them properly
$tables = ['ticket_watchers', 'ticket_tags', 'ticket_attachments', 'ticket_messages', 'support_tickets', 'ticket_categories'];

echo "Dropping partial support tables...\n\n";
foreach ($tables as $table) {
    if (Schema::hasTable($table)) {
        Schema::dropIfExists($table);
        echo "✅ Dropped $table\n";
    } else {
        echo "⏭️  $table doesn't exist, skipping\n";
    }
}

echo "\nDone! Now run: php artisan migrate\n";
