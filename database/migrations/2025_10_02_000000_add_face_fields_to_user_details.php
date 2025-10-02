<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('user_details')) {
            Schema::table('user_details', function (Blueprint $table) {
                if (!Schema::hasColumn('user_details', 'selfie_path')) {
                    $table->string('selfie_path')->nullable()->after('id_path');
                }
                if (!Schema::hasColumn('user_details', 'face_score')) {
                    $table->double('face_score', 8, 4)->nullable()->after('selfie_path');
                }
                if (!Schema::hasColumn('user_details', 'face_verified')) {
                    $table->boolean('face_verified')->default(false)->after('face_score');
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
        if (Schema::hasTable('user_details')) {
            Schema::table('user_details', function (Blueprint $table) {
                if (Schema::hasColumn('user_details', 'face_verified')) {
                    $table->dropColumn('face_verified');
                }
                if (Schema::hasColumn('user_details', 'face_score')) {
                    $table->dropColumn('face_score');
                }
                if (Schema::hasColumn('user_details', 'selfie_path')) {
                    $table->dropColumn('selfie_path');
                }
            });
        }
    }
};
