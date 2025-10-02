<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('user_details')) {
            Schema::table('user_details', function (Blueprint $table) {
                if (!Schema::hasColumn('user_details', 'is_second_hand')) {
                    $table->boolean('is_second_hand')->default(false)->after('face_verified');
                }
                if (!Schema::hasColumn('user_details', 'deed_path')) {
                    $table->string('deed_path')->nullable()->after('is_second_hand');
                }
                if (!Schema::hasColumn('user_details', 'deed_name')) {
                    $table->string('deed_name')->nullable()->after('deed_path');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('user_details')) {
            Schema::table('user_details', function (Blueprint $table) {
                if (Schema::hasColumn('user_details', 'deed_name')) {
                    $table->dropColumn('deed_name');
                }
                if (Schema::hasColumn('user_details', 'deed_path')) {
                    $table->dropColumn('deed_path');
                }
                if (Schema::hasColumn('user_details', 'is_second_hand')) {
                    $table->dropColumn('is_second_hand');
                }
            });
        }
    }
};
