<?php

namespace Database\Seeders;

use App\Models\Berita;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Seeder;

class BeritaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $beritaSamples = [
            [
                "judul" => "Info: Pemeliharaan CCTV Wonorejo",
                "isi_berita" =>
                    "Dinas terkait melakukan pemeliharaan perangkat CCTV di area Wonorejo. Selama proses berlangsung, pemantauan dialihkan ke titik kamera terdekat.",
                "lokasi" => "Lumajang (Kota)",
                "penulis_email" => "admin.lumajang@polri.go.id",
                "status" => "published",
                "published_at" => now()->subDays(1)->setTime(9, 0),
            ],
            [
                "judul" => "Waspada: Peningkatan Arus Lalu Lintas di Pasirian",
                "isi_berita" =>
                    "Masyarakat diimbau berhati-hati saat melintas di jalur Pasirian karena terjadi peningkatan arus kendaraan pada jam pulang kerja.",
                "lokasi" => "Pasirian",
                "penulis_email" => "fitri.handayani@polri.go.id",
                "status" => "published",
                "published_at" => now()->subDays(2)->setTime(15, 30),
            ],
            [
                "judul" => "Imbauan Keamanan Area Parkir Pasar Lumajang",
                "isi_berita" =>
                    "Pengunjung pasar diminta memastikan kendaraan terkunci ganda dan tidak meninggalkan barang berharga di kendaraan.",
                "lokasi" => "Lumajang (Kota)",
                "penulis_email" => "admin.polsekkota@polri.go.id",
                "status" => "draft",
                "published_at" => null,
            ],
            [
                "judul" => "Patroli Malam Ditingkatkan di Sumbersuko",
                "isi_berita" =>
                    "Petugas meningkatkan patroli malam di beberapa titik rawan Kecamatan Sumbersuko untuk menjaga keamanan lingkungan.",
                "lokasi" => "Sumbersuko",
                "penulis_email" => "dewi.lestari@polri.go.id",
                "status" => "draft",
                "published_at" => null,
            ],
        ];

        foreach ($beritaSamples as $sample) {
            $penulis = User::where("email", $sample["penulis_email"])->first();
            $location = Location::where(
                "nama_lokasi",
                $sample["lokasi"],
            )->first();

            if (!$penulis) {
                continue;
            }

            Berita::updateOrCreate(
                ["judul" => $sample["judul"]],
                [
                    "user_id" => $penulis->id,
                    "lokasi_id" => $location?->id,
                    "isi_berita" => $sample["isi_berita"],
                    "status" => $sample["status"],
                    "published_at" => $sample["published_at"],
                ],
            );
        }
    }
}
