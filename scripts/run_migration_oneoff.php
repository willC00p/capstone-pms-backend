<?php
// One-off script to execute a migration class up() method safely.
// Usage: php scripts/run_migration_oneoff.php database/migrations/2025_10_02_200000_add_qr_to_user_details.php

if ($argc < 2) {
    echo "Usage: php scripts/run_migration_oneoff.php <relative-migration-path>\n";
    exit(1);
}

$migrationPath = $argv[1];
$fullPath = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . $migrationPath;
if (!file_exists($fullPath)) {
    echo "Migration file not found: $fullPath\n";
    exit(2);
}

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Bootstrapped Laravel app\n";

$code = file_get_contents($fullPath);
// Evaluate the migration file in isolated scope and capture the returned class
$migration = null;
try {
    $migration = eval('?>' . $code);
} catch (Throwable $e) {
    echo "Failed to eval migration file: " . $e->getMessage() . "\n";
    exit(3);
}

if (! $migration) {
    echo "Migration file did not return a migration class instance.\n";
    exit(4);
}

if (method_exists($migration, 'up')) {
    echo "Running migration up()...\n";
    try {
        $migration->up();
        echo "Migration up() finished successfully.\n";
    } catch (Throwable $e) {
        echo "Migration up() threw: " . $e->getMessage() . "\n";
        echo $e->getTraceAsString() . "\n";
        exit(5);
    }
} else {
    echo "Migration class has no up() method.\n";
    exit(6);
}

exit(0);
