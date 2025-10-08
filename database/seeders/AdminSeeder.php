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
        // Create admin role if it doesn't exist (canonical 'Admin')
        $adminRole = \App\Models\Role::firstOrCreate(
            ['name' => 'Admin'],
            ['description' => 'Administrator']
        );

        // Create admin user (use .env override if provided)
        $email = env('ADMIN_EMAIL', 'admin@admin.com');
        $password = env('ADMIN_PASSWORD', 'admin123');

        // Remove any existing user with this email to ensure deterministic seeding
        \App\Models\User::where('email', $email)->delete();

        $admin = \App\Models\User::create([
            'roles_id' => $adminRole->id,
            'name' => 'Admin',
            'email' => $email,
            'password' => bcrypt($password)
        ]);

        // ensure user details row exists
        $admin->userDetail()->create([
            'user_id' => $admin->id,
            'firstname' => 'Admin',
            'lastname' => '',
            'from_pending' => false,
        ]);
    }
}
