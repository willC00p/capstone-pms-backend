<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin role if it doesn't exist
        $adminRole = \App\Models\Role::firstOrCreate(
            ['name' => 'admin'],
            ['description' => 'Administrator']
        );

        // Create admin user
        \App\Models\User::create([
            'roles_id' => $adminRole->id,
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => bcrypt('admin123')
        ]);
    }
}
