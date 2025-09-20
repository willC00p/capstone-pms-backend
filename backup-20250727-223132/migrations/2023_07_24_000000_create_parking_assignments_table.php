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
            $table->foreignId('parking_slot_id')->constrained('parking_slots')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('guest_name')->nullable();
            $table->string('guest_contact')->nullable();
            $table->string('vehicle_plate')->nullable();
            $table->timestamp('start_time');
            $table->timestamp('end_time')->nullable();
            $table->string('status')->default('active'); // active, completed, cancelled
            $table->text('notes')->nullable();
            $table->timestamps();

            // Ensure a parking slot can only have one active assignment
            $table->unique(['parking_slot_id', 'status']);
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
