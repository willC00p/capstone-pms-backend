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
        Schema::table('parking_slots', function (Blueprint $table) {
            $table->float('rotation')->default(0)->after('height');
            // Add a json column for metadata if it doesn't exist
            if (!Schema::hasColumn('parking_slots', 'metadata')) {
                $table->json('metadata')->nullable()->after('rotation');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parking_slots', function (Blueprint $table) {
            $table->dropColumn('rotation');
        });
    }
};
