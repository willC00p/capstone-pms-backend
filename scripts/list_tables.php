<?php
// scripts/list_tables.php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $tables = DB::select('SHOW TABLES');
    if (empty($tables)) {
        echo "No tables returned\n";
        exit(0);
    }
    foreach ($tables as $row) {
        $arr = (array)$row;
        echo reset($arr) . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
