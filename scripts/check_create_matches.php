<?php
$path = __DIR__ . '/../database/sql/pms_db (11).sql';
if (!file_exists($path)) { echo "dump not found\n"; exit(1); }
$text = file_get_contents($path);
$text = str_replace("\r\n", "\n", $text);
$pattern = '/(CREATE TABLE\s+(?:IF NOT EXISTS\s+)?(?:`[^`]+`\.)?`([^`]+)`[\s\S]*?;)/i';
if (preg_match_all($pattern, $text, $m, PREG_SET_ORDER)) {
    echo "Found CREATE TABLE matches: " . count($m) . "\n";
    foreach ($m as $cm) {
        echo "TABLE: " . $cm[2] . "\n";
    }
} else {
    echo "No matches\n";
}
