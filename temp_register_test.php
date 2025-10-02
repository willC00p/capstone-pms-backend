<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

$url = env('APP_URL', 'http://127.0.0.1:8000') . '/api/register';
try {
    $resp = Http::asForm()->post($url, [
        'firstname' => 'TTest',
        'lastname' => 'User',
        'email' => 'testreg2+' . rand(1000,9999) . '@example.com',
        'password' => 'Password123',
        'c_password' => 'Password123'
    ]);
    echo "status: " . $resp->status() . "\n";
    echo $resp->body();
} catch (\Throwable $e) {
    echo 'request failed: ' . $e->getMessage();
}
