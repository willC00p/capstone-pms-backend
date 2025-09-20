<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        // Create or update admin user (idempotent)
        $adminData = [
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make('admin123'),
            'created_at' => now(),
            'updated_at' => now()
        ];

        DB::table('users')->updateOrInsert(
            ['email' => 'admin@admin.com'],
            $adminData
        );

        // Also create the requested admin user for Edward (idempotent)
        $edwardData = [
            'name' => 'Edward Layno',
            'email' => 'waynelamarca720@gmail.com',
            'password' => Hash::make('admin1234'),
            'created_at' => now(),
            'updated_at' => now()
        ];

        DB::table('users')->updateOrInsert(
            ['email' => 'waynelamarca720@gmail.com'],
            $edwardData
        );

        // Feedback for the seeder run
        echo "Admin users (admin@admin.com, waynelamarca720@gmail.com) created or updated.\n";
    }
}
