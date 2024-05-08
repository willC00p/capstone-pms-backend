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
        Schema::create('user_details', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('firstname')->required();
            $table->string('middleinitial')->nullable();
            $table->string('lastname')->required();
            $table->date('dob')->nullable()->comment('Date of Birth');
            $table->string('gender')->nullable();
            $table->string('civil_status')->nullable();
            $table->string('nationality')->nullable();
            $table->string('religion')->nullable();
            $table->string('place_of_birth')->nullable();
            $table->string('address')->nullable();
            $table->string('municipality')->nullable();
            $table->string('provice')->nullable();
            $table->string('country')->nullable();
            $table->string('zip_code')->nullable();
            $table->string('fb_account_name')->nullable();
            $table->string('father_firstname')->nullable();
            $table->string('father_middleinitial')->nullable();
            $table->string('father_lastname')->nullable();
            $table->string('mother_firstname')->nullable();
            $table->string('mother_middleinitial')->nullable();
            $table->string('mother_lastname')->nullable();
            $table->string('spouse_firstname')->nullable();
            $table->string('spouse_middleinitial')->nullable();
            $table->string('spouse_lastname')->nullable();
            $table->text('no_of_children')->nullable();
            $table->string('source_of_income')->nullable();
            $table->string('work_description')->nullable();
            $table->string('id_card_presented')->nullable();
            $table->date('membership_date')->nullable();
            $table->string('profile_photo_path', 2048)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_details');
    }
};
