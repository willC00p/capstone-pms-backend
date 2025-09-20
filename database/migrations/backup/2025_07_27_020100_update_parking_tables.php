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
        Schema::dropIfExists('parking_slots');
        Schema::dropIfExists('parking_layouts');
        
        Schema::create('parking_layouts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('background_image')->nullable();
            $table->json('layout_data')->nullable();
            $table->timestamps();
        });

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
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parking_slots');
        Schema::dropIfExists('parking_layouts');
    }
};
