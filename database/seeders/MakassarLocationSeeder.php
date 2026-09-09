<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Laporan;
use App\Models\Location;
use App\Models\Polsek;
use App\Models\User;
use Illuminate\Database\Seeder;

class MakassarLocationSeeder extends Seeder
{
    /**
     * Run the database seeds for Makassar City.
     */
    public function run(): void
    {
        // 1. Definisikan Data Wilayah & Geofence Polygon untuk Makassar
        $locations = [
            [
                "nama_lokasi" => "Makassar - Tamalanrea & Biringkanaya",
                "latitude" => -5.1384,
                "longitude" => 119.4932,
                "status_kerawanan" => "Rawan",
                "polygon_geojson" => json_encode([
                    "type" => "Polygon",
                    "coordinates" => [[
                        [119.4600, -5.1050],
                        [119.5350, -5.1050],
                        [119.5350, -5.1650],
                        [119.4600, -5.1650],
                        [119.4600, -5.1050],
                    ]],
                ]),
            ],
            [
                "nama_lokasi" => "Makassar - Panakkukang & Manggala",
                "latitude" => -5.1520,
                "longitude" => 119.4500,
                "status_kerawanan" => "Sangat Rawan",
                "polygon_geojson" => json_encode([
                    "type" => "Polygon",
                    "coordinates" => [[
                        [119.4300, -5.1300],
                        [119.4850, -5.1300],
                        [119.4850, -5.1750],
                        [119.4300, -5.1750],
                        [119.4300, -5.1300],
                    ]],
                ]),
            ],
            [
                "nama_lokasi" => "Makassar - Ujung Pandang & Losari",
                "latitude" => -5.1400,
                "longitude" => 119.4080,
                "status_kerawanan" => "Rawan",
                "polygon_geojson" => json_encode([
                    "type" => "Polygon",
                    "coordinates" => [[
                        [119.3900, -5.1150],
                        [119.4300, -5.1150],
                        [119.4300, -5.1600],
                        [119.3900, -5.1600],
                        [119.3900, -5.1150],
                    ]],
                ]),
            ],
            [
                "nama_lokasi" => "Makassar - Rappocini",
                "latitude" => -5.1700,
                "longitude" => 119.4300,
                "status_kerawanan" => "Rawan",
                "polygon_geojson" => json_encode([
                    "type" => "Polygon",
                    "coordinates" => [[
                        [119.4150, -5.1550],
                        [119.4600, -5.1550],
                        [119.4600, -5.2000],
                        [119.4150, -5.2000],
                        [119.4150, -5.1550],
                    ]],
                ]),
            ],
            [
                "nama_lokasi" => "Makassar - Safe Zone (CPI & Losari Promenade)",
                "latitude" => -5.1485,
                "longitude" => 119.3980,
                "status_kerawanan" => "Aman",
                "polygon_geojson" => json_encode([
                    "type" => "Polygon",
                    "coordinates" => [[
                        [119.3850, -5.1400],
                        [119.4050, -5.1400],
                        [119.4050, -5.1620],
                        [119.3850, -5.1620],
                        [119.3850, -5.1400],
                    ]],
                ]),
            ],
            [
                "nama_lokasi" => "Makassar - Kawasan Metropolitan (Pusat Kota)",
                "latitude" => -5.1476,
                "longitude" => 119.4327,
                "status_kerawanan" => "Rawan",
                "polygon_geojson" => json_encode([
                    "type" => "Polygon",
                    "coordinates" => [[
                        [119.3600, -5.0600],
                        [119.5500, -5.0600],
                        [119.5500, -5.2400],
                        [119.3600, -5.2400],
                        [119.3600, -5.0600],
                    ]],
                ]),
            ],
        ];

        foreach ($locations as $locData) {
            Location::updateOrCreate(
                ["nama_lokasi" => $locData["nama_lokasi"]],
                $locData
            );
        }

        // 2. Definisikan Polsek di Wilayah Makassar
        $polseks = [
            [
                "nama" => "Polsek Tamalanrea",
                "lokasi_name" => "Makassar - Tamalanrea & Biringkanaya",
                "alamat" => "Jl. Perintis Kemerdekaan KM.11, Tamalanrea, Makassar",
                "telepon" => "(0411) 583500",
            ],
            [
                "nama" => "Polsek Panakkukang",
                "lokasi_name" => "Makassar - Panakkukang & Manggala",
                "alamat" => "Jl. Pengayoman No.19, Panakkukang, Makassar",
                "telepon" => "(0411) 441010",
            ],
            [
                "nama" => "Polsek Ujung Pandang",
                "lokasi_name" => "Makassar - Ujung Pandang & Losari",
                "alamat" => "Jl. Sultan Hasanuddin No.3, Ujung Pandang, Makassar",
                "telepon" => "(0411) 3624450",
            ],
            [
                "nama" => "Polsek Rappocini",
                "lokasi_name" => "Makassar - Rappocini",
                "alamat" => "Jl. Teduh Bersinar No.9, Rappocini, Makassar",
                "telepon" => "(0411) 867900",
            ],
            [
                "nama" => "Polsek Mariso",
                "lokasi_name" => "Makassar - Safe Zone (CPI & Losari Promenade)",
                "alamat" => "Jl. Mataram No.1, Mariso, Makassar",
                "telepon" => "(0411) 873110",
            ],
            [
                "nama" => "Polrestabes Makassar",
                "lokasi_name" => "Makassar - Kawasan Metropolitan (Pusat Kota)",
                "alamat" => "Jl. Ahmad Yani No.9, Makassar",
                "telepon" => "(0411) 314444",
            ],
        ];

        foreach ($polseks as $pData) {
            $location = Location::where("nama_lokasi", $pData["lokasi_name"])->first();

            Polsek::updateOrCreate(
                ["nama" => $pData["nama"]],
                [
                    "lokasi_id" => $location?->id,
                    "alamat" => $pData["alamat"],
                    "telepon" => $pData["telepon"],
                ]
            );
        }

        // 3. Tambahkan Sampel Laporan Kejahatan Terkonfirmasi di Makassar (untuk Safe Route & Statistik)
        $firstUser = User::first();
        $firstCategory = Category::first();
        $panakkukangLoc = Location::where("nama_lokasi", "Makassar - Panakkukang & Manggala")->first();
        $panakkukangPolsek = Polsek::where("nama", "Polsek Panakkukang")->first();

        if ($firstUser && $firstCategory && $panakkukangLoc && $panakkukangPolsek) {
            $sampleReports = [
                [
                    "judul_laporan" => "Pencurian Kendaraan Bermotor (Curanmor) di Parkiran Ruko",
                    "deskripsi" => "Terjadi pencurian sepeda motor matic di area parkir ruko Panakkukang saat malam hari.",
                    "latitude" => -5.1530,
                    "longitude" => 119.4490,
                ],
                [
                    "judul_laporan" => "Aksi Begal / Penjambretan Ponsel",
                    "deskripsi" => "Penjambretan pengendara motor oleh 2 pelaku tak dikenal di sekitar jalanan sepi.",
                    "latitude" => -5.1560,
                    "longitude" => 119.4520,
                ],
            ];

            foreach ($sampleReports as $report) {
                Laporan::updateOrCreate(
                    [
                        "judul_laporan" => $report["judul_laporan"],
                        "user_id" => $firstUser->id,
                    ],
                    [
                        "kategori_id" => $firstCategory->id,
                        "lokasi_id" => $panakkukangLoc->id,
                        "polsek_id" => $panakkukangPolsek->id,
                        "deskripsi" => $report["deskripsi"],
                        "latitude" => $report["latitude"],
                        "longitude" => $report["longitude"],
                        "status" => "dikonfirmasi",
                    ]
                );
            }
        }

        // 4. Definisikan Akun Login Petugas Polsek & Warga Makassar
        $makassarUsers = [
            [
                "name" => "Polsek Tamalanrea Admin",
                "username" => "admin.tamalanrea",
                "email" => "admin.tamalanrea@polri.go.id",
                "telepon" => "0411583500",
                "alamat" => "Polsek Tamalanrea, Jl. Perintis Kemerdekaan KM.11, Makassar",
                "role" => "admin",
                "aktif" => true,
                "password" => "Password123!",
            ],
            [
                "name" => "Polsek Panakkukang Admin",
                "username" => "admin.panakkukang",
                "email" => "admin.panakkukang@polri.go.id",
                "telepon" => "0411441010",
                "alamat" => "Polsek Panakkukang, Jl. Pengayoman No.19, Makassar",
                "role" => "admin",
                "aktif" => true,
                "password" => "Password123!",
            ],
            [
                "name" => "Polsek Ujung Pandang Admin",
                "username" => "admin.ujungpandang",
                "email" => "admin.ujungpandang@polri.go.id",
                "telepon" => "04113624450",
                "alamat" => "Polsek Ujung Pandang, Jl. Sultan Hasanuddin No.3, Makassar",
                "role" => "admin",
                "aktif" => true,
                "password" => "Password123!",
            ],
            [
                "name" => "Polsek Rappocini Admin",
                "username" => "admin.rappocini",
                "email" => "admin.rappocini@polri.go.id",
                "telepon" => "0411867900",
                "alamat" => "Polsek Rappocini, Jl. Teduh Bersinar No.9, Makassar",
                "role" => "admin",
                "aktif" => true,
                "password" => "Password123!",
            ],
            [
                "name" => "Polsek Mariso Admin",
                "username" => "admin.mariso",
                "email" => "admin.mariso@polri.go.id",
                "telepon" => "0411873110",
                "alamat" => "Polsek Mariso, Jl. Mataram No.1, Makassar",
                "role" => "admin",
                "aktif" => true,
                "password" => "Password123!",
            ],
            [
                "name" => "Command Center Polrestabes Makassar",
                "username" => "admin.polrestabes",
                "email" => "admin.polrestabes@polri.go.id",
                "telepon" => "0411314444",
                "alamat" => "Polrestabes Makassar, Jl. Ahmad Yani No.9, Makassar",
                "role" => "super_admin",
                "aktif" => true,
                "password" => "Password123!",
            ],
            [
                "name" => "Warga Makassar",
                "username" => "warga.makassar",
                "email" => "warga.makassar@gmail.com",
                "telepon" => "081244556677",
                "alamat" => "Tamalanrea, Makassar",
                "role" => "user",
                "aktif" => true,
                "password" => "Password123!",
            ],
        ];

        $firebaseService = app(\App\Services\FirebaseService::class);
        foreach ($makassarUsers as $mUser) {
            $plainPassword = $mUser["password"];
            unset($mUser["password"]);
            $dbUser = User::updateOrCreate(
                ["email" => $mUser["email"]],
                $mUser + ["password" => \Illuminate\Support\Facades\Hash::make($plainPassword)]
            );

            $uid = $firebaseService->createUserInFirebaseAuth($dbUser->email, $plainPassword, $dbUser->name);
            if ($uid) {
                $dbUser->update(["firebase_uid" => $uid]);
            } elseif (!$dbUser->firebase_uid) {
                $dbUser->update(["firebase_uid" => "makassar_uid_" . $dbUser->id]);
            }
        }
    }
}
