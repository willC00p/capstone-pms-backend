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
        Schema::create('parking_slot', function (Blueprint $table) {
            $table->string('parking_code')->primary();
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicle')->nullOnDelete();
            //$table->integer('vehicle_id');
            $table->string('location');
            $table->enum('status', ['availavle', 'occupied', 'reserved']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parking_slot');
    }
};
