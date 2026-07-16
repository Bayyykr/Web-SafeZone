<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\KonfirmasiLaporan;
use App\Models\Laporan;
use App\Models\Location;
use App\Models\Polsek;
use App\Models\User;
use Illuminate\Database\Seeder;

class LaporanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $laporanSamples = [
            [
                "user_email" => "bayukristanto2005@gmail.com",
                "kategori" => "Kecelakaan Lalu Lintas Tunggal",
                "lokasi" => "Sukodono",
                "polsek" => "Polsek Sukodono",
                "judul_laporan" => "Kecelakaan motor di jalan raya Sukodono",
                "deskripsi" =>
                    "Pengendara motor tergelincir saat melewati tikungan licin. Korban mengalami luka ringan dan sudah dibantu warga sekitar.",
                "latitude" => -8.1127,
                "longitude" => 113.2321,
                "status" => "pending",
                "created_at" => now()->subDays(1)->setTime(7, 20),
            ],
            [
                "user_email" => "budi.santoso@gmail.com",
                "kategori" => "Pencurian Motor (Curanmor)",
                "lokasi" => "Lumajang (Kota)",
                "polsek" => "Polsek Lumajang Kota",
                "judul_laporan" => "Motor hilang di area parkir pasar",
                "deskripsi" =>
                    "Motor matic warna hitam hilang sekitar pukul 19.30 WIB. Pelapor sudah mengecek area parkir namun kendaraan tidak ditemukan.",
                "latitude" => -8.1336,
                "longitude" => 113.2228,
                "status" => "dikonfirmasi",
                "catatan" =>
                    "Laporan valid, petugas diarahkan mengecek CCTV sekitar pasar.",
                "petugas_email" => "admin.lumajang@polri.go.id",
                "created_at" => now()->subDays(2)->setTime(19, 45),
            ],
            [
                "user_email" => "siti.aminah@gmail.com",
                "kategori" => "Pembegalan / Perampokan Jalanan",
                "lokasi" => "Pasirian",
                "polsek" => "Polsek Pasirian",
                "judul_laporan" => "Dugaan pembegalan di jalan sepi Pasirian",
                "deskripsi" =>
                    "Ada dua orang mencurigakan menghentikan kendaraan warga di ruas jalan minim penerangan. Warga sekitar diminta lebih waspada.",
                "latitude" => -8.2163,
                "longitude" => 113.1158,
                "status" => "ditolak",
                "catatan" =>
                    "Data belum lengkap dan tidak ada saksi pendukung. Pelapor diminta melengkapi informasi.",
                "petugas_email" => "fitri.handayani@polri.go.id",
                "created_at" => now()->subDays(3)->setTime(21, 10),
            ],
            [
                "user_email" => "maya.putri@gmail.com",
                "kategori" => "Kecelakaan Tabrakan",
                "lokasi" => "Candipuro",
                "polsek" => "Polsek Candipuro",
                "judul_laporan" =>
                    "Tabrakan dua kendaraan dekat simpang Candipuro",
                "deskripsi" =>
                    "Dua kendaraan roda dua bertabrakan di dekat simpang. Arus lalu lintas sempat melambat dan warga membantu mengatur jalan.",
                "latitude" => -8.1889,
                "longitude" => 113.0515,
                "status" => "selesai",
                "catatan" =>
                    "Laporan telah ditangani Polsek Candipuro dan lalu lintas kembali normal.",
                "petugas_email" => "dewi.lestari@polri.go.id",
                "created_at" => now()->subDays(4)->setTime(16, 5),
            ],
            [
                "user_email" => "andi.saputra@gmail.com",
                "kategori" => "Pencurian Rumah / Toko",
                "lokasi" => "Yosowilangun",
                "polsek" => "Polsek Yosowilangun",
                "judul_laporan" => "Toko kelontong dibobol dini hari",
                "deskripsi" =>
                    "Pemilik toko menemukan pintu belakang rusak dan beberapa barang hilang. Kejadian diperkirakan terjadi dini hari.",
                "latitude" => -8.2048,
                "longitude" => 113.3198,
                "status" => "pending",
                "created_at" => now()->subDays(5)->setTime(5, 40),
            ],
            [
                "user_email" => "nia.kurniawati@gmail.com",
                "kategori" => "Pencurian Motor (Curanmor)",
                "lokasi" => "Lumajang (Kota)",
                "polsek" => "Polsek Lumajang Kota",
                "judul_laporan" => "Curanmor di parkiran minimarket Veteran",
                "deskripsi" =>
                    "Motor bebek warna merah hilang saat pemilik berbelanja sekitar 15 menit. Area parkir cukup ramai dan pelapor sudah mengecek sekitar lokasi.",
                "latitude" => -8.1328,
                "longitude" => 113.2242,
                "status" => "pending",
                "created_at" => now()->subDays(1)->setTime(20, 15),
            ],
            [
                "user_email" => "yoga.firmansyah@gmail.com",
                "kategori" => "Penjambretan / Pencurian HP",
                "lokasi" => "Lumajang (Kota)",
                "polsek" => "Polsek Lumajang Kota",
                "judul_laporan" => "Penjambretan HP dekat kawasan alun-alun",
                "deskripsi" =>
                    "Pelapor kehilangan handphone saat berjalan kaki. Pelaku menggunakan sepeda motor dan langsung kabur ke arah jalan utama.",
                "latitude" => -8.134,
                "longitude" => 113.2215,
                "status" => "dikonfirmasi",
                "catatan" =>
                    "Laporan valid. Petugas melakukan penelusuran saksi dan rekaman kamera sekitar.",
                "petugas_email" => "admin.polsekkota@polri.go.id",
                "created_at" => now()->subDays(2)->setTime(18, 55),
            ],
            [
                "user_email" => "bayukristanto2005@gmail.com",
                "kategori" => "Penipuan Online / Scam Digital",
                "lokasi" => "Lumajang (Kota)",
                "polsek" => "Polsek Lumajang Kota",
                "judul_laporan" =>
                    "Penipuan marketplace dengan bukti transfer palsu",
                "deskripsi" =>
                    "Pelapor menerima bukti transfer palsu dari pembeli online. Barang hampir dikirim sebelum pelapor menyadari rekening belum bertambah.",
                "latitude" => -8.1352,
                "longitude" => 113.2237,
                "status" => "pending",
                "created_at" => now()->subDays(3)->setTime(10, 25),
            ],
            [
                "user_email" => "budi.santoso@gmail.com",
                "kategori" => "Balap Liar / Gangguan Ketertiban",
                "lokasi" => "Sukodono",
                "polsek" => "Polsek Sukodono",
                "judul_laporan" =>
                    "Balap liar saat malam minggu di jalur Sukodono",
                "deskripsi" =>
                    "Sekelompok remaja melakukan balap liar dan mengganggu pengguna jalan. Warga meminta patroli rutin saat akhir pekan.",
                "latitude" => -8.1118,
                "longitude" => 113.2329,
                "status" => "dikonfirmasi",
                "catatan" =>
                    "Patroli malam ditingkatkan di titik rawan balap liar.",
                "petugas_email" => "dewi.lestari@polri.go.id",
                "created_at" => now()->subDays(4)->setTime(23, 30),
            ],
            [
                "user_email" => "siti.aminah@gmail.com",
                "kategori" => "Kecelakaan Tabrakan",
                "lokasi" => "Pasirian",
                "polsek" => "Polsek Pasirian",
                "judul_laporan" => "Tabrakan motor di jalur padat Pasirian",
                "deskripsi" =>
                    "Dua pengendara motor bertabrakan saat salah satu kendaraan hendak menyalip. Korban mengalami luka ringan dan dibawa ke fasilitas kesehatan terdekat.",
                "latitude" => -8.2152,
                "longitude" => 113.1155,
                "status" => "pending",
                "created_at" => now()->subDays(2)->setTime(7, 45),
            ],
            [
                "user_email" => "maya.putri@gmail.com",
                "kategori" => "Kecelakaan Beruntun",
                "lokasi" => "Sumbersuko",
                "polsek" => "Polsek Sumbersuko",
                "judul_laporan" =>
                    "Kecelakaan beruntun kecil akibat pengereman mendadak",
                "deskripsi" =>
                    "Tiga kendaraan terlibat kecelakaan ringan saat arus lalu lintas padat. Tidak ada korban jiwa, namun arus sempat tersendat.",
                "latitude" => -8.1631,
                "longitude" => 113.2028,
                "status" => "dikonfirmasi",
                "catatan" =>
                    "Petugas mengatur lalu lintas dan meminta pengendara menjaga jarak aman.",
                "petugas_email" => "admin.lumajang@polri.go.id",
                "created_at" => now()->subDays(6)->setTime(16, 20),
            ],
            [
                "user_email" => "andi.saputra@gmail.com",
                "kategori" => "Pencurian Motor (Curanmor)",
                "lokasi" => "Sumbersuko",
                "polsek" => "Polsek Sumbersuko",
                "judul_laporan" =>
                    "Percobaan curanmor di halaman kos Sumbersuko",
                "deskripsi" =>
                    "Warga memergoki dua orang mencoba merusak kunci motor di halaman kos. Pelaku kabur setelah diteriaki warga.",
                "latitude" => -8.164,
                "longitude" => 113.2041,
                "status" => "pending",
                "created_at" => now()->subDays(1)->setTime(2, 10),
            ],
            [
                "user_email" => "fitri.handayani@polri.go.id",
                "kategori" => "Pemberatan / Penganiayaan",
                "lokasi" => "Yosowilangun",
                "polsek" => "Polsek Yosowilangun",
                "judul_laporan" => "Keributan antar pemuda setelah acara musik",
                "deskripsi" =>
                    "Terjadi keributan antar pemuda setelah acara musik lokal. Warga melerai sebelum kejadian meluas.",
                "latitude" => -8.2055,
                "longitude" => 113.3189,
                "status" => "dikonfirmasi",
                "catatan" =>
                    "Petugas melakukan pendataan saksi dan mediasi awal.",
                "petugas_email" => "fitri.handayani@polri.go.id",
                "created_at" => now()->subDays(7)->setTime(22, 40),
            ],
            [
                "user_email" => "eko.wahyudi@polri.go.id",
                "kategori" => "Pencurian Rumah / Toko",
                "lokasi" => "Kunir",
                "polsek" => "Polsek Lumajang Kota",
                "judul_laporan" =>
                    "Gudang toko bangunan kehilangan kabel dan perkakas",
                "deskripsi" =>
                    "Pemilik menemukan beberapa gulung kabel dan perkakas hilang dari gudang belakang. Diduga pelaku masuk melalui pagar samping.",
                "latitude" => -8.2128,
                "longitude" => 113.2668,
                "status" => "pending",
                "created_at" => now()->subDays(2)->setTime(6, 30),
            ],
            [
                "user_email" => "nia.kurniawati@gmail.com",
                "kategori" => "Pembegalan / Perampokan Jalanan",
                "lokasi" => "Kunir",
                "polsek" => "Polsek Lumajang Kota",
                "judul_laporan" =>
                    "Dugaan pembegalan di ruas Kunir menjelang subuh",
                "deskripsi" =>
                    "Pengendara merasa diikuti dua sepeda motor di ruas sepi. Pelapor berhasil menuju permukiman warga sebelum terjadi kontak langsung.",
                "latitude" => -8.2119,
                "longitude" => 113.2654,
                "status" => "dikonfirmasi",
                "catatan" => "Patroli subuh ditingkatkan di ruas rawan Kunir.",
                "petugas_email" => "admin.lumajang@polri.go.id",
                "created_at" => now()->subDays(3)->setTime(4, 35),
            ],
            [
                "user_email" => "yoga.firmansyah@gmail.com",
                "kategori" => "Kecelakaan Lalu Lintas Tunggal",
                "lokasi" => "Candipuro",
                "polsek" => "Polsek Candipuro",
                "judul_laporan" => "Motor tergelincir saat hujan di Candipuro",
                "deskripsi" =>
                    "Pengendara tergelincir di jalan licin saat hujan deras. Warga membantu korban dan menghubungi keluarga.",
                "latitude" => -8.1892,
                "longitude" => 113.0524,
                "status" => "selesai",
                "catatan" =>
                    "Korban sudah ditangani dan kendaraan diamankan keluarga.",
                "petugas_email" => "dewi.lestari@polri.go.id",
                "created_at" => now()->subMonth()->setDay(18)->setTime(17, 10),
            ],
            [
                "user_email" => "budi.santoso@gmail.com",
                "kategori" => "Penipuan Online / Scam Digital",
                "lokasi" => "Sukodono",
                "polsek" => "Polsek Sukodono",
                "judul_laporan" =>
                    "Modus link kurir palsu menguras saldo e-wallet",
                "deskripsi" =>
                    "Pelapor membuka tautan yang mengatasnamakan kurir paket. Setelah mengisi data, saldo e-wallet berkurang tanpa transaksi yang diakui.",
                "latitude" => -8.1125,
                "longitude" => 113.2314,
                "status" => "selesai",
                "catatan" =>
                    "Pelapor diarahkan mengamankan akun dan membuat laporan pendukung ke penyedia layanan.",
                "petugas_email" => "admin.lumajang@polri.go.id",
                "created_at" => now()->subMonth()->setDay(24)->setTime(13, 5),
            ],
            [
                "user_email" => "siti.aminah@gmail.com",
                "kategori" => "Pencurian Motor (Curanmor)",
                "lokasi" => "Pasirian",
                "polsek" => "Polsek Pasirian",
                "judul_laporan" => "Curanmor di parkiran tempat makan Pasirian",
                "deskripsi" =>
                    "Motor matic dilaporkan hilang saat pemilik makan malam. Saksi melihat dua orang mencurigakan di area parkir.",
                "latitude" => -8.2166,
                "longitude" => 113.1148,
                "status" => "selesai",
                "catatan" =>
                    "Laporan masuk arsip penanganan dan koordinasi patroli setempat.",
                "petugas_email" => "fitri.handayani@polri.go.id",
                "created_at" => now()->subMonths(2)->setDay(12)->setTime(20, 0),
            ],
            [
                "user_email" => "bayukristanto2005@gmail.com",
                "kategori" => "Kecelakaan Tabrakan",
                "lokasi" => "Lumajang (Kota)",
                "polsek" => "Polsek Lumajang Kota",
                "judul_laporan" => "Senggolan motor saat jam berangkat sekolah",
                "deskripsi" =>
                    "Dua motor bersenggolan saat arus kendaraan padat. Tidak ada korban berat, namun perlu pengaturan lalu lintas pagi.",
                "latitude" => -8.1319,
                "longitude" => 113.222,
                "status" => "selesai",
                "catatan" =>
                    "Petugas mengimbau pengendara berhati-hati di jam padat sekolah.",
                "petugas_email" => "admin.polsekkota@polri.go.id",
                "created_at" => now()->subMonths(3)->setDay(9)->setTime(6, 50),
            ],
        ];

        foreach ($laporanSamples as $sample) {
            $user = User::where("email", $sample["user_email"])->first();
            $category = Category::where(
                "nama_kategori",
                $sample["kategori"],
            )->first();
            $location = Location::where(
                "nama_lokasi",
                $sample["lokasi"],
            )->first();
            $polsek = Polsek::where("nama", $sample["polsek"])->first();

            if (!$user || !$category) {
                continue;
            }

            $laporan = Laporan::updateOrCreate(
                ["judul_laporan" => $sample["judul_laporan"]],
                [
                    "user_id" => $user->id,
                    "kategori_id" => $category->id,
                    "lokasi_id" => $location?->id,
                    "polsek_id" => $polsek?->id,
                    "deskripsi" => $sample["deskripsi"],
                    "latitude" => $sample["latitude"],
                    "longitude" => $sample["longitude"],
                    "status" => $sample["status"],
                    "created_at" => $sample["created_at"],
                    "updated_at" => $sample["created_at"],
                ],
            );

            if (
                in_array(
                    $sample["status"],
                    ["dikonfirmasi", "ditolak", "selesai"],
                    true,
                )
            ) {
                $petugas = User::where(
                    "email",
                    $sample["petugas_email"] ?? "admin.lumajang@polri.go.id",
                )->first();

                KonfirmasiLaporan::updateOrCreate(
                    ["laporan_id" => $laporan->id],
                    [
                        "petugas_id" => $petugas?->id,
                        "status" =>
                            $sample["status"] === "ditolak"
                                ? "ditolak"
                                : "valid",
                        "catatan" => $sample["catatan"] ?? null,
                        "dikonfirmasi_pada" => $sample["created_at"]
                            ->copy()
                            ->addHours(2),
                    ],
                );
            }
        }
    }
}
