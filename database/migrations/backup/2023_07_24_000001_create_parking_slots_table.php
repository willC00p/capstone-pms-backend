<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // First drop the assignments table that depends on parking_slots
        Schema::dropIfExists('parking_assignments');
        
        // Now we can safely drop and recreate parking_slots
        Schema::dropIfExists('parking_slots');
        
        Schema::create('parking_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('layout_id')->constrained('parking_layouts')->onDelete('cascade');
            $table->string('space_number');
            $table->string('space_type')->default('standard');
            $table->enum('space_status', ['available', 'occupied'])->default('available');
            $table->float('position_x');
            $table->float('position_y');
            $table->float('width');
            $table->float('height');
            $table->float('rotation')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('parking_slots');
    }
};
