<?php
require __DIR__ . '/vendor/autoload.php';
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

try {
    $options = new QROptions([
        'outputType' => QRCode::OUTPUT_MARKUP_SVG,
        'eccLevel' => QRCode::ECC_L,
        'version' => 0,
        'scale' => 4,
    ]);
    $qrcode = new QRCode($options);
    $qrPayload = "id=123";
    $svg = $qrcode->render($qrPayload);
    echo "OK\n";
    // write to temp file
    $tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'qr_test.svg';
    file_put_contents($tmp, $svg);
    echo "WROTE:$tmp\n";
} catch (Throwable $e) {
    echo "ERR:" . $e->getMessage() . PHP_EOL;
}
