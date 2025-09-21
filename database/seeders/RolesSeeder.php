<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Remove any existing roles and seed only the canonical roles used by the application
        DB::table('roles')->truncate();
        DB::table('roles')->insert([
            ['name' => 'Admin'],
            ['name' => 'Student'],
            ['name' => 'Faculty'],
            ['name' => 'Employee'],
            ['name' => 'Guard'],
        ]);
    }
}
