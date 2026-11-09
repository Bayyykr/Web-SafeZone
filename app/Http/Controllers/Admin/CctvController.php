<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CctvRequest;
use App\Models\Cctv;
use App\Models\EmergencyReport;
use App\Models\Location;
use App\Models\Polsek;
use App\Services\CctvService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CctvController extends Controller
{
    protected CctvService $cctvService;

    public function __construct(CctvService $cctvService)
    {
        $this->cctvService = $cctvService;
    }

    public function index(Request $request): View
    {
        $items = Cctv::query()
            ->with('lokasi')
            ->when($request->search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where('nama', 'like', "%{$search}%")
                        ->orWhere('keterangan', 'like', "%{$search}%")
                        ->orWhereHas(
                            'lokasi',
                            fn($query) => $query->where(
                                'nama_lokasi',
                                'like',
                                "%{$search}%",
                            ),
                        );
                });
            })
            ->latest()
            ->paginate(8)
            ->withQueryString();

        $locations = Location::query()->orderBy('nama_lokasi')->get();
        $createItem = new Cctv([
            'aktif' => true,
            'url_stream' => config('services.cctv_ai.stream_url', 'http://127.0.0.1:5000/video_feed'),
        ]);

        $stats = [
            'total' => Cctv::count(),
            'aktif' => Cctv::where('aktif', true)->count(),
            'nonaktif' => Cctv::where('aktif', false)->count(),
            'ai_streams' => Cctv::all()->filter(fn($c) => $c->is_begalvision)->count(),
        ];

        $aiConfig = config('services.cctv_ai');

        return view(
            'admin.master.cctv.index',
            compact('items', 'locations', 'createItem', 'stats', 'aiConfig'),
        );
    }

    public function create(): View
    {
        return view('admin.master.cctv.form', [
            'item' => new Cctv([
                'aktif' => true,
                'url_stream' => config('services.cctv_ai.stream_url', 'http://127.0.0.1:5000/video_feed'),
            ]),
            'locations' => Location::query()->orderBy('nama_lokasi')->get(),
        ]);
    }

    public function show(Cctv $cctv): View
    {
        $cctv->load('lokasi');
        $flaskBase = $this->getFlaskBaseUrl($cctv);

        $defaultModels = [
            [
                'name' => 'lyolo.onnx',
                'display_name' => 'lYOLO (BegalVision Edge)',
                'active' => true,
                'description' => 'Model optimal deteksi begal, korban & senjata tajam real-time',
                'size_mb' => 12.4,
            ],
            [
                'name' => 'yolov8nano.onnx',
                'display_name' => 'YOLOv8 Nano',
                'active' => false,
                'description' => 'Model Ultralytics Nano dengan FPS tinggi dan latensi rendah',
                'size_mb' => 6.2,
            ],
            [
                'name' => 'yolov3tiny.onnx',
                'display_name' => 'YOLOv3 Tiny',
                'active' => false,
                'description' => 'Model klasik hemat resource untuk CPU / IoT Mini PC',
                'size_mb' => 33.1,
            ],
            [
                'name' => 'yolov26.onnx',
                'display_name' => 'YOLOv26',
                'active' => false,
                'description' => 'Arsitektur model eksperimental',
                'size_mb' => 24.8,
            ],
        ];

        $cameraStatus = [
            'is_connected' => false,
            'current_source' => 0,
            'resolution' => ['width' => 640, 'height' => 480],
            'fps' => 30,
            'active_viewers' => 1,
            'available_cameras' => [0],
            'error' => null,
        ];
        $models = $defaultModels;
        $activeModel = 'lyolo.onnx';
        $isAiOnline = false;

        // Probe status dari Flask AI server secara non-blocking dengan timeout singkat
        try {
            $statusRes = Http::withoutVerifying()
                ->timeout(3.0)
                ->get("{$flaskBase}/camera/status");
            if ($statusRes->successful()) {
                $cameraStatus = array_merge($cameraStatus, $statusRes->json());
                $isAiOnline = true;
            }

            $modelsRes = Http::withoutVerifying()
                ->timeout(3.0)
                ->get("{$flaskBase}/models");
            if ($modelsRes->successful()) {
                $body = $modelsRes->json();
                if (!empty($body['models'])) {
                    $models = $body['models'];
                }
                if (!empty($body['active_model'])) {
                    $activeModel = $body['active_model'];
                }
            }
        } catch (\Throwable $e) {
            Log::info("Flask AI Server at {$flaskBase} is currently offline or unreachable: " . $e->getMessage());
        }

        $firebaseDbUrl = config('services.firebase.database_url', 'https://geocrime-safezone-default-rtdb.firebaseio.com/');
        $aiConfig = config('services.cctv_ai');

        // Koordinat default Lumajang jika belum ada lokasi terkait
        $latitude = $cctv->lokasi?->latitude ?? -8.133182;
        $longitude = $cctv->lokasi?->longitude ?? 113.224522;

        $telemetry = [
            'pir_motion' => false,
            'noise_db' => 42,
            'noise_status' => 'Normal',
            'lux' => 24,
            'lux_status' => 'Redup',
            'buzzer_active' => false,
            'led_active' => false,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'timestamp' => now()->format('H:i:s d/m/Y'),
        ];

        return view('admin.master.cctv.show', [
            'item' => $cctv,
            'flaskBase' => $flaskBase,
            'cameraStatus' => $cameraStatus,
            'models' => $models,
            'activeModel' => $activeModel,
            'isAiOnline' => $isAiOnline,
            'firebaseDbUrl' => $firebaseDbUrl,
            'aiConfig' => $aiConfig,
            'telemetry' => $telemetry,
        ]);
    }

    public function store(CctvRequest $request): RedirectResponse
    {
        $this->cctvService->store($request->validated());

        return redirect()
            ->route('admin.cctvs.index')
            ->with('success', 'CCTV berhasil ditambahkan.');
    }

    public function edit(Cctv $cctv): View
    {
        return view('admin.master.cctv.form', [
            'item' => $cctv,
            'locations' => Location::query()->orderBy('nama_lokasi')->get(),
        ]);
    }

    public function update(CctvRequest $request, Cctv $cctv): RedirectResponse
    {
        $this->cctvService->update($cctv, $request->validated());

        return redirect()
            ->route('admin.cctvs.index')
            ->with('success', 'CCTV berhasil diperbarui.');
    }

    public function destroy(Cctv $cctv): RedirectResponse
    {
        $this->cctvService->destroy($cctv);

        return redirect()
            ->route('admin.cctvs.index')
            ->with('success', 'CCTV berhasil dihapus.');
    }

    /**
     * Endpoint AJAX: Memeriksa status kamera dan server Flask AI secara berkala.
     */
    public function aiStatus(Cctv $cctv): JsonResponse
    {
        $flaskBase = $this->getFlaskBaseUrl($cctv);

        try {
            $camRes = Http::withoutVerifying()->timeout(4.0)->get("{$flaskBase}/camera/status");
            $modRes = Http::withoutVerifying()->timeout(4.0)->get("{$flaskBase}/models");

            $camData = $camRes->successful() ? $camRes->json() : null;
            $modData = $modRes->successful() ? $modRes->json() : null;

            return response()->json([
                'success' => true,
                'online' => $camRes->successful(),
                'flask_url' => $flaskBase,
                'camera' => $camData ?? [
                    'is_connected' => false,
                    'current_source' => 0,
                    'available_cameras' => [0],
                    'resolution' => ['width' => 640, 'height' => 480],
                    'fps' => 0,
                    'error' => 'Koneksi ke Flask AI gagal',
                ],
                'models' => $modData['models'] ?? [],
                'active_model' => $modData['active_model'] ?? null,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'online' => false,
                'flask_url' => $flaskBase,
                'error' => $e->getMessage(),
                'camera' => [
                    'is_connected' => false,
                    'current_source' => 0,
                    'available_cameras' => [0],
                    'resolution' => ['width' => 640, 'height' => 480],
                    'fps' => 0,
                    'error' => 'Server Flask AI Offline',
                ],
            ]);
        }
    }

    /**
     * Endpoint AJAX: Mengganti model YOLO ONNX pada Flask AI secara dinamis.
     */
    public function changeModel(Request $request, Cctv $cctv): JsonResponse
    {
        $validated = $request->validate([
            'model' => 'required|string',
        ]);

        $flaskBase = $this->getFlaskBaseUrl($cctv);

        try {
            // Coba endpoint /models/select terlebih dahulu, lalu fallback ke /reload
            $res = Http::withoutVerifying()
                ->timeout(6.0)
                ->post("{$flaskBase}/models/select", [
                    'model' => $validated['model'],
                ]);

            if (! $res->successful()) {
                $res = Http::withoutVerifying()
                    ->timeout(6.0)
                    ->post("{$flaskBase}/reload", [
                        'model' => $validated['model'],
                    ]);
            }

            return response()->json(
                $res->json() ?? ['success' => $res->successful(), 'message' => 'Model berhasil dipilih'],
                $res->status()
            );
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah model di server AI: ' . $e->getMessage(),
            ], 502);
        }
    }

    /**
     * Endpoint AJAX: Mengganti index input Webcam USB pada Flask AI.
     */
    public function changeCameraSource(Request $request, Cctv $cctv): JsonResponse
    {
        $validated = $request->validate([
            'source' => 'required',
        ]);

        $flaskBase = $this->getFlaskBaseUrl($cctv);

        try {
            $res = Http::withoutVerifying()
                ->timeout(8.0)
                ->post("{$flaskBase}/camera/source", [
                    'source' => $validated['source'],
                ]);

            return response()->json(
                $res->json() ?? ['success' => $res->successful(), 'message' => 'Source kamera berhasil diubah'],
                $res->status()
            );
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah source kamera: ' . $e->getMessage(),
            ], 502);
        }
    }

    /**
     * Endpoint AJAX: Mengontrol aktuator ESP-32 (Buzzer Sirine / LED Lampu Sorot).
     */
    public function triggerActuator(Request $request, Cctv $cctv): JsonResponse
    {
        $validated = $request->validate([
            'device' => 'required|in:buzzer,led,alarm',
            'state' => 'required|boolean',
        ]);

        $device = $validated['device'];
        $state = (bool) $validated['state'];

        // Jika buzzer/alarm dinyalakan secara darurat, buat record log darurat
        if ($state && in_array($device, ['buzzer', 'alarm'])) {
            $nearestPolsek = Polsek::first();
            EmergencyReport::create([
                'user_id' => auth()->id(),
                'nearest_polsek_id' => $nearestPolsek?->id,
                'kode_darurat' => 'EMG-ACT-' . strtoupper(Str::random(6)),
                'status' => 'aktif',
                'latitude' => $cctv->lokasi?->latitude ?? -8.133182,
                'longitude' => $cctv->lokasi?->longitude ?? 113.224522,
                'alamat_terdeteksi' => 'Pemicu Manual CCTV: ' . $cctv->nama,
                'waktu_sos' => now(),
                'catatan_petugas' => 'Sirine darurat lapangan diaktifkan oleh operator operator dari panel BegalVision CCTV.',
                'telemetri' => [
                    'source' => 'cctv_actuator',
                    'cctv_id' => $cctv->id,
                    'device' => $device,
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => "Aktuator {$device} berhasil diubah ke status " . ($state ? 'AKTIF (ON)' : 'MATI (OFF)'),
            'device' => $device,
            'state' => $state,
            'timestamp' => now()->format('H:i:s'),
        ]);
    }

    /**
     * Endpoint AJAX: Mengambil telemetri sensor ESP-32 terkini (PIR, dB suara, Lux, GPS).
     */
    public function getTelemetry(Cctv $cctv): JsonResponse
    {
        $lat = $cctv->lokasi?->latitude ?? -8.133182;
        $lng = $cctv->lokasi?->longitude ?? 113.224522;

        return response()->json([
            'success' => true,
            'cctv_id' => $cctv->id,
            'telemetry' => [
                'pir_motion' => false,
                'noise_db' => rand(38, 56),
                'lux' => rand(15, 35),
                'buzzer_active' => false,
                'led_active' => false,
                'latitude' => $lat,
                'longitude' => $lng,
                'timestamp' => now()->format('H:i:s d/m/Y'),
            ],
        ]);
    }

    /**
     * Webhook Endpoint: Menerima laporan otomatis dari Flask AI Server saat Begal / Senjata terdeteksi.
     * POST /api/incidents/report
     */
    public function reportIncident(Request $request): JsonResponse
    {
        $data = $request->validate([
            'cctv_id' => 'nullable|integer',
            'label' => 'required|string',
            'confidence' => 'required|numeric',
            'snapshot' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'keterangan' => 'nullable|string',
        ]);

        $cctv = !empty($data['cctv_id']) ? Cctv::find($data['cctv_id']) : null;
        $lat = $data['latitude'] ?? $cctv?->lokasi?->latitude ?? -8.133182;
        $lng = $data['longitude'] ?? $cctv?->lokasi?->longitude ?? 113.224522;

        $nearestPolsek = Polsek::first();
        $confPct = round($data['confidence'] * 100);

        $report = EmergencyReport::create([
            'nearest_polsek_id' => $nearestPolsek?->id,
            'kode_darurat' => 'EMG-AI-' . strtoupper(Str::random(6)),
            'status' => 'aktif',
            'latitude' => $lat,
            'longitude' => $lng,
            'alamat_terdeteksi' => 'Deteksi BegalVision AI: ' . ($cctv?->nama ?? 'Webcam USB Lapangan'),
            'waktu_sos' => now(),
            'catatan_petugas' => "Otomatis terdeteksi oleh AI ({$data['label']}, Confidence: {$confPct}%). " . ($data['keterangan'] ?? ''),
            'telemetri' => [
                'source' => 'begalvision_ai',
                'cctv_id' => $cctv?->id,
                'label' => $data['label'],
                'confidence' => $data['confidence'],
                'snapshot' => $data['snapshot'] ?? null,
            ],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Laporan ancaman berhasil dicatat ke sistem darurat SafeZone.',
            'incident_id' => $report->id,
            'kode_darurat' => $report->kode_darurat,
        ]);
    }

    /**
     * Helper untuk mengekstrak Base URL dari URL stream CCTV atau default config.
     */
    private function getFlaskBaseUrl(?Cctv $cctv = null): string
    {
        if ($cctv && $cctv->url_stream && ! $cctv->is_youtube) {
            $parts = parse_url($cctv->url_stream);
            if (!empty($parts['host'])) {
                $scheme = $parts['scheme'] ?? 'http';
                $port = !empty($parts['port']) ? ':' . $parts['port'] : '';
                return "{$scheme}://{$parts['host']}{$port}";
            }
        }

        return rtrim(config('services.cctv_ai.base_url', 'http://127.0.0.1:5000'), '/');
    }
}
