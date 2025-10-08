<?php
require __DIR__ . '/../vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use Dotenv\Dotenv;

// Load env
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Setup Capsule (DB connection) using env
$capsule = new Capsule;
$capsule->addConnection([
    'driver' => getenv('DB_CONNECTION') ?: 'mysql',
    'host' => getenv('DB_HOST'),
    'database' => getenv('DB_DATABASE'),
    'username' => getenv('DB_USERNAME'),
    'password' => getenv('DB_PASSWORD'),
    'charset' => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix' => '',
]);
$capsule->setAsGlobal();
$capsule->bootEloquent();

// Query
$user = $capsule->table('users')->where('email', 'admin@admin.com')->first();
if ($user) {
    echo "FOUND: " . $user->email . PHP_EOL;
} else {
    echo "NOT FOUND" . PHP_EOL;
}
