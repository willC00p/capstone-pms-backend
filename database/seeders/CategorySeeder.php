<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('categories')->insert([
            ['code' => 'DKP', 'name' => "DEESH PREMIUM", 'attribute' => "Kalamansi"],
            ['code' => 'DLP', 'name' => "DEESH PREMIUM", 'attribute' => "Lemon"],
            ['code' => 'DUP', 'name' => "DEESH PREMIUM", 'attribute' => "Unscented"],

            ['code' => 'DKE', 'name' => "DEESH ECONO", 'attribute' => "Kalamansi"],
            ['code' => 'DLE', 'name' => "DEESH ECONO", 'attribute' => "Lemon"],
            ['code' => 'DUE', 'name' => "DEESH ECONO", 'attribute' => "Unscented"],
            
            ['code' => 'SLD', 'name' => "SWITCH LIQUID DETERGENT", 'attribute' => "High Efficiency"],

            ['code' => 'LG-DB', 'name' => "LANZ GENTLE", 'attribute' => "Blue(DAINTY)"],
            ['code' => 'LG-MV', 'name' => "LANZ GENTLE", 'attribute' => "Violiet(MYSTIFY)"],
            ['code' => 'LG-BP', 'name' => "LANZ GENTLE", 'attribute' => "Pink(BLOSSOM)"],
            
            ['code' => 'L3XP-DB', 'name' => "LANZ 3X", 'attribute' => "Blue(DAINTY)"],
            ['code' => 'L3XV-MV', 'name' => "LANZ 3X", 'attribute' => "Violiet(MYSTIFY)"],
            ['code' => 'L3XP-BP', 'name' => "LANZ 3X", 'attribute' => "Pink(BLOSSOM)"],

            ['code' => 'HHS-GBB', 'name' => "HUSH HANDSOAP", 'attribute' => 'G-APPLE'],
            ['code' => 'HHS-PBB', 'name' => "HUSH HANDSOAP", 'attribute' => 'P-Strawberry'],

            ['code' => 'VTC', 'name' => "VOROX LIQUID TOILET", 'attribute' => 'Regular'],
            ['code' => 'VTC', 'name' => "VOROX LIQUID TOILET", 'attribute' => 'Regular'],

            ['code' => 'CCS', 'name' => "CARZ CAR SHAMPOO", 'attribute' => 'PINK'],
        ]);
    }
}
