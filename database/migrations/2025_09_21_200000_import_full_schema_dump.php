<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ImportFullSchemaDump extends Migration
{
    /**
     * Run the migrations.
     * This migration imports a raw SQL dump file. Replace database/sql/pms_db_dump.sql
     * with the full SQL dump before running `php artisan migrate`.
     *
     * Note: For safety this migration will throw if the dump file still contains the
     * placeholder header text to avoid accidental partial imports.
     */
    public function up()
    {
        // Prefer the exact attached dump if present (user provided `pms_db (11).sql`).
        $preferred = database_path('sql/pms_db (11).sql');
        $fallback = database_path('sql/pms_db_dump.sql');
        $path = file_exists($preferred) ? $preferred : $fallback;
        if (!file_exists($path)) {
            throw new \Exception("SQL dump file not found at {$path}. Please add the full dump before running this migration.");
        }

        $sql = file_get_contents($path);
        // Quick sanity checks
        if (strpos($sql, 'Lines omitted') !== false || strpos($sql, 'PMS DB dump placeholder') !== false) {
            throw new \Exception("The SQL dump file appears to be a placeholder or partial. Replace it with the full dump before running this migration.");
        }

        // Remove mysql-specific restore directives and stray fences
        $sql = preg_replace('/\/\*!\d+\s+SET\s+[^;]*@OLD_[^;]*;?\s*\*\//i', '', $sql);
        $sql = preg_replace('/^\s*SET\s+[^=]+=\s*@OLD_[^;]+;?\s*$/mi', '', $sql);
        $sql = preg_replace('/^\s*```(?:sql)?\s*$/mi', '', $sql);
        $sql = preg_replace('/^\s*;\s*$/m', '', $sql);
        $pos = strripos($sql, "COMMIT;");
        if ($pos !== false) $sql = substr($sql, 0, $pos + strlen("COMMIT;"));
        $sql = trim($sql);

        // Remove any statements that touch Laravel's migrations table to avoid conflicts
        $sql = preg_replace('/CREATE TABLE `migrations`[\s\S]*?;\s*/i', '', $sql);
        $sql = preg_replace('/INSERT INTO `migrations`[\s\S]*?;\s*/i', '', $sql);
        $sql = preg_replace('/ALTER TABLE `migrations`[\s\S]*?;\s*/i', '', $sql);

        $text = str_replace("\r\n", "\n", $sql);

        // Extract CREATEs, INSERTs and ALTERs
        preg_match_all('/(CREATE TABLE\s+(?:IF NOT EXISTS\s+)?(?:`[^`]+`\.)?`([^`]+)`[\s\S]*?;)/i', $text, $cMatches, PREG_SET_ORDER);
        $createOrder = [];
        foreach ($cMatches as $cm) {
            $tbl = $cm[2];
            $createOrder[$tbl] = trim($cm[1]);
        }

        preg_match_all('/(INSERT INTO\s+`([^`]+)`[\s\S]*?;)/i', $text, $iMatches, PREG_SET_ORDER);
        $insertsByTable = [];
        foreach ($iMatches as $im) {
            $tbl = $im[2];
            $insertsByTable[$tbl][] = trim($im[1]);
        }

        preg_match_all('/(ALTER TABLE\s+(?:`[^`]+`\.)?`([^`]+)`[\s\S]*?;)/i', $text, $aMatches, PREG_SET_ORDER);
        $alterList = [];
        foreach ($aMatches as $am) $alterList[] = trim($am[1]);

        // Begin import: disable foreign keys
        try { DB::unprepared('SET FOREIGN_KEY_CHECKS=0;'); } catch (\Exception $e) {}

        // Run CREATEs (in-dump order). If a table exists but is missing columns from the
        // dump's CREATE, drop it and recreate from the dump statement. This prevents the
        // 'minimal fallback' table from earlier failed runs blocking a correct import.
        foreach ($createOrder as $tbl => $stmt) {
            try {
                if (Schema::hasTable($tbl)) {
                    // Extract column names from the CREATE statement
                    preg_match_all('/^\s*`([^`]+)`\s+/m', $stmt, $colMatches);
                    $expectedCols = array_map('strtolower', $colMatches[1] ?? []);
                    $existingCols = array_map('strtolower', Schema::getColumnListing($tbl));
                    $missing = array_diff($expectedCols, $existingCols);
                    if (!empty($missing)) {
                        // drop and recreate
                        try {
                            Schema::dropIfExists($tbl);
                        } catch (\Exception $e) {
                            @file_put_contents(storage_path('logs/import-error.log'), "Failed to drop {$tbl}: " . $e->getMessage() . "\n", FILE_APPEND);
                        }
                        DB::unprepared($stmt);
                        continue;
                    }
                    // table ok, skip
                    continue;
                }
                DB::unprepared($stmt);
            } catch (\Exception $e) {
                // log and continue
                @file_put_contents(storage_path('logs/import-error.log'), "CREATE {$tbl} failed: " . $e->getMessage() . "\n", FILE_APPEND);
            }
        }

        // Ensure tables referenced by INSERTs exist; if not, try a minimal fallback
        foreach (array_keys($insertsByTable) as $tbl) {
            if (!Schema::hasTable($tbl)) {
                try {
                    Schema::create($tbl, function ($table) {
                        $table->bigIncrements('id');
                    });
                } catch (\Exception $e) {
                    @file_put_contents(storage_path('logs/import-error.log'), "Fallback create {$tbl} failed: " . $e->getMessage() . "\n", FILE_APPEND);
                }
            }
        }

        // Run INSERTs (INSERT IGNORE to tolerate duplicates)
        foreach ($insertsByTable as $tbl => $stmts) {
            foreach ($stmts as $ins) {
                $safe = preg_replace('/^INSERT INTO\s+/i', 'INSERT IGNORE INTO ', $ins);
                try {
                    DB::unprepared($safe);
                } catch (\Exception $e) {
                    @file_put_contents(storage_path('logs/import-error.log'), "INSERT {$tbl} failed: " . $e->getMessage() . "\n", FILE_APPEND);
                }
            }
        }

        // Run ALTERs and constraints
        foreach ($alterList as $a) {
            if (preg_match('/ALTER TABLE\s+(?:`[^`]+`\.)?`([^`]+)`/i', $a, $mm)) {
                if (!Schema::hasTable($mm[1])) continue;
            }
            try {
                DB::unprepared($a);
            } catch (\Exception $e) {
                $msg = $e->getMessage();
                if (stripos($msg, 'Multiple primary key defined') !== false
                    || stripos($msg, 'Duplicate key name') !== false
                    || stripos($msg, 'Duplicate column name') !== false
                    || stripos($msg, 'already exists') !== false
                ) {
                    continue;
                }
                @file_put_contents(storage_path('logs/import-error.log'), "ALTER failed: " . $e->getMessage() . "\n", FILE_APPEND);
            }
        }

        try { DB::unprepared('SET FOREIGN_KEY_CHECKS=1;'); } catch (\Exception $e) {}
    }

    /**
     * Reverse the migrations.
     * By default this will try to drop every table found in the dump (best-effort).
     */
    public function down()
    {
        // NOTE: This is a best-effort down. It reads CREATE TABLE statements from the SQL
        // and attempts to DROP the created tables. If the dump uses complex constructs,
        // you may need to drop tables manually.
        $path = database_path('sql/pms_db_dump.sql');
        if (!file_exists($path)) {
            return;
        }
        $sql = file_get_contents($path);

        // find all table names from CREATE TABLE `name`
        preg_match_all('/CREATE TABLE `([^`]+)`/i', $sql, $matches);
        $tables = array_unique($matches[1] ?? []);
        foreach ($tables as $t) {
            DB::statement("DROP TABLE IF EXISTS `{$t}`");
        }
    }
}
