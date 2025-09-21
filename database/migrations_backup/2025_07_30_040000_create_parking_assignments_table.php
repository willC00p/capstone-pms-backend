<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateParkingAssignmentsTable extends Migration
{
    public function up()
    {
        Schema::create('parking_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parking_slot_id')->constrained('parking_slots')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('guest_name')->nullable();
            $table->string('guest_contact')->nullable();
            $table->string('vehicle_plate')->nullable();
            $table->string('vehicle_type')->nullable();
            $table->dateTime('start_time');
            $table->dateTime('end_time')->nullable();
            $table->string('status')->default('active'); // active, completed, cancelled
            $table->string('type')->default('assignment'); // assignment, reservation
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('parking_assignments');
    }
}
