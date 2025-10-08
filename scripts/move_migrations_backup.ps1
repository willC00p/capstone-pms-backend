# Move all migration files except the import migration into migrations_backup/originals
param(
    [string]$RepoRoot = "c:\pms\Capstone-PMS-backend"
)

$migrations = Join-Path $RepoRoot 'database\migrations'
$backup = Join-Path $RepoRoot 'database\migrations_backup\originals'

Write-Host "Source migrations: $migrations"
Write-Host "Backup folder: $backup"

if (-not (Test-Path $migrations)) {
    Write-Error "Migrations folder not found: $migrations"
    exit 1
}

New-Item -ItemType Directory -Force -Path $backup | Out-Null

Get-ChildItem -Path $migrations -Filter '*.php' | Where-Object { $_.Name -ne '2025_09_21_200000_import_full_schema_dump.php' } | ForEach-Object {
    $dest = Join-Path $backup $_.Name
    Move-Item -Path $_.FullName -Destination $dest -Force
    Write-Host "Moved: $($_.Name) -> $dest"
}

Write-Host "Done. Remaining migrations in:`n";
Get-ChildItem -Path $migrations -Filter '*.php' | ForEach-Object { Write-Host $_.Name }
