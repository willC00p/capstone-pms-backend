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
        Schema::create('parking_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parking_slot_id');
            $table->foreign('parking_slot_id')->references('id')->on('parking_slots')->onDelete('cascade');
            $table->enum('assignee_type', ['guest', 'faculty']);
            $table->string('name');
            $table->string('contact_number');
            $table->enum('vehicle_type', ['car', 'motorcycle', 'bicycle']);
            $table->string('plate_number');
            $table->dateTime('start_time');
            $table->dateTime('end_time')->nullable();
            $table->text('purpose');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parking_assignments');
    }
};