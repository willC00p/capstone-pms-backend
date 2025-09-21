<?php
// Debug: replicate migration splitting logic on the SQL dump and list detected tables
$path = __DIR__ . '/../database/sql/pms_db (11).sql';
if (!file_exists($path)) { echo "dump not found: $path\n"; exit(1); }
$sql = file_get_contents($path);
// Apply same sanitizers as migration (minimal)
$sql = preg_replace('/\/\*!\d+\s+SET\s+[^;]*@OLD_[^;]*;?\s*\*\//i', '', $sql);
$sql = preg_replace('/^\s*SET\s+[^=]+=\s*@OLD_[^;]+;?\s*$/mi', '', $sql);
$sql = preg_replace('/^\s*```(?:sql)?\s*$/mi', '', $sql);
$sql = preg_replace('/^\s*;\s*$/m', '', $sql);
$pos = strripos($sql, "COMMIT;");
if ($pos !== false) { $sql = substr($sql, 0, $pos + strlen("COMMIT;")); }
$sql = trim($sql);
$parts = preg_split('/;\s*\r?\n/', $sql);
$create = [];
$insert = [];
$alter = [];
foreach ($parts as $p) {
    $s = trim($p);
    if ($s === '') continue;
    $sTerm = $s . ';';
    if (preg_match('/^CREATE TABLE\b/i', $s)) {
        if (preg_match('/CREATE TABLE\s+(?:IF NOT EXISTS\s+)?(?:`[^`]+`\.)?`([^`]+)`/i', $s, $m)) {
            $create[] = $m[1];
        } else {
            $create[] = substr($s,0,60);
        }
    } elseif (preg_match('/^INSERT INTO\b/i', $s)) {
        if (preg_match('/INSERT INTO\s+(?:`[^`]+`\.)?`([^`]+)`/i', $s, $m2)) {
            $insert[] = $m2[1];
        } else {
            $insert[] = substr($s,0,60);
        }
    } elseif (preg_match('/^ALTER TABLE\b/i', $s)) {
        if (preg_match('/ALTER TABLE\s+(?:`[^`]+`\.)?`([^`]+)`/i', $s, $m3)) {
            $alter[] = $m3[1];
        } else {
            $alter[] = substr($s,0,60);
        }
    }
}
echo "CREATE (".count($create)."):\n".implode(',', $create)."\n\n";
echo "INSERT (".count($insert)."):\n".implode(',', array_unique($insert))."\n\n";
echo "ALTER (".count($alter)."):\n".implode(',', array_unique($alter))."\n";
