<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('user_details', 'middlename')) {
            Schema::table('user_details', function (Blueprint $table) {
                $table->string('middlename')->nullable()->after('firstname');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('user_details', 'middlename')) {
            Schema::table('user_details', function (Blueprint $table) {
                $table->dropColumn('middlename');
            });
        }
    }
};
