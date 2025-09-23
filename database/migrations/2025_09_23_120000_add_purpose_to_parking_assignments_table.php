<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPurposeToParkingAssignmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('parking_assignments')) {
            Schema::table('parking_assignments', function (Blueprint $table) {
                // Ensure faculty_position exists (some installs may be missing it)
                if (!Schema::hasColumn('parking_assignments', 'faculty_position')) {
                    // place after guest_contact if present, otherwise just add
                    if (Schema::hasColumn('parking_assignments', 'guest_contact')) {
                        $table->string('faculty_position')->nullable()->after('guest_contact');
                    } else {
                        $table->string('faculty_position')->nullable();
                    }
                }

                // Then add purpose after faculty_position
                if (!Schema::hasColumn('parking_assignments', 'purpose')) {
                    if (Schema::hasColumn('parking_assignments', 'faculty_position')) {
                        $table->string('purpose')->nullable()->after('faculty_position');
                    } else {
                        $table->string('purpose')->nullable();
                    }
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('parking_assignments')) {
            Schema::table('parking_assignments', function (Blueprint $table) {
                if (Schema::hasColumn('parking_assignments', 'purpose')) {
                    $table->dropColumn('purpose');
                }
            });
        }
    }
}
