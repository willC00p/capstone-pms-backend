<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('user_details')) {
            Schema::table('user_details', function (Blueprint $table) {
                if (!Schema::hasColumn('user_details', 'faculty_id')) {
                    $table->string('faculty_id')->nullable()->after('student_no');
                }
                if (!Schema::hasColumn('user_details', 'employee_id')) {
                    $table->string('employee_id')->nullable()->after('faculty_id');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('user_details')) {
            Schema::table('user_details', function (Blueprint $table) {
                if (Schema::hasColumn('user_details', 'faculty_id')) {
                    $table->dropColumn('faculty_id');
                }
                if (Schema::hasColumn('user_details', 'employee_id')) {
                    $table->dropColumn('employee_id');
                }
            });
        }
    }
};
