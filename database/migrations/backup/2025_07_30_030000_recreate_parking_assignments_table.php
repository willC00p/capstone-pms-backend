<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Drop existing table if it exists
        Schema::dropIfExists('parking_assignments');

        // Create new parking_assignments table with all required fields
        Schema::create('parking_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parking_slot_id')->constrained('parking_slots')->onDelete('cascade');
            $table->string('assignee_type')->default('driver');
            $table->string('assignee_id')->nullable();
            $table->string('name')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('vehicle_details')->nullable();
            $table->dateTime('start_time');
            $table->dateTime('end_time')->nullable();
            $table->string('status')->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('parking_assignments');
    }
};
