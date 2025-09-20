<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateParkingSlotsTable extends Migration
{
    public function up()
    {
        Schema::create('parking_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('layout_id')->constrained('parking_layouts')->onDelete('cascade');
            $table->string('space_number');
            $table->string('space_type');
            $table->string('space_status')->default('available');
            $table->decimal('position_x', 10, 2);
            $table->decimal('position_y', 10, 2);
            $table->decimal('width', 10, 2);
            $table->decimal('height', 10, 2);
            $table->decimal('rotation', 10, 2)->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('parking_slots');
    }
}
