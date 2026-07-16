<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                "name" => "Kominfo Lumajang Admin",
                "username" => "admin.kominfo",
                "email" => "admin.kominfo@lumajang.go.id",
                "telepon" => "081234560001",
                "alamat" => "Kantor Kominfo Lumajang, Jl. Alun-Alun Utara No. 2",
                "role" => "super_admin",
                "aktif" => true,
                "password" => "KominfoLumajang2026!",
            ],
            [
                "name" => "Polda Jatim Patroli",
                "username" => "admin.patroli",
                "email" => "admin.patroli@polri.go.id",
                "telepon" => "081234560002",
                "alamat" => "Ruang Command Center Polres Lumajang",
                "role" => "admin",
                "aktif" => true,
                "password" => "PolriPatroli2026!",
            ],
            [
                "name" => "Polsek Lumajang Kota Admin",
                "username" => "admin.polseklumajang",
                "email" => "admin.polsekkota@polri.go.id",
                "telepon" => "081234560003",
                "alamat" => "Polsek Lumajang Kota, Jl. Alun-Alun Barat",
                "role" => "admin",
                "aktif" => true,
                "password" => "PolsekKota2026!",
            ],
            [
                "name" => "Bayu Kristanto",
                "username" => "bayukristanto",
                "email" => "bayukristanto2005@gmail.com",
                "telepon" => "081234560004",
                "alamat" => "Kecamatan Sukodono, Lumajang",
                "role" => "user",
                "aktif" => true,
                "password" => "masyarakat123",
            ],
            [
                "name" => "Budi Santoso",
                "username" => "budi.santoso",
                "email" => "budi.santoso@gmail.com",
                "telepon" => "081234560005",
                "alamat" => "Kecamatan Sumbersuko, Lumajang",
                "role" => "user",
                "aktif" => true,
                "password" => "masyarakat123",
            ],
            [
                "name" => "Dewi Lestari",
                "username" => "dewi.lestari",
                "email" => "dewi.lestari@polri.go.id",
                "telepon" => "081234560006",
                "alamat" => "Kecamatan Tekung, Lumajang",
                "role" => "admin",
                "aktif" => true,
                "password" => "PolriDewi2026!",
            ],
            [
                "name" => "Agus Prasetyo",
                "username" => "agus.prasetyo",
                "email" => "agus.prasetyo@gmail.com",
                "telepon" => "081234560007",
                "alamat" => "Kecamatan Senduro, Lumajang",
                "role" => "user",
                "aktif" => false,
                "password" => "masyarakat123",
            ],
            [
                "name" => "Siti Aminah",
                "username" => "siti.aminah",
                "email" => "siti.aminah@gmail.com",
                "telepon" => "081234560008",
                "alamat" => "Kecamatan Pasirian, Lumajang",
                "role" => "user",
                "aktif" => true,
                "password" => "masyarakat123",
            ],
            [
                "name" => "Eko Wahyudi",
                "username" => "eko.wahyudi",
                "email" => "eko.wahyudi@polri.go.id",
                "telepon" => "081234560009",
                "alamat" => "Kecamatan Klakah, Lumajang",
                "role" => "admin",
                "aktif" => true,
                "password" => "PolriEko2026!",
            ],
            [
                "name" => "Maya Putri",
                "username" => "maya.putri",
                "email" => "maya.putri@gmail.com",
                "telepon" => "081234560010",
                "alamat" => "Kecamatan Candipuro, Lumajang",
                "role" => "user",
                "aktif" => true,
                "password" => "masyarakat123",
            ],
            [
                "name" => "Andi Saputra",
                "username" => "andi.saputra",
                "email" => "andi.saputra@gmail.com",
                "telepon" => "081234560011",
                "alamat" => "Kecamatan Yosowilangun, Lumajang",
                "role" => "user",
                "aktif" => true,
                "password" => "masyarakat123",
            ],
            [
                "name" => "Fitri Handayani",
                "username" => "fitri.handayani",
                "email" => "fitri.handayani@polri.go.id",
                "telepon" => "081234560012",
                "alamat" => "Kecamatan Randuagung, Lumajang",
                "role" => "admin",
                "aktif" => true,
                "password" => "PolriFitri2026!",
            ],
            [
                "name" => "Hendra Wijaya",
                "username" => "hendra.wijaya",
                "email" => "hendra.wijaya@polri.go.id",
                "telepon" => "081234560013",
                "alamat" => "Kecamatan Jatiroto, Lumajang",
                "role" => "admin",
                "aktif" => false,
                "password" => "PolriHendra2026!",
            ],
            [
                "name" => "Nia Kurniawati",
                "username" => "nia.kurniawati",
                "email" => "nia.kurniawati@gmail.com",
                "telepon" => "081234560014",
                "alamat" => "Kecamatan Rowokangkung, Lumajang",
                "role" => "user",
                "aktif" => true,
                "password" => "masyarakat123",
            ],
            [
                "name" => "Yoga Firmansyah",
                "username" => "yoga.firmansyah",
                "email" => "yoga.firmansyah@gmail.com",
                "telepon" => "081234560015",
                "alamat" => "Kecamatan Tempursari, Lumajang",
                "role" => "user",
                "aktif" => true,
                "password" => "masyarakat123",
            ],
        ];

        $firebaseService = app(\App\Services\FirebaseService::class);
        foreach ($users as $user) {
            $password = $user['password'];
            unset($user['password']);
            $dbUser = User::updateOrCreate(
                ["email" => $user["email"]],
                $user + ["password" => Hash::make($password)],
            );

            $uid = $firebaseService->createUserInFirebaseAuth($dbUser->email, $password, $dbUser->name);
            if ($uid) {
                $dbUser->update(['firebase_uid' => $uid]);
            }
        }
    }
}
