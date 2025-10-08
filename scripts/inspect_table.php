<?php
// scripts/inspect_table.php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$table = 'parking_assignments';
try {
    $rows = DB::select("SHOW TABLES LIKE '{$table}'");
    if (empty($rows)) {
        echo "Table '{$table}' does not exist.\n";
        exit(0);
    }
    $create = DB::select("SHOW CREATE TABLE `{$table}`");
    if (!empty($create)) {
        $row = (array)$create[0];
        $key = count($row) > 1 ? array_keys($row)[1] : array_keys($row)[0];
        echo "SHOW CREATE TABLE for {$table}:\n";
        echo $row[$key] . "\n\n";
    }
    $cols = DB::select("DESCRIBE `{$table}`");
    echo "Columns:\n";
    foreach ($cols as $c) {
        $arr = (array)$c;
        echo $arr['Field'] . "\t" . $arr['Type'] . "\t" . $arr['Null'] . "\t" . $arr['Key'] . "\t" . $arr['Default'] . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
