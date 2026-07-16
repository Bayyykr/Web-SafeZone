<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\Polsek;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locations = [
            [
                "nama_lokasi" => "Lumajang (Kota)",
                "latitude" => -8.1331,
                "longitude" => 113.2224,
                "status_kerawanan" => "Rawan",
            ],
            [
                "nama_lokasi" => "Sumbersuko",
                "latitude" => -8.1636,
                "longitude" => 113.2031,
                "status_kerawanan" => "Rawan",
            ],
            [
                "nama_lokasi" => "Kunir",
                "latitude" => -8.2125,
                "longitude" => 113.2662,
                "status_kerawanan" => "Sangat Rawan",
            ],
            [
                "nama_lokasi" => "Yosowilangun",
                "latitude" => -8.2044,
                "longitude" => 113.3194,
                "status_kerawanan" => "Rawan",
            ],
            [
                "nama_lokasi" => "Sukodono",
                "latitude" => -8.1122,
                "longitude" => 113.2318,
                "status_kerawanan" => "Aman",
            ],
            [
                "nama_lokasi" => "Pasirian",
                "latitude" => -8.2157,
                "longitude" => 113.1152,
                "status_kerawanan" => "Rawan",
            ],
            [
                "nama_lokasi" => "Candipuro",
                "latitude" => -8.1885,
                "longitude" => 113.0512,
                "status_kerawanan" => "Aman",
            ],
        ];

        foreach ($locations as $location) {
            Location::updateOrCreate(
                ["nama_lokasi" => $location["nama_lokasi"]],
                $location,
            );
        }

        // Map locations to Polseks
        $polsekLocationMap = [
            "Polsek Lumajang Kota" => "Lumajang (Kota)",
            "Polsek Sumbersuko" => "Sumbersuko",
            "Polsek Sukodono" => "Sukodono",
            "Polsek Pasirian" => "Pasirian",
            "Polsek Candipuro" => "Candipuro",
            "Polsek Yosowilangun" => "Yosowilangun",
        ];

        foreach ($polsekLocationMap as $polsekName => $locationName) {
            $location = Location::where("nama_lokasi", $locationName)->first();

            if ($location) {
                Polsek::where("nama", $polsekName)->update([
                    "lokasi_id" => $location->id,
                ]);
            }
        }
    }
}
