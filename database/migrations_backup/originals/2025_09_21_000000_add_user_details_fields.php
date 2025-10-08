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
        if (!Schema::hasTable('user_details')) {
            Schema::create('user_details', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('firstname')->nullable();
                $table->string('middlename')->nullable();
                $table->string('lastname')->nullable();
                $table->string('email')->nullable();
                $table->string('dob')->nullable();
                $table->string('gender')->nullable();
                $table->string('civil_status')->nullable();
                $table->string('nationality')->nullable();
                $table->string('religion')->nullable();
                $table->string('place_of_birth')->nullable();
                $table->text('address')->nullable();
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
                $table->integer('no_of_children')->nullable();
                $table->string('source_of_income')->nullable();
                $table->string('work_description')->nullable();
                $table->string('id_card_presented')->nullable();
                $table->timestamp('membership_date')->nullable();
                $table->string('profile_photo_path')->nullable();
                // added for account management
                $table->string('department')->nullable();
                $table->string('contact_number')->nullable();
                $table->string('plate_number')->nullable();
                $table->string('student_no')->nullable();
                $table->string('course')->nullable();
                $table->string('yr_section')->nullable();
                $table->string('position')->nullable();
                $table->string('or_cr_path')->nullable();
                $table->boolean('from_pending')->default(false);
                $table->timestamps();
            });
        } else {
            Schema::table('user_details', function (Blueprint $table) {
                if (!Schema::hasColumn('user_details', 'department')) {
                    $table->string('department')->nullable();
                }
                if (!Schema::hasColumn('user_details', 'contact_number')) {
                    $table->string('contact_number')->nullable();
                }
                if (!Schema::hasColumn('user_details', 'plate_number')) {
                    $table->string('plate_number')->nullable();
                }

                // student specific
                if (!Schema::hasColumn('user_details', 'student_no')) {
                    $table->string('student_no')->nullable();
                }
                if (!Schema::hasColumn('user_details', 'course')) {
                    $table->string('course')->nullable();
                }
                if (!Schema::hasColumn('user_details', 'yr_section')) {
                    $table->string('yr_section')->nullable();
                }

                // faculty / employee specific
                if (!Schema::hasColumn('user_details', 'position')) {
                    $table->string('position')->nullable();
                }
                if (!Schema::hasColumn('user_details', 'or_cr_path')) {
                    $table->string('or_cr_path')->nullable();
                }

                // guard: link to pending id if applicable
                if (!Schema::hasColumn('user_details', 'from_pending')) {
                    $table->boolean('from_pending')->default(false);
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_details', function (Blueprint $table) {
            $cols = [
                'department','contact_number','plate_number','student_no','course','yr_section','position','or_cr_path','from_pending'
            ];
            foreach ($cols as $c) {
                if (Schema::hasColumn('user_details', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
    }
};
