param(
    [string]$RepoRoot = "c:\pms\Capstone-PMS-backend",
    [string]$DbName = 'pms_db',
    [string]$DbUser = 'root',
    [string]$DumpFilePath
)

$mysqlPath = 'C:\xampp\mysql\bin\mysql.exe'
$mysqldumpPath = 'C:\xampp\mysql\bin\mysqldump.exe'
$defaultDump = Join-Path $RepoRoot 'database\sql\pms_db_dump.sql'
$dumpFile = if ($DumpFilePath) { $DumpFilePath } else { $defaultDump }
$backupFile = Join-Path $RepoRoot 'database\sql\pms_db_preimport_backup.sql'

if (-not (Test-Path $mysqlPath)) {
    Write-Error "mysql.exe not found at $mysqlPath. Update this script to point to your mysql client."
    exit 1
}
if (-not (Test-Path $mysqldumpPath)) {
    Write-Error "mysqldump.exe not found at $mysqldumpPath. Update this script to point to your mysqldump client."
    exit 1
}
if (-not (Test-Path $dumpFile)) {
    Write-Error "Dump file not found: $dumpFile"
    exit 1
}

Write-Host "Creating backup of current database to: $backupFile"
& $mysqldumpPath -u $DbUser $DbName > $backupFile
if ($LASTEXITCODE -ne 0) {
    Write-Error "mysqldump failed with exit code $LASTEXITCODE"
    exit 2
}

Write-Host "Listing existing tables in database $DbName..."
$tables = & $mysqlPath -u $DbUser -N -e "SHOW TABLES" $DbName
if ($LASTEXITCODE -ne 0) {
    Write-Error "Failed to list tables (exit code $LASTEXITCODE)"
    exit 3
}

if ($tables) {
    Write-Host "Found tables:"
    $tables | ForEach-Object { Write-Host " - $_" }

    $dropStatements = ($tables | ForEach-Object { "DROP TABLE IF EXISTS `$_`;" }) -join ' '
    $dropSql = "SET FOREIGN_KEY_CHECKS=0; $dropStatements SET FOREIGN_KEY_CHECKS=1;"
    Write-Host "Dropping tables (disabling foreign key checks)..."
    & $mysqlPath -u $DbUser $DbName -e $dropSql
    if ($LASTEXITCODE -ne 0) {
        Write-Error "Failed to drop tables (exit code $LASTEXITCODE)"
        exit 4
    }
    Write-Host "Dropped existing tables."
} else {
    Write-Host "No tables found in $DbName."
}

Write-Host "Importing dump from $dumpFile into $DbName... (this may take a moment)"
Get-Content $dumpFile -Raw | & $mysqlPath -u $DbUser $DbName
if ($LASTEXITCODE -ne 0) {
    Write-Error "Import failed with exit code $LASTEXITCODE"
    exit 5
}

Write-Host "Import completed successfully. Backup kept at: $backupFile"
