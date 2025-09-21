<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UserDetails;
use App\Models\Vehicle;
use Illuminate\Support\Str;

class VehiclesFromUserDetailsSeeder extends Seeder
{
    public function run(): void
    {
        $all = UserDetails::all();
        foreach ($all as $ud) {
            $plates = [];
            if (!empty($ud->plate_number)) $plates[] = $ud->plate_number;
            if (!empty($ud->plate_numbers) && is_array($ud->plate_numbers)) $plates = array_merge($plates, $ud->plate_numbers);

            foreach ($plates as $p) {
                if (empty($p)) continue;

                // don't duplicate
                $exists = Vehicle::where('plate_number', $p)->first();
                if ($exists) continue;

                Vehicle::create([
                    'user_id' => $ud->user_id,
                    'user_details_id' => $ud->id,
                    'plate_number' => $p,
                    'vehicle_color' => 'Unknown',
                    'vehicle_type' => 'Unknown',
                    'brand' => null,
                    'model' => null,
                ]);
            }
        }

        $this->command->info('Vehicles generated from user_details plate numbers.');
    }
}
