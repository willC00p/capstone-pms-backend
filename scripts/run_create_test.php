<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;

$file = __DIR__ . '/../database/sql/pms_db (11).sql';
$text = file_get_contents($file);
$text = str_replace("\r\n", "\n", $text);
preg_match_all('/(CREATE TABLE\s+(?:IF NOT EXISTS\s+)?(?:`[^`]+`\.)?`([^`]+)`[\s\S]*?;)/i', $text, $cMatches, PREG_SET_ORDER);
$found = false;
foreach ($cMatches as $cm) {
    $tbl = $cm[2];
    if (strtolower($tbl) === 'parking_assignments') {
        $stmt = $cm[1];
        echo "Attempting to run CREATE for parking_assignments...\n";
        try {
            DB::unprepared('DROP TABLE IF EXISTS `parking_assignments`;');
            DB::unprepared($stmt);
            echo "CREATE executed without exception.\n";
        } catch (Exception $e) {
            echo "CREATE threw: " . $e->getMessage() . "\n";
        }
        $found = true;
        break;
    }
}
if (!$found) echo "CREATE not found in dump\n";
