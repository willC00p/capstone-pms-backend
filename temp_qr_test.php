<?php
$p = json_encode(['id'=>9999,'name'=>'Tess Tester','email'=>'tess@example.com']);
$url = 'https://chart.googleapis.com/chart?cht=qr&chs=400x400&chl=' . rawurlencode($p) . '&choe=UTF-8';
$r = @file_get_contents($url);
$dir = __DIR__ . '/storage/app/public/qrs';
if ($r !== false) {
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    file_put_contents($dir . '/test_qr.png', $r);
    echo 'wrote: ' . $dir . '/test_qr.png';
} else {
    echo 'fetch failed';
}
