<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('parking_layouts', function (Blueprint $table) {
            if (!Schema::hasColumn('parking_layouts', 'background_image')) {
                $table->string('background_image')->nullable();
            }
        });

        Schema::table('parking_slots', function (Blueprint $table) {
            if (!Schema::hasColumn('parking_slots', 'rotation')) {
                $table->decimal('rotation', 10, 2)->default(0);
            }
        });
    }

    public function down()
    {
        Schema::table('parking_layouts', function (Blueprint $table) {
            $table->dropColumn('background_image');
        });

        Schema::table('parking_slots', function (Blueprint $table) {
            $table->dropColumn('rotation');
        });
    }
};
