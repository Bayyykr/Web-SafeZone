<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                "nama_kategori" => "Pembegalan / Perampokan Jalanan",
                "jenis" => "kejahatan",
                "warna_marker" => "#FF0000",
            ],
            [
                "nama_kategori" => "Pencurian Motor (Curanmor)",
                "jenis" => "kejahatan",
                "warna_marker" => "#E91E63",
            ],
            [
                "nama_kategori" => "Pemberatan / Penganiayaan",
                "jenis" => "kejahatan",
                "warna_marker" => "#9C27B0",
            ],
            [
                "nama_kategori" => "Pencurian Rumah / Toko",
                "jenis" => "kejahatan",
                "warna_marker" => "#FF5722",
            ],
            [
                "nama_kategori" => "Penjambretan / Pencurian HP",
                "jenis" => "kejahatan",
                "warna_marker" => "#F97316",
            ],
            [
                "nama_kategori" => "Penipuan Online / Scam Digital",
                "jenis" => "kejahatan",
                "warna_marker" => "#6366F1",
            ],
            [
                "nama_kategori" => "Balap Liar / Gangguan Ketertiban",
                "jenis" => "kejahatan",
                "warna_marker" => "#F59E0B",
            ],
            [
                "nama_kategori" => "Kecelakaan Lalu Lintas Tunggal",
                "jenis" => "kecelakaan",
                "warna_marker" => "#4CAF50",
            ],
            [
                "nama_kategori" => "Kecelakaan Tabrakan",
                "jenis" => "kecelakaan",
                "warna_marker" => "#009688",
            ],
            [
                "nama_kategori" => "Kecelakaan Beruntun",
                "jenis" => "kecelakaan",
                "warna_marker" => "#00BCD4",
            ],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ["nama_kategori" => $category["nama_kategori"]],
                $category,
            );
        }
    }
}
