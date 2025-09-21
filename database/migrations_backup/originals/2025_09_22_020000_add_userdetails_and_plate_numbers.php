<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            if (!Schema::hasColumn('vehicles', 'user_details_id')) {
                $table->unsignedBigInteger('user_details_id')->nullable()->after('user_id');
                $table->foreign('user_details_id')->references('id')->on('user_details')->nullOnDelete();
            }
        });

        Schema::table('user_details', function (Blueprint $table) {
            if (!Schema::hasColumn('user_details', 'plate_numbers')) {
                $table->json('plate_numbers')->nullable()->after('plate_number');
            }
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            if (Schema::hasColumn('vehicles', 'user_details_id')) {
                $table->dropForeign(['user_details_id']);
                $table->dropColumn('user_details_id');
            }
        });

        Schema::table('user_details', function (Blueprint $table) {
            if (Schema::hasColumn('user_details', 'plate_numbers')) {
                $table->dropColumn('plate_numbers');
            }
        });
    }
};
