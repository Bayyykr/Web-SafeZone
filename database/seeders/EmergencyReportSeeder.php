<?php

namespace Database\Seeders;

use App\Models\EmergencyReport;
use App\Models\Polsek;
use App\Models\User;
use Illuminate\Database\Seeder;

class EmergencyReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $emergencySamples = [
            [
                "user_email" => "bayukristanto2005@gmail.com",
                "polsek" => "Polsek Lumajang Kota",
                "kode_darurat" => "SOS-LMJ-0001",
                "status" => "aktif",
                "latitude" => -8.1342,
                "longitude" => 113.2231,
                "alamat_terdeteksi" => "Area Alun-Alun Lumajang, Jogoyudan",
                "jarak_polsek_km" => 0.8,
                "waktu_sos" => now()->subMinutes(12),
                "waktu_dispatch" => null,
                "waktu_selesai" => null,
                "petugas_penanganan" => null,
                "catatan_petugas" =>
                    "SOS aktif, menunggu dispatch petugas terdekat.",
                "telemetri" => [
                    "battery" => 78,
                    "accuracy_meter" => 18,
                    "device" => "Android",
                ],
            ],
            [
                "user_email" => "budi.santoso@gmail.com",
                "polsek" => "Polsek Sukodono",
                "kode_darurat" => "SOS-LMJ-0002",
                "status" => "dalam_penanganan",
                "latitude" => -8.1129,
                "longitude" => 113.2326,
                "alamat_terdeteksi" => "Jl. Raya Sukodono, dekat SPBU Sukodono",
                "jarak_polsek_km" => 1.4,
                "waktu_sos" => now()->subMinutes(45),
                "waktu_dispatch" => now()->subMinutes(32),
                "waktu_selesai" => null,
                "petugas_penanganan" => "Unit Patroli Sukodono 01",
                "catatan_petugas" =>
                    "Personel telah diberangkatkan menuju titik SOS.",
                "telemetri" => [
                    "battery" => 52,
                    "accuracy_meter" => 24,
                    "device" => "Android",
                ],
            ],
            [
                "user_email" => "siti.aminah@gmail.com",
                "polsek" => "Polsek Pasirian",
                "kode_darurat" => "SOS-LMJ-0003",
                "status" => "selesai",
                "latitude" => -8.2161,
                "longitude" => 113.1161,
                "alamat_terdeteksi" => "Jl. Raya Pasirian, area pertokoan",
                "jarak_polsek_km" => 2.1,
                "waktu_sos" => now()->subDays(1)->setTime(20, 15),
                "waktu_dispatch" => now()->subDays(1)->setTime(20, 25),
                "waktu_selesai" => now()->subDays(1)->setTime(21, 5),
                "petugas_penanganan" => "Unit Reskrim Pasirian",
                "catatan_petugas" =>
                    "Situasi aman. Pelapor sudah didampingi dan diminta membuat laporan lanjutan jika diperlukan.",
                "telemetri" => [
                    "battery" => 64,
                    "accuracy_meter" => 16,
                    "device" => "iOS",
                ],
            ],
            [
                "user_email" => "maya.putri@gmail.com",
                "polsek" => "Polsek Candipuro",
                "kode_darurat" => "SOS-LMJ-0004",
                "status" => "arsip",
                "latitude" => -8.1887,
                "longitude" => 113.052,
                "alamat_terdeteksi" => "Simpang Candipuro arah Pasirian",
                "jarak_polsek_km" => 1.7,
                "waktu_sos" => now()->subDays(3)->setTime(18, 35),
                "waktu_dispatch" => now()->subDays(3)->setTime(18, 42),
                "waktu_selesai" => now()->subDays(3)->setTime(19, 18),
                "petugas_penanganan" => "Patroli Candipuro 02",
                "catatan_petugas" =>
                    "SOS telah selesai and masuk arsip riwayat darurat.",
                "telemetri" => [
                    "battery" => 41,
                    "accuracy_meter" => 21,
                    "device" => "Android",
                ],
            ],
        ];

        foreach ($emergencySamples as $sample) {
            $user = User::where("email", $sample["user_email"])->first();
            $polsek = Polsek::where("nama", $sample["polsek"])->first();

            EmergencyReport::updateOrCreate(
                ["kode_darurat" => $sample["kode_darurat"]],
                [
                    "user_id" => $user?->id,
                    "nearest_polsek_id" => $polsek?->id,
                    "status" => $sample["status"],
                    "latitude" => $sample["latitude"],
                    "longitude" => $sample["longitude"],
                    "alamat_terdeteksi" => $sample["alamat_terdeteksi"],
                    "jarak_polsek_km" => $sample["jarak_polsek_km"],
                    "waktu_sos" => $sample["waktu_sos"],
                    "waktu_dispatch" => $sample["waktu_dispatch"],
                    "waktu_selesai" => $sample["waktu_selesai"],
                    "petugas_penanganan" => $sample["petugas_penanganan"],
                    "catatan_petugas" => $sample["catatan_petugas"],
                    "telemetri" => $sample["telemetri"],
                    "created_at" => $sample["waktu_sos"],
                    "updated_at" =>
                        $sample["waktu_selesai"] ??
                        ($sample["waktu_dispatch"] ?? $sample["waktu_sos"]),
                ],
            );
        }
    }
}
