<?php

namespace Database\Seeders;

use App\Models\Cctv;
use App\Models\Location;
use Illuminate\Database\Seeder;

class CctvSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some locations to map CCTV to
        $lokasiKota = Location::where('nama_lokasi', 'LIKE', '%Lumajang%')->first();
        $lokasiSukodono = Location::where('nama_lokasi', 'LIKE', '%Sukodono%')->first();
        $lokasiPasirian = Location::where('nama_lokasi', 'LIKE', '%Pasirian%')->first();
        
        $cctvs = [
            [
                'nama' => 'CCTV Simpang Toga Lumajang',
                'url_stream' => 'https://www.youtube.com/watch?v=1d9yv2_aMIs', // Live ATCS Yogyakarta
                'keterangan' => 'Memantau arus lalu lintas di Simpang Toga Lumajang Kota.',
                'aktif' => true,
                'lokasi_id' => $lokasiKota?->id,
            ],
            [
                'nama' => 'CCTV Alun-Alun Lumajang',
                'url_stream' => 'https://www.youtube.com/watch?v=F3G8n23L_gY', // Live CCTV
                'keterangan' => 'Memantau area publik Alun-Alun Lumajang.',
                'aktif' => true,
                'lokasi_id' => $lokasiKota?->id,
            ],
            [
                'nama' => 'CCTV Simpang Sukodono',
                'url_stream' => 'https://www.youtube.com/watch?v=1d9yv2_aMIs',
                'keterangan' => 'Memantau perbatasan wilayah masuk Sukodono.',
                'aktif' => true,
                'lokasi_id' => $lokasiSukodono?->id,
            ],
            [
                'nama' => 'CCTV Pasar Pasirian',
                'url_stream' => 'https://www.youtube.com/watch?v=F3G8n23L_gY',
                'keterangan' => 'Memantau kondisi keamanan di sekitar pasar Pasirian.',
                'aktif' => true,
                'lokasi_id' => $lokasiPasirian?->id,
            ],
        ];

        foreach ($cctvs as $cctv) {
            Cctv::updateOrCreate(
                ['nama' => $cctv['nama']],
                $cctv
            );
        }
    }
}
