<?php
require __DIR__ . '/vendor/autoload.php';
// Bootstrap minimal app for facades
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$status = $kernel->bootstrap();

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

$p = json_encode(['id'=>9999,'name'=>'Tess Tester','email'=>'tess@example.com']);
$url = 'https://chart.googleapis.com/chart?cht=qr&chs=400x400&chl=' . rawurlencode($p) . '&choe=UTF-8';
try {
    $r = Http::timeout(10)->withoutVerifying()->get($url);
    if ($r->ok()) {
        $body = $r->body();
        Storage::disk('public')->put('qrs/test_qr_http.png', $body);
        echo Storage::url('qrs/test_qr_http.png');
    } else {
        echo 'http failed status: ' . $r->status();
    }
} catch (\Throwable $e) {
    echo 'http exception: ' . $e->getMessage();
}
