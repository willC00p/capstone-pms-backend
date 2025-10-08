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
        Schema::table('vehicles', function (Blueprint $table) {
            if (!Schema::hasColumn('vehicles', 'or_number')) {
                $table->string('or_number')->nullable()->after('or_path');
            }
            if (!Schema::hasColumn('vehicles', 'cr_number')) {
                $table->string('cr_number')->nullable()->after('cr_path');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            if (Schema::hasColumn('vehicles', 'or_number')) {
                $table->dropColumn('or_number');
            }
            if (Schema::hasColumn('vehicles', 'cr_number')) {
                $table->dropColumn('cr_number');
            }
        });
    }
};
