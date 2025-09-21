<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('parking_assignments', function (Blueprint $table) {
            $table->string('faculty_position')->nullable()->after('guest_contact');
            $table->dropColumn('notes'); // Removing notes field since it's not needed for faculty
            $table->text('purpose')->nullable()->after('faculty_position'); // Renaming notes to purpose for clarity
        });
    }

    public function down()
    {
        Schema::table('parking_assignments', function (Blueprint $table) {
            $table->dropColumn('faculty_position');
            $table->text('notes')->nullable();
            $table->dropColumn('purpose');
        });
    }
};
