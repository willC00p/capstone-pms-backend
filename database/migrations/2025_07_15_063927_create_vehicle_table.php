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
        Schema::create('vehicle', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->constrained('driver')->onDelete('cascade');
           // $table->string('vehicle_type');
            $table->string('plate_no')->unique();
            $table->string('or'); 
            $table->string('cr'); 
            $table->string('vehicle_color');
            $table->year('year_model'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle');
    }
};
