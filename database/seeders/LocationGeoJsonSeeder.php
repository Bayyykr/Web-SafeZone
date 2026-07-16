<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Location;
use Illuminate\Support\Facades\File;

class LocationGeoJsonSeeder extends Seeder
{
    public function run()
    {
        // Path to the geojson file
        $path = public_path('data/lumajang_kecamatan.geojson');
        
        if (!File::exists($path)) {
            $this->command->error("File GeoJSON tidak ditemukan di: {$path}");
            return;
        }

        $json = File::get($path);
        $data = json_decode($json, true);

        if (!isset($data['features'])) {
            $this->command->error("Struktur GeoJSON tidak valid.");
            return;
        }

        foreach ($data['features'] as $feature) {
            $namaKecamatan = $feature['properties']['NAMOBJ'] ?? null; 
            
            if (!$namaKecamatan) {
                continue;
            }
            
            // Search location by name
            $location = Location::where('nama_lokasi', 'LIKE', "%{$namaKecamatan}%")->first();
            
            if (!$location) {
                // If not found, create it
                $location = new Location();
                $location->nama_lokasi = $namaKecamatan;
                $location->status_kerawanan = 'Aman'; // Default
            }

            // Calculate centroid
            $centroid = $this->getCentroid($feature['geometry']);
            $location->latitude = $centroid['lat'];
            $location->longitude = $centroid['lng'];

            $location->polygon_geojson = json_encode($feature['geometry']);
            $location->save();
            $this->command->info("Updated/Created: {$namaKecamatan} (Lat: {$centroid['lat']}, Lng: {$centroid['lng']})");
        }
    }

    private function getCentroid($geometry) {
        $coords = [];
        if ($geometry['type'] === 'Polygon') {
            $coords = $geometry['coordinates'][0];
        } elseif ($geometry['type'] === 'MultiPolygon') {
            foreach ($geometry['coordinates'] as $polygon) {
                foreach ($polygon[0] as $point) {
                    $coords[] = $point;
                }
            }
        }

        $sumLat = 0; $sumLng = 0; $count = count($coords);
        if ($count === 0) return ['lat' => 0, 'lng' => 0];

        foreach ($coords as $point) {
            $sumLng += $point[0]; // lng
            $sumLat += $point[1]; // lat
        }
        return ['lat' => $sumLat / $count, 'lng' => $sumLng / $count];
    }
}
