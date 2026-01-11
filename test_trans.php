<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Locale: " . app()->getLocale() . "\n";

$keys = [
    'marketplace::app.admin.extensions.index.title',
    'marketplace::app.admin.categories.index.title',
    'marketplace::app.admin.submissions.index.title',
    'marketplace::app.admin.revenue.index.title',
    'marketplace::app.admin.developer-applications.index.title',
];

foreach ($keys as $key) {
    $trans = trans($key);
    echo "$key => $trans\n";
    if ($trans === $key) {
        echo "FAIL\n";
    }
}
