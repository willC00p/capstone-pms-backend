<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RecreateParkingSlotsTable extends Migration
{
    public function up()
    {
        Schema::create('parking_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('layout_id')->constrained('parking_layouts')->onDelete('cascade');
            $table->string('space_number');
            $table->string('space_type')->default('standard');
            $table->string('space_status')->default('available');
            $table->float('position_x');
            $table->float('position_y');
            $table->float('width');
            $table->float('height');
            $table->float('rotation')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('parking_slots');
    }
}
