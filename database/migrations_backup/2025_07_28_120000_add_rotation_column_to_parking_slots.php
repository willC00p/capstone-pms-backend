<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('parking_slots', 'rotation')) {
            Schema::table('parking_slots', function (Blueprint $table) {
                $table->float('rotation')->default(0)->after('height');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('parking_slots', 'rotation')) {
            Schema::table('parking_slots', function (Blueprint $table) {
                $table->dropColumn('rotation');
            });
        }
    }
};
