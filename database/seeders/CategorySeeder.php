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
            ['name' => "DEESH PREMIUM", 'attribute' => "Kalamansi"],
            ['name' => "DEESH PREMIUM", 'attribute' => "Lemon"],
            ['name' => "DEESH PREMIUM", 'attribute' => "Unscented"],

            ['name' => "DEESH ECONO", 'attribute' => "Kalamansi"],
            ['name' => "DEESH ECONO", 'attribute' => "Lemon"],
            ['name' => "DEESH ECONO", 'attribute' => "Unscented"],
            
            ['name' => "SWITCH LIQUID DETERGENT", 'attribute' => "High Efficiency"],

            ['name' => "LANZ GENTLE", 'attribute' => "Blue(DAINTY)"],
            ['name' => "LANZ GENTLE", 'attribute' => "Violiet(MYSTIFY)"],
            ['name' => "LANZ GENTLE", 'attribute' => "Pink(BLOSSOM)"],
            
            ['name' => "LANZ 3X", 'attribute' => "Blue(DAINTY)"],
            ['name' => "LANZ 3X", 'attribute' => "Violiet(MYSTIFY)"],
            ['name' => "LANZ 3X", 'attribute' => "Pink(BLOSSOM)"],

            ['name' => "VOROX LIQUID BLEACH", 'attribute' => 'Regular'],
        ]);
    }
}
