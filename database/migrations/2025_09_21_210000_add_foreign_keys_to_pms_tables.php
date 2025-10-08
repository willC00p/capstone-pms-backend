<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddForeignKeysToPmsTables extends Migration
{
    public function up()
    {
        $db = env('DB_DATABASE');

        // helper to check if a constraint exists in this schema
        $exists = function($name) use ($db) {
            $row = DB::select("SELECT COUNT(*) as c FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = ? AND CONSTRAINT_NAME = ?", [$db, $name]);
            return intval($row[0]->c ?? 0) > 0;
        };

        // parking_slots.layout_id -> parking_layouts.id
        if (! $exists('parking_slots_layout_id_foreign')) {
            DB::statement("ALTER TABLE `parking_slots` ADD CONSTRAINT `parking_slots_layout_id_foreign` FOREIGN KEY (`layout_id`) REFERENCES `parking_layouts` (`id`) ON DELETE CASCADE");
        }

        // parking_assignments.parking_slot_id -> parking_slots.id
        if (! $exists('parking_assignments_parking_slot_id_foreign')) {
            DB::statement("ALTER TABLE `parking_assignments` ADD CONSTRAINT `parking_assignments_parking_slot_id_foreign` FOREIGN KEY (`parking_slot_id`) REFERENCES `parking_slots` (`id`) ON DELETE CASCADE");
        }

        // vehicles.user_details_id -> user_details.id
        if (! $exists('vehicles_user_details_id_foreign')) {
            DB::statement("ALTER TABLE `vehicles` ADD CONSTRAINT `vehicles_user_details_id_foreign` FOREIGN KEY (`user_details_id`) REFERENCES `user_details` (`id`) ON DELETE SET NULL");
        }

        // vehicles.user_id -> users.id
        if (! $exists('vehicles_user_id_foreign')) {
            DB::statement("ALTER TABLE `vehicles` ADD CONSTRAINT `vehicles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL");
        }
    }

    public function down()
    {
        $db = env('DB_DATABASE');
        $exists = function($name) use ($db) {
            $row = DB::select("SELECT COUNT(*) as c FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = ? AND CONSTRAINT_NAME = ?", [$db, $name]);
            return intval($row[0]->c ?? 0) > 0;
        };

        if ($exists('parking_assignments_parking_slot_id_foreign')) {
            DB::statement("ALTER TABLE `parking_assignments` DROP FOREIGN KEY `parking_assignments_parking_slot_id_foreign`");
        }
        if ($exists('parking_slots_layout_id_foreign')) {
            DB::statement("ALTER TABLE `parking_slots` DROP FOREIGN KEY `parking_slots_layout_id_foreign`");
        }
        if ($exists('vehicles_user_details_id_foreign')) {
            DB::statement("ALTER TABLE `vehicles` DROP FOREIGN KEY `vehicles_user_details_id_foreign`");
        }
        if ($exists('vehicles_user_id_foreign')) {
            DB::statement("ALTER TABLE `vehicles` DROP FOREIGN KEY `vehicles_user_id_foreign`");
        }
    }
}
