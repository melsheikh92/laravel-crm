<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$columns = DB::select("DESCRIBE users");

echo "Users table structure:\n\n";
foreach ($columns as $column) {
    echo "Field: {$column->Field}\n";
    echo "Type: {$column->Type}\n";
    echo "Null: {$column->Null}\n";
    echo "Key: {$column->Key}\n";
    echo "Default: {$column->Default}\n";
    echo "Extra: {$column->Extra}\n";
    echo "---\n";
}
