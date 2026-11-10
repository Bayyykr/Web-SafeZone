<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CctvResource;
use App\Models\Cctv;
use App\Services\FirebaseService;
use App\Services\MultiModalFuzzyService;
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
     * Mengambil pembacaan sensor telemetri ESP-32 real-time dari Firebase RTDB
     * dan skor Fuzzy Logic multi-modal untuk widget mobile.
     * Mode: View-Only (Sensor pembacaan saja, tanpa kontrol tombol aktuator).
     */
    public function telemetry(Cctv $cctv, FirebaseService $firebaseService, MultiModalFuzzyService $fuzzyService): JsonResponse
    {
        $cctv->load('lokasi');

        $fbTelemetry = $firebaseService->fetchDeviceTelemetry('BEGAL-VISION-01');

        $sensorNoise = $fbTelemetry['sensors']['noise_db'] ?? 42.0;
        $sensorLux = $fbTelemetry['sensors']['lux'] ?? 24.0;
        $sensorPir = !empty($fbTelemetry['sensors']['pir_motion']);

        $fuzzyResult = $fuzzyService->evaluate([
            'visual_label' => 'normal',
            'visual_confidence' => 0.0,
            'noise_db' => $sensorNoise,
            'lux' => $sensorLux,
            'pir' => $sensorPir,
        ]);

        $cctvLat = $cctv->lokasi?->latitude ? (float) $cctv->lokasi->latitude : -8.133182;
        $cctvLng = $cctv->lokasi?->longitude ? (float) $cctv->lokasi->longitude : 113.224522;
        $iotLat = $fbTelemetry['gps']['latitude'];
        $iotLng = $fbTelemetry['gps']['longitude'];

        $proximity = $firebaseService->checkDeviceProximity($cctvLat, $cctvLng, $iotLat, $iotLng, 10.0);

        $lat = $iotLat ?? $cctvLat;
        $lng = $iotLng ?? $cctvLng;

        $statusGerakan = $sensorPir ? 'Pergerakan Terdeteksi (PIR Aktif)' : 'Aman (Tidak Ada Gerakan)';
        $statusSuara = ($sensorNoise >= 75) ? 'Teriakan / Suara Bising Tinggi' : (($sensorNoise >= 55) ? 'Aktivitas Normal' : 'Hening');
        $statusCahaya = ($sensorLux < 20) ? 'Gelap (Malam Hari)' : 'Terang Cukup';

        return response()->json([
            'success' => true,
            'cctv_id' => $cctv->id,
            'cctv_nama' => $cctv->nama,
            'is_begalvision' => (bool) $cctv->is_begalvision,
            'can_control' => false,
            'mode' => 'view_only',
            'iot_proximity' => [
                'is_matched' => $proximity['is_matched'],
                'distance_meters' => $proximity['distance_meters'],
                'max_threshold_meters' => $proximity['max_threshold_meters'],
                'status_lokasi' => $proximity['is_matched'] ? 'Terdeteksi di Titik CCTV' : 'Di Luar Jangkauan Kamera',
                'cctv_coords' => ['latitude' => $cctvLat, 'longitude' => $cctvLng],
                'iot_coords' => ['latitude' => $iotLat, 'longitude' => $iotLng],
            ],
            'telemetri' => [
                'pir_motion' => $sensorPir,
                'status_gerakan' => $statusGerakan,
                'noise_db' => $sensorNoise,
                'raw_audio' => $fbTelemetry['sensors']['raw_audio_level'] ?? null,
                'status_suara' => $statusSuara,
                'lux' => $sensorLux,
                'status_cahaya' => $statusCahaya,
                'sirine_aktif' => !empty($fbTelemetry['actuators']['siren_active']),
                'begal_aktif' => !empty($fbTelemetry['status']['begal']),
                'lampu_aktif' => !empty($fbTelemetry['actuators']['led_active']),
                'latitude' => $lat,
                'longitude' => $lng,
                'timestamp' => now()->format('H:i:s d/m/Y'),
            ],
            'fuzzy_threat' => [
                'skor' => $fuzzyResult['score'],
                'kategori' => $fuzzyResult['category'],
                'alarm_otomatis' => $fuzzyResult['is_alarm_triggered'],
                'aturan_dominan' => [
                    'kode' => $fuzzyResult['dominant_rule']['code'],
                    'skor' => $fuzzyResult['dominant_rule']['score'],
                    'skenario' => $fuzzyResult['dominant_rule']['scenario'],
                ],
            ],
        ]);
                    'skor' => $fuzzyResult['dominant_rule']['score'],
                    'skenario' => $fuzzyResult['dominant_rule']['scenario'],
                ],
            ],
        ]);
    }
}
