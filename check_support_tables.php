<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Check which support-related tables exist
$tables = [
    'ticket_categories',
    'support_tickets',
    'ticket_messages',
    'ticket_attachments',
    'kb_categories',
    'kb_articles',
    'kb_article_attachments',
    'sla_policies',
    'sla_policy_rules',
    'sla_policy_conditions',
];

echo "Checking support tables:\n\n";
foreach ($tables as $table) {
    $exists = Schema::hasTable($table);
    echo ($exists ? '✅' : '❌') . " $table\n";
}
