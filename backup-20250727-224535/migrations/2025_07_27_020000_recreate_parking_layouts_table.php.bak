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
        // First drop tables with foreign key constraints
        Schema::dropIfExists('parking_assignments');
        Schema::dropIfExists('parking_slots');
        Schema::dropIfExists('parking_layouts');

        // Now create the new parking_layouts table
        Schema::create('parking_layouts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('background_image')->nullable();
            $table->json('layout_data')->nullable();
            $table->timestamps();
        });
        Schema::create('parking_layouts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('background_image')->nullable();
            $table->json('layout_data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parking_layouts');
    }
};
