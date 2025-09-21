<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('parking_layouts')) {
            Schema::create('parking_layouts', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('background_image')->nullable();
                $table->json('layout_data')->nullable();
                $table->timestamps();
            });
        } else {
            Schema::table('parking_layouts', function (Blueprint $table) {
                if (!Schema::hasColumn('parking_layouts', 'background_image')) {
                    $table->string('background_image')->nullable();
                }
                if (!Schema::hasColumn('parking_layouts', 'layout_data')) {
                    $table->json('layout_data')->nullable();
                }
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('parking_layouts');
    }
};
