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
        if (!Schema::hasTable('parking_assignments')) {
            Schema::create('parking_assignments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('parking_slot_id')->constrained('parking_slots')->onDelete('cascade');
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
                $table->string('guest_name')->nullable();
                $table->string('guest_contact')->nullable();
                $table->string('vehicle_plate')->nullable();
                $table->string('vehicle_type')->nullable();
                $table->string('vehicle_color')->nullable();
                $table->dateTime('start_time');
                $table->dateTime('end_time')->nullable();
                $table->string('status')->default('active');
                $table->string('assignment_type')->default('assign');
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parking_assignments', function (Blueprint $table) {
            if (Schema::hasColumn('parking_assignments', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
            if (Schema::hasColumn('parking_assignments', 'guest_name')) {
                $table->dropColumn('guest_name');
            }
            if (Schema::hasColumn('parking_assignments', 'guest_contact')) {
                $table->dropColumn('guest_contact');
            }
            if (Schema::hasColumn('parking_assignments', 'vehicle_plate')) {
                $table->dropColumn('vehicle_plate');
            }
            if (Schema::hasColumn('parking_assignments', 'vehicle_type')) {
                $table->dropColumn('vehicle_type');
            }
            if (Schema::hasColumn('parking_assignments', 'vehicle_color')) {
                $table->dropColumn('vehicle_color');
            }
            if (Schema::hasColumn('parking_assignments', 'start_time')) {
                $table->dropColumn('start_time');
            }
            if (Schema::hasColumn('parking_assignments', 'end_time')) {
                $table->dropColumn('end_time');
            }
            if (Schema::hasColumn('parking_assignments', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('parking_assignments', 'assignment_type')) {
                $table->dropColumn('assignment_type');
            }
            if (Schema::hasColumn('parking_assignments', 'notes')) {
                $table->dropColumn('notes');
            }
        });
    }
};
