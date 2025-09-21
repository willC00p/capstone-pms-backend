<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('parking_slots', function (Blueprint $table) {
            // Add metadata column if it doesn't exist
            if (!Schema::hasColumn('parking_slots', 'metadata')) {
                $table->json('metadata')->nullable()->after('rotation');
            }
        });
    }

    public function down()
    {
        Schema::table('parking_slots', function (Blueprint $table) {
            if (Schema::hasColumn('parking_slots', 'metadata')) {
                $table->dropColumn('metadata');
            }
        });
    }
};
