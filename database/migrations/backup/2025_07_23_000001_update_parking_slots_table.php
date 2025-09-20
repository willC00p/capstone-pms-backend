<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('parking_slots', function (Blueprint $table) {
            if (!Schema::hasColumn('parking_slots', 'space_status')) {
                $table->enum('space_status', ['available', 'occupied'])->default('available');
            }
            if (!Schema::hasColumn('parking_slots', 'space_type')) {
                $table->string('space_type')->default('standard');
            }
            if (!Schema::hasColumn('parking_slots', 'space_number')) {
                $table->string('space_number');
            }
            if (!Schema::hasColumn('parking_slots', 'position_x')) {
                $table->float('position_x');
            }
            if (!Schema::hasColumn('parking_slots', 'position_y')) {
                $table->float('position_y');
            }
            if (!Schema::hasColumn('parking_slots', 'width')) {
                $table->float('width');
            }
            if (!Schema::hasColumn('parking_slots', 'height')) {
                $table->float('height');
            }
            if (!Schema::hasColumn('parking_slots', 'rotation')) {
                $table->float('rotation')->default(0);
            }
        });
    }

    public function down()
    {
        Schema::table('parking_slots', function (Blueprint $table) {
            $table->dropColumn([
                'space_status',
                'space_type',
                'space_number',
                'position_x',
                'position_y',
                'width',
                'height',
                'rotation'
            ]);
        });
    }
};
