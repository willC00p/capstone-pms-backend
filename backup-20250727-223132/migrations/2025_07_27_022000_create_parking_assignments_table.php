<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('parking_assignments')) {
            Schema::create('parking_assignments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('parking_slot_id')->constrained('parking_slots')->onDelete('cascade');
                $table->string('guest_name')->nullable();
                $table->string('guest_contact')->nullable();
                $table->string('vehicle_type')->nullable();
                $table->string('vehicle_plate');
                $table->enum('assignment_type', ['assign', 'reserve'])->default('assign');
                $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');
                $table->dateTime('start_time');
                $table->dateTime('end_time')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
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
    }

    public function down()
    {
        Schema::dropIfExists('parking_assignments');
    }
};
