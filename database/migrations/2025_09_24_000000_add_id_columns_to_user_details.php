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
            if (! Schema::hasColumn('user_details', 'id_path')) {
                $table->string('id_path')->nullable()->after('cr_path');
            }
            // face_verified column intentionally removed
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_details', function (Blueprint $table) {
            // face_verified column intentionally removed
            if (Schema::hasColumn('user_details', 'id_path')) {
                $table->dropColumn('id_path');
            }
        });
    }
};
