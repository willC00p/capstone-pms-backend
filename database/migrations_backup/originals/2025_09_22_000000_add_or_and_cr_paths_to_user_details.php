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
        Schema::table('user_details', function (Blueprint $table) {
            if (!Schema::hasColumn('user_details', 'or_path')) {
                $table->string('or_path')->nullable()->after('position');
            }
            if (!Schema::hasColumn('user_details', 'cr_path')) {
                $table->string('cr_path')->nullable()->after('or_path');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_details', function (Blueprint $table) {
            if (Schema::hasColumn('user_details', 'cr_path')) {
                $table->dropColumn('cr_path');
            }
            if (Schema::hasColumn('user_details', 'or_path')) {
                $table->dropColumn('or_path');
            }
        });
    }
};
