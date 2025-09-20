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
        // Drop all tables in correct order to avoid foreign key constraints
        Schema::dropIfExists('parking_assignments');
        Schema::dropIfExists('parking_slots');
        Schema::dropIfExists('parking_layouts');
        Schema::dropIfExists('team_users');
        Schema::dropIfExists('teams');
        Schema::dropIfExists('user_details');
        Schema::dropIfExists('driver');
        Schema::dropIfExists('admin');
        Schema::dropIfExists('user_info');
        Schema::dropIfExists('vehicle');
        Schema::dropIfExists('paking_history');
        Schema::dropIfExists('qr_code');
        Schema::dropIfExists('feedback_code');
        Schema::dropIfExists('incident_report');
        Schema::dropIfExists('guest');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('personal_access_tokens');
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('password_resets');
        Schema::dropIfExists('users');

        // Create parking_layouts table
        Schema::create('parking_layouts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('background_image')->nullable();
            $table->json('layout_data')->nullable();
            $table->timestamps();
        });

        // Create parking_slots table
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
            $table->float('rotation')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        // Create parking_assignments table
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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop all tables in reverse order
        Schema::dropIfExists('parking_assignments');
        Schema::dropIfExists('parking_slots');
        Schema::dropIfExists('parking_layouts');
        Schema::dropIfExists('team_users');
        Schema::dropIfExists('teams');
        Schema::dropIfExists('user_details');
        Schema::dropIfExists('driver');
        Schema::dropIfExists('admin');
        Schema::dropIfExists('user_info');
        Schema::dropIfExists('vehicle');
        Schema::dropIfExists('paking_history');
        Schema::dropIfExists('qr_code');
        Schema::dropIfExists('feedback_code');
        Schema::dropIfExists('incident_report');
        Schema::dropIfExists('guest');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('personal_access_tokens');
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('password_resets');
        Schema::dropIfExists('users');
    }
};
