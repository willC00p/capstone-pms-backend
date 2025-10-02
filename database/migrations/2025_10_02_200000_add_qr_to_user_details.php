<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('user_details', 'qr_path')) {
            Schema::table('user_details', function (Blueprint $table) {
                $table->string('qr_path')->nullable()->after('deed_name');
            });
        }
        // Also fix any previously-mangled qr_path values
        DB::statement("UPDATE user_details SET qr_path = REPLACE(qr_path, '/storage/https://', 'https://') WHERE qr_path LIKE '/storage/https://%'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('user_details', 'qr_path')) {
            Schema::table('user_details', function (Blueprint $table) {
                $table->dropColumn('qr_path');
            });
        }
        // Revert only Google Chart QR entries back to the broken prefix if needed
        DB::statement("UPDATE user_details SET qr_path = CONCAT('/storage/', qr_path) WHERE qr_path LIKE 'https://chart.googleapis.com/chart?cht=qr%'");
    }
};
