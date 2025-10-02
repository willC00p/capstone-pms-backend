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
        Schema::dropIfExists('parking_assignments');

        Schema::create('parking_assignments', function (Blueprint $table) {
            $table->id();
            // avoid creating a DB-level foreign key here because the `parking_slots` creation
            // lives in a backup migration file (not executed before this one). Some DB
            // environments produce errno 150 when the referenced table/column isn't present
            // or has mismatched definitions. Use an indexed unsignedBigInteger instead and
            // add a FK later (or restore the parking_slots migration to the main folder).
            $table->unsignedBigInteger('parking_slot_id')->nullable()->index();
            // define user_id as unsignedBigInteger nullable and index it instead of forcing a FK constraint
            // Some environments may fail to create this FK during migrations; keep it nullable and indexed.
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('guest_name')->nullable();
            $table->string('guest_contact')->nullable();
            $table->string('vehicle_plate');
            $table->string('vehicle_color');
            $table->string('vehicle_type');
            $table->dateTime('start_time');
            $table->dateTime('end_time')->nullable();
            $table->string('status')->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->string('assignee_type');
            $table->string('assignment_type');
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
