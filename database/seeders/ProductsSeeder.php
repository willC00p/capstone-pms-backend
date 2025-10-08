<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class ProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('products')->insert([
            [
                'category_id' => 1,
                'name' => 'Carbouy (20 Liters)',
                'price' => 600.00
            ],
            [
                'category_id' => 1,
                'name' => 'Refill per Liter',
                'price' => 30.00
            ],
            [
                'category_id' => 1,
                'name' => '3.4 L Gallon',
                'price' => 145.00
            ],
            [
                'category_id' => 1,
                'name' => '250 ML Bottle',
                'price' => 35.00
            ],
            [
                'category_id' => 1,
                'name' => '1 L Bottle',
                'price' => 50.00
            ],
            // category 2
            [
                'category_id' => 4,
                'name' => 'Carbouy (20 Liters)',
                'price' => 500.00
            ],
            [
                'category_id' => 4,
                'name' => 'Refill per Liter',
                'price' => 25.00
            ],
            [
                'category_id' => 4,
                'name' => '3.4 L Gallon',
                'price' => 125.00
            ],
            [
                'category_id' => 4,
                'name' => '250 ML Bottle',
                'price' => 40.00
            ],
            // category 3
            [
                'category_id' => 7,
                'name' => 'Carbouy (20 Liters)',
                'price' => 1000.00
            ],
            [
                'category_id' => 7,
                'name' => 'Refill per Liter',
                'price' => 50.00
            ],
            [
                'category_id' => 7,
                'name' => '3.4 L Gallon',
                'price' => 210.00
            ],
            [
                'category_id' => 7,
                'name' => '1 L Pet Bottle',
                'price' => 65.00
            ],
            [
                'category_id' => 7,
                'name' => '1 L Premium Handy Bottle',
                'price' => 125.00
            ],
            // category 4
            [
                'category_id' => 8,
                'name' => 'Carbouy (20 Liters)',
                'price' => 960.00
            ],
            [
                'category_id' => 8,
                'name' => 'Refill per Liter',
                'price' => 48.00
            ],
            [
                'category_id' => 8,
                'name' => '3.4 L Gallon',
                'price' => 170.00
            ],
            [
                'category_id' => 8,
                'name' => '1 L Pet Bottle',
                'price' => 60.00
            ],
            // category 5
            [
                'category_id' => 11,
                'name' => 'Carbouy (20 Liters)',
                'price' => 1400.00
            ],
            [
                'category_id' => 11,
                'name' => 'Refill per Liter',
                'price' => 70.00
            ],
            [
                'category_id' => 11,
                'name' => '3.4 L Gallon',
                'price' => 270.00
            ],
            [
                'category_id' => 11,
                'name' => '1 L Handy Bottle',
                'price' => 135.00
            ],
            // category 6
            [
                'category_id' => 11,
                'name' => 'Refill per Liter',
                'price' => 30.00
            ],
            [
                'category_id' => 11,
                'name' => '3.4 L Gallon',
                'price' => 270.00
            ],
            [
                'category_id' => 14,
                'name' => '1L Neck Bottle',
                'price' => 50.00
            ],
        ]);
    }
}
