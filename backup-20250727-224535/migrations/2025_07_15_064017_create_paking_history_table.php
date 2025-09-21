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
        Schema::create('paking_history', function (Blueprint $table) {
            $table->id();
            $table->integer('vehicle_id');
            $table->string('parking_code');
            $table->dateTime('enrty_date_time');
            $table->dateTime('exit_date_time')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paking_history');
    }
};
