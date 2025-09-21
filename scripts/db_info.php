<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;
try {
    $conn = DB::connection();
    $config = $conn->getConfig();
    echo "DB connection config:\n";
    echo "driver: " . ($config['driver'] ?? '') . "\n";
    echo "host: " . ($config['host'] ?? '') . "\n";
    echo "database: " . ($config['database'] ?? '') . "\n";
    echo "username: " . ($config['username'] ?? '') . "\n";
    $dbname = $conn->getDatabaseName();
    echo "DB::getDatabaseName(): " . $dbname . "\n";

    echo "\nSHOW TABLES:\n";
    $rows = DB::select('SHOW TABLES');
    foreach ($rows as $r) {
        $arr = (array)$r;
        echo reset($arr) . "\n";
    }

    echo "\nINFORMATION_SCHEMA TABLES for this database:\n";
    $rows = DB::select('SELECT TABLE_NAME FROM information_schema.tables WHERE TABLE_SCHEMA = ?', [$dbname]);
    foreach ($rows as $r) {
        $a = (array)$r;
        echo $a['TABLE_NAME'] . "\n";
    }

    echo "\nMIGRATIONS rows:\n";
    $m = DB::select('SELECT migration,batch FROM migrations ORDER BY id');
    foreach ($m as $row) {
        $r = (array)$row;
        echo $r['migration'] . ' | ' . $r['batch'] . "\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
$kernel->terminate(
    new Symfony\Component\Console\Input\ArgvInput([]),
    0
);
