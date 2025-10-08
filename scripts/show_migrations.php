<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;
try {
    $rows = DB::select('SELECT * FROM migrations');
    if (empty($rows)) { echo "no rows in migrations\n"; exit(0); }
    foreach ($rows as $r) {
        $a = (array)$r;
        echo $a['id'] . ' | ' . $a['migration'] . ' | ' . $a['batch'] . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
$kernel->terminate(
    new Symfony\Component\Console\Input\ArgvInput([]),
    0
);
