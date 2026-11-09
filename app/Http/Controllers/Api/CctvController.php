<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CctvResource;
use App\Models\Cctv;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CctvController extends Controller
{
    /**
     * GET /api/cctvs
     * Menampilkan daftar semua CCTV yang tersedia untuk aplikasi mobile.
     * Mode: View-Only (Hanya melihat live stream, tidak bisa mengatur kamera/AI).
     *
     * Query Parameters Opsional:
     * - search: mencari berdasarkan nama, keterangan, atau wilayah lokasi
     * - aktif: filter status aktif (true/false / 1/0)
     * - filter: filter 'begalvision' hanya menampilkan kamera AI
     * - wrap: set true untuk mendapatkan response format { success: true, data: [...] }
     */
    public function index(Request $request): JsonResponse
    {
        $query = Cctv::query()
            ->with('lokasi')
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->input('search');
                $q->where(function ($sub) use ($search) {
                    $sub->where('nama', 'like', "%{$search}%")
                        ->orWhere('keterangan', 'like', "%{$search}%")
                        ->orWhereHas('lokasi', fn($l) => $l->where('nama_lokasi', 'like', "%{$search}%"));
                });
            })
            ->when($request->has('aktif'), function ($q) use ($request) {
                $q->where('aktif', filter_var($request->input('aktif'), FILTER_VALIDATE_BOOLEAN));
            })
            ->when($request->input('filter') === 'begalvision', function ($q) {
                $q->where(function ($sub) {
                    $sub->where('url_stream', 'like', '%video_feed%')
                        ->orWhere('nama', 'like', '%begal%')
                        ->orWhere('keterangan', 'like', '%begalvision%');
                });
            })
            ->orderByDesc('aktif')
            ->orderBy('nama');

        $cctvs = CctvResource::collection($query->get());

        if ($request->boolean('wrap')) {
            return response()->json([
                'success' => true,
                'message' => 'Daftar CCTV berhasil diambil',
                'total' => $cctvs->count(),
                'mode' => 'view_only',
                'data' => $cctvs,
            ]);
        }

        return response()->json($cctvs);
    }

    /**
     * GET /api/cctvs/{cctv}
     * Menampilkan detail CCTV dan URL live streaming untuk aplikasi mobile.
     * Mode: View-Only (Hanya melihat live stream, tidak bisa mengatur kamera/AI).
     */
    public function show(Cctv $cctv, Request $request): JsonResponse
    {
        $cctv->load('lokasi');
        $resource = new CctvResource($cctv);

        if ($request->boolean('wrap')) {
            return response()->json([
                'success' => true,
                'message' => 'Detail CCTV berhasil diambil',
                'mode' => 'view_only',
                'data' => $resource,
            ]);
        }

        return response()->json($resource);
    }

    /**
     * GET /api/cctvs/{cctv}/telemetry
     * Mengambil pembacaan sensor telemetri ESP-32 real-time untuk widget mobile.
     * Mode: View-Only (Sensor pembacaan saja, tanpa kontrol tombol aktuator).
     */
    public function telemetry(Cctv $cctv): JsonResponse
    {
        $cctv->load('lokasi');

        $lat = $cctv->lokasi?->latitude ? (float) $cctv->lokasi->latitude : -8.133182;
        $lng = $cctv->lokasi?->longitude ? (float) $cctv->lokasi->longitude : 113.224522;

        return response()->json([
            'success' => true,
            'cctv_id' => $cctv->id,
            'cctv_nama' => $cctv->nama,
            'is_begalvision' => (bool) $cctv->is_begalvision,
            'can_control' => false,
            'mode' => 'view_only',
            'telemetri' => [
                'pir_motion' => false,
                'status_gerakan' => 'Aman (Tidak Ada Gerakan)',
                'noise_db' => rand(38, 52),
                'status_suara' => 'Normal',
                'lux' => rand(20, 35),
                'status_cahaya' => 'Redup (Malam Hari)',
                'sirine_aktif' => false,
                'lampu_aktif' => false,
                'latitude' => $lat,
                'longitude' => $lng,
                'timestamp' => now()->format('H:i:s d/m/Y'),
            ],
        ]);
    }
}
