<?php
$file = __DIR__ . '/../database/sql/pms_db (11).sql';
if (!file_exists($file)) { echo "dump not found\n"; exit(1); }
$text = file_get_contents($file);
$text = str_replace("\r\n", "\n", $text);

preg_match_all('/(CREATE TABLE\s+(?:IF NOT EXISTS\s+)?(?:`[^`]+`\.)?`([^`]+)`[\s\S]*?;)/i', $text, $cMatches, PREG_SET_ORDER);
$found = 0;
foreach ($cMatches as $cm) {
    $tbl = $cm[2];
    if (strtolower($tbl) === 'parking_assignments') {
        echo "Found CREATE for parking_assignments:\n";
        echo $cm[1] . "\n\n";
        preg_match_all('/^\s*`([^`]+)`\s+/m', $cm[1], $colMatches);
        echo "Extracted columns:\n";
        foreach ($colMatches[1] as $c) echo "- $c\n";
        $found++;
    }
}
if ($found === 0) echo "No CREATE found for parking_assignments\n";
