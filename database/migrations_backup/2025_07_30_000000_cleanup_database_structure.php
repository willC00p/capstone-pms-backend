<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Drop all existing tables in reverse order to avoid foreign key constraints
        Schema::dropIfExists('parking_assignments');
        Schema::dropIfExists('parking_slots');
        Schema::dropIfExists('parking_layouts');
        Schema::dropIfExists('vehicle');
        Schema::dropIfExists('team_users');
        Schema::dropIfExists('teams');
        Schema::dropIfExists('user_details');
        Schema::dropIfExists('incident_report');
        Schema::dropIfExists('qr_code');
        Schema::dropIfExists('feedback_code');
        Schema::dropIfExists('guest');
        Schema::dropIfExists('driver');
        Schema::dropIfExists('admin');
        Schema::dropIfExists('user_info');
        
        // Create tables in correct order
        
        // Roles table
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Users table with role
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'role_id')) {
                $table->foreignId('role_id')->nullable()->constrained('roles')->onDelete('set null');
            }
            if (!Schema::hasColumn('users', 'contact_number')) {
                $table->string('contact_number')->nullable();
            }
            if (!Schema::hasColumn('users', 'address')) {
                $table->string('address')->nullable();
            }
        });

        // User Details
        Schema::create('user_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('department')->nullable();
            $table->string('position')->nullable();
            $table->string('employee_id')->nullable();
            $table->timestamps();
        });

        // Teams
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Team Users
        Schema::create('team_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });

        // Vehicles
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('plate_number');
            $table->string('vehicle_type');
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('color')->nullable();
            $table->timestamps();
        });

        // Parking Layouts
        Schema::create('parking_layouts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('layout_data')->nullable();
            $table->timestamps();
        });

        // Parking Slots
        Schema::create('parking_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('layout_id')->constrained('parking_layouts')->onDelete('cascade');
            $table->string('space_number');
            $table->string('space_type');
            $table->string('space_status')->default('available');
            $table->decimal('position_x', 10, 2);
            $table->decimal('position_y', 10, 2);
            $table->decimal('width', 10, 2);
            $table->decimal('height', 10, 2);
            $table->decimal('rotation', 10, 2)->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        // Parking Assignments
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

        // QR Codes
        Schema::create('qr_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parking_assignment_id')->constrained()->onDelete('cascade');
            $table->string('code');
            $table->datetime('valid_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Incident Reports
        Schema::create('incident_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('parking_slot_id')->nullable()->constrained()->onDelete('set null');
            $table->string('incident_type');
            $table->text('description');
            $table->string('status')->default('pending');
            $table->datetime('incident_time');
            $table->timestamps();
        });

        // Feedback
        Schema::create('feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->text('content');
            $table->integer('rating')->nullable();
            $table->string('category')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    public function down()
    {
        // Drop all tables in reverse order
        Schema::dropIfExists('feedback');
        Schema::dropIfExists('incident_reports');
        Schema::dropIfExists('qr_codes');
        Schema::dropIfExists('parking_assignments');
        Schema::dropIfExists('parking_slots');
        Schema::dropIfExists('parking_layouts');
        Schema::dropIfExists('vehicles');
        Schema::dropIfExists('team_users');
        Schema::dropIfExists('teams');
        Schema::dropIfExists('user_details');
        
        // Remove columns from users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('role_id');
            $table->dropColumn('contact_number');
            $table->dropColumn('address');
        });
        
        Schema::dropIfExists('roles');
    }
};
