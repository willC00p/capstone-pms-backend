<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    \Illuminate\Support\Facades\Storage::disk('public')->put('qrs/test_write.txt', 'hello-from-test');
    echo "STORED_OK\n";
} catch (Throwable $e) {
    echo "ERR: " . $e->getMessage() . "\n";
}
