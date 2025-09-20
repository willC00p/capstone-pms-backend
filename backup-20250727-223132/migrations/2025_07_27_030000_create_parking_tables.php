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
            $table->string('space_type');
            $table->string('space_status');
            $table->float('position_x');
            $table->float('position_y');
            $table->float('width');
            $table->float('height');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('parking_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parking_slot_id')->constrained('parking_slots')->onDelete('cascade');
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
        Schema::dropIfExists('parking_slots');
        Schema::dropIfExists('parking_layouts');
    }
};
