<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CctvRequest;
use App\Models\Cctv;
use App\Models\EmergencyReport;
use App\Models\Location;
use App\Models\Polsek;
use App\Services\CctvService;
use App\Services\FirebaseService;
use App\Services\MultiModalFuzzyService;
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
    protected FirebaseService $firebaseService;
    protected MultiModalFuzzyService $fuzzyService;

    public function __construct(
        CctvService $cctvService,
        FirebaseService $firebaseService,
        MultiModalFuzzyService $fuzzyService
    ) {
        $this->cctvService = $cctvService;
        $this->firebaseService = $firebaseService;
        $this->fuzzyService = $fuzzyService;
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

    public function show(Cctv $cctv): \Illuminate\Http\Response
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

        $firebaseDbUrl = $this->firebaseService->getDatabaseUrl();
        $aiConfig = config('services.cctv_ai');

        // Ambil data telemetri real-time dari Firebase RTDB
        $fbTelemetry = $this->firebaseService->fetchDeviceTelemetry('BEGAL-VISION-01');

        // Koordinat CCTV dari tabel lokasi (atau fallback Lumajang)
        $cctvLat = $cctv->lokasi?->latitude ? (float) $cctv->lokasi->latitude : -8.133182;
        $cctvLng = $cctv->lokasi?->longitude ? (float) $cctv->lokasi->longitude : 113.224522;

        // Koordinat GPS IoT ESP-32 terkini dari Firebase RTDB
        $iotLat = $fbTelemetry['gps']['latitude'];
        $iotLng = $fbTelemetry['gps']['longitude'];

        // Cek apakah alat IoT berada di lokasi CCTV ini (Jarak maksimal 10 meter)
        $proximity = $this->firebaseService->checkDeviceProximity($cctvLat, $cctvLng, $iotLat, $iotLng, 10.0);

        $latitude = $iotLat ?? $cctvLat;
        $longitude = $iotLng ?? $cctvLng;

        $sensorNoise = $fbTelemetry['sensors']['noise_db'] ?? 42.0;
        $sensorLux = $fbTelemetry['sensors']['lux'] ?? 24.0;
        $sensorPir = !empty($fbTelemetry['sensors']['pir_motion']);

        $initVisualLabel = 'normal';
        $initVisualConf = 0.0;
        $hasInitBegal = false;
        $hasInitSenjata = false;
        $begalConf = 0.0;
        $senjataConf = 0.0;

        if (!empty($cameraStatus['latest_detections']) && is_array($cameraStatus['latest_detections'])) {
            foreach ($cameraStatus['latest_detections'] as $det) {
                $cName = strtolower($det['class_name'] ?? $det['class'] ?? '');
                if (str_contains($cName, 'begal')) {
                    $hasInitBegal = true;
                    $begalConf = max($begalConf, (float) ($det['confidence'] ?? 0.8));
                } elseif (str_contains($cName, 'senjata') || str_contains($cName, 'clurit') || str_contains($cName, 'pisau')) {
                    $hasInitSenjata = true;
                    $senjataConf = max($senjataConf, (float) ($det['confidence'] ?? 0.7));
                }
            }
        }

        if (!empty($cameraStatus['latest_threat'])) {
            $lt = $cameraStatus['latest_threat'];
            if (!empty($lt['has_both']) || ($lt['primary_label'] ?? '') === 'begal_bersenjata') {
                $hasInitBegal = true;
                $hasInitSenjata = true;
            } elseif (($lt['primary_label'] ?? '') === 'begal') {
                $hasInitBegal = true;
            } elseif (($lt['primary_label'] ?? '') === 'senjata') {
                $hasInitSenjata = true;
            }
        }

        // OPSI B (Proteksi Ketat): Wajib Begal + Senjata Keduanya untuk Bahaya Ekstrim
        if ($hasInitBegal && $hasInitSenjata) {
            $initVisualLabel = 'begal_bersenjata';
            $initVisualConf = max($begalConf, $senjataConf, 0.7);
        } elseif ($hasInitBegal) {
            $initVisualLabel = 'begal';
            $initVisualConf = $begalConf ?: 0.6;
        } elseif ($hasInitSenjata) {
            $initVisualLabel = 'senjata';
            $initVisualConf = $senjataConf ?: 0.6;
        }

        // Evaluasi fuzzy logic awal (sinkron dengan deteksi AI terkini)
        $fuzzyResult = $this->fuzzyService->evaluate([
            'visual_label' => $initVisualLabel,
            'visual_confidence' => $initVisualConf,
            'noise_db' => $sensorNoise,
            'lux' => $sensorLux,
            'pir' => $sensorPir,
        ]);

        $telemetry = [
            'pir_motion' => $sensorPir,
            'noise_db' => $sensorNoise,
            'raw_audio' => $fbTelemetry['sensors']['raw_audio_level'] ?? null,
            'noise_status' => ($sensorNoise >= 75) ? 'Teriakan Terdeteksi!' : (($sensorNoise >= 55) ? 'Aktivitas Suara' : 'Normal'),
            'lux' => $sensorLux,
            'lux_status' => ($sensorLux < 20) ? 'Gelap (Malam Hari)' : 'Terang Cukup',
            'buzzer_active' => !empty($fbTelemetry['actuators']['siren_active']),
            'led_active' => !empty($fbTelemetry['actuators']['led_active']),
            'is_connected' => $fbTelemetry['is_connected'],
            'mode' => $fbTelemetry['status']['mode'] ?? 'AUTO',
            'latitude' => $latitude,
            'longitude' => $longitude,
            'cctv_latitude' => $cctvLat,
            'cctv_longitude' => $cctvLng,
            'timestamp' => now()->format('H:i:s d/m/Y'),
        ];

        return response()->view('admin.master.cctv.show', [
            'item' => $cctv,
            'flaskBase' => $flaskBase,
            'cameraStatus' => $cameraStatus,
            'models' => $models,
            'activeModel' => $activeModel,
            'isAiOnline' => $isAiOnline,
            'firebaseDbUrl' => $firebaseDbUrl,
            'aiConfig' => $aiConfig,
            'telemetry' => $telemetry,
            'fuzzyResult' => $fuzzyResult,
            'fuzzyRules' => MultiModalFuzzyService::getAllRules(),
            'proximity' => $proximity,
            'cctvLat' => $cctvLat,
            'cctvLng' => $cctvLng,
            'initVisualLabel' => $initVisualLabel,
            'initVisualConf' => $initVisualConf,
            'iotLat' => $iotLat,
            'iotLng' => $iotLng,
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, post-check=0, pre-check=0')
          ->header('Pragma', 'no-cache')
          ->header('Expires', '0');
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

        // Sinkronisasi status aktuator langsung ke hardware ESP-32 via Firebase RTDB
        // Jika buzzer/alarm, set parameter begal = true/false agar dibaca IoT
        if (in_array($device, ['buzzer', 'alarm'])) {
            $this->firebaseService->setBegalAlert('BEGAL-VISION-01', $state, [
                'cctv_id' => $cctv->id,
                'cctv_nama' => $cctv->nama,
                'manual_trigger' => true,
            ]);
        } else {
            $this->firebaseService->updateDeviceActuator('BEGAL-VISION-01', $device, $state);
        }

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
                'catatan_petugas' => 'Sirine darurat lapangan diaktifkan oleh operator dari panel BegalVision CCTV (Parameter begal: true dikirim ke Firebase).',
                'telemetri' => [
                    'source' => 'cctv_actuator',
                    'cctv_id' => $cctv->id,
                    'device' => $device,
                    'begal_parameter' => true,
                    'firebase_synced' => true,
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => "Aktuator {$device} berhasil diubah ke status " . ($state ? 'AKTIF (ON)' : 'MATI (OFF)') . " (Firebase synced: begal=" . ($state ? 'true' : 'false') . ")",
            'device' => $device,
            'state' => $state,
            'begal' => $state,
            'firebase_synced' => true,
            'timestamp' => now()->format('H:i:s'),
        ]);
    }

    /**
     * Endpoint AJAX: Mengambil telemetri sensor ESP-32 terkini dari Firebase RTDB,
     * mengecek kecocokan koordinat (radius max 10 meter),
     * dan mengevaluasi Fuzzy Logic multi-modal.
     */
    public function getTelemetry(Request $request, Cctv $cctv): JsonResponse
    {
        $fbTelemetry = $this->firebaseService->fetchDeviceTelemetry('BEGAL-VISION-01');

        $visualLabel = $request->input('visual_label', 'normal');
        $visualConf = (float) $request->input('visual_confidence', 0.0);

        // Jika visual_label dari request masih normal, cek langsung sinkronisasi Flask AI terkini
        if ($visualLabel === 'normal' || empty($visualLabel)) {
            try {
                $flaskBase = $this->getFlaskBaseUrl($cctv);
                $camCheck = Http::withoutVerifying()->timeout(1.5)->get("{$flaskBase}/camera/status");
                if ($camCheck->successful()) {
                    $camJson = $camCheck->json();
                    $lt = $camJson['latest_threat'] ?? null;
                    $hasB = false;
                    $hasS = false;
                    $bConf = 0.0;
                    $sConf = 0.0;

                    if (!empty($camJson['latest_detections']) && is_array($camJson['latest_detections'])) {
                        foreach ($camJson['latest_detections'] as $det) {
                            $cName = strtolower($det['class_name'] ?? $det['class'] ?? '');
                            if (str_contains($cName, 'begal')) {
                                $hasB = true;
                                $bConf = max($bConf, (float) ($det['confidence'] ?? 0.8));
                            } elseif (str_contains($cName, 'senjata') || str_contains($cName, 'clurit') || str_contains($cName, 'pisau')) {
                                $hasS = true;
                                $sConf = max($sConf, (float) ($det['confidence'] ?? 0.7));
                            }
                        }
                    }

                    if ($lt) {
                        if (!empty($lt['has_both']) || ($lt['primary_label'] ?? '') === 'begal_bersenjata') {
                            $hasB = true;
                            $hasS = true;
                        } elseif (($lt['primary_label'] ?? '') === 'begal') {
                            $hasB = true;
                        } elseif (($lt['primary_label'] ?? '') === 'senjata') {
                            $hasS = true;
                        }
                    }

                    // OPSI B (Proteksi Ketat): Wajib Keduanya Begal + Senjata untuk Bahaya Ekstrim
                    if ($hasB && $hasS) {
                        $visualLabel = 'begal_bersenjata';
                        $visualConf = max($bConf, $sConf, 0.7);
                    } elseif ($hasB) {
                        $visualLabel = 'begal';
                        $visualConf = $bConf ?: 0.6;
                    } elseif ($hasS) {
                        $visualLabel = 'senjata';
                        $visualConf = $sConf ?: 0.6;
                    }
                }
            } catch (\Throwable $e) {}
        }

        $sensorNoise = $fbTelemetry['sensors']['noise_db'] ?? 42.0;
        $sensorLux = $fbTelemetry['sensors']['lux'] ?? 24.0;
        $sensorPir = !empty($fbTelemetry['sensors']['pir_motion']);

        $fuzzyResult = $this->fuzzyService->evaluate([
            'visual_label' => $visualLabel,
            'visual_confidence' => $visualConf,
            'noise_db' => $sensorNoise,
            'lux' => $sensorLux,
            'pir' => $sensorPir,
        ]);

        $cctvLat = $cctv->lokasi?->latitude ? (float) $cctv->lokasi->latitude : -8.133182;
        $cctvLng = $cctv->lokasi?->longitude ? (float) $cctv->lokasi->longitude : 113.224522;
        $iotLat = $fbTelemetry['gps']['latitude'];
        $iotLng = $fbTelemetry['gps']['longitude'];

        // Cek kecocokan posisi alat IoT dengan CCTV (jarak maksimal 10 meter)
        $proximity = $this->firebaseService->checkDeviceProximity(
            $cctvLat,
            $cctvLng,
            $iotLat,
            $iotLng,
            10.0 // Toleransi jarak maksimal 10 meter
        );

        $isVisualBegal = in_array(strtolower($visualLabel), ['begal_bersenjata', 'begal_senjata', 'begal+senjata']) ||
            (str_contains(strtolower($visualLabel), 'begal') && (str_contains(strtolower($visualLabel), 'senjata') || str_contains(strtolower($visualLabel), 'bersenjata')));
        $isStale = !empty($fbTelemetry['is_stale']);

        // OPSI B (Proteksi Ketat):
        // Sirine Buzzer HANYA boleh terpicu jika terdeteksi KEDUANYA (Begal + Senjata) DAN Skor Fuzzy mencapai BAHAYA (>=70%).
        if ($isStale || ! $isVisualBegal) {
            $isBegal = false;
        } else {
            $isBegal = !empty($fuzzyResult['is_alarm_triggered']) && $isVisualBegal;
        }

        // Auto-Trigger & Auto-Silence Sinkronisasi ke Hardware ESP-32 via Firebase RTDB
        if ($isBegal) {
            $this->firebaseService->setBegalAlert('BEGAL-VISION-01', true, [
                'threat_score' => $fuzzyResult['score'],
                'cctv_id' => $cctv->id,
                'cctv_nama' => $cctv->nama,
                'is_cctv_matched' => $proximity['is_matched'],
                'distance_meters' => $proximity['distance_meters'],
            ]);
        } else {
            // Active Silence: Matikan sirine di hardware seketika jika bukan kondisi bahaya kedua objek
            $this->firebaseService->setBegalAlert('BEGAL-VISION-01', false, [
                'threat_score' => $fuzzyResult['score'],
                'cctv_id' => $cctv->id,
                'cctv_nama' => $cctv->nama,
            ]);
        }

        $lat = $iotLat ?? $cctvLat;
        $lng = $iotLng ?? $cctvLng;

        return response()->json([
            'success' => true,
            'cctv_id' => $cctv->id,
            'telemetry' => [
                'pir_motion' => $sensorPir,
                'noise_db' => $sensorNoise,
                'raw_audio' => $fbTelemetry['sensors']['raw_audio_level'] ?? null,
                'noise_status' => $isStale ? 'Hening (Alat Mati / >20s)' : (($sensorNoise >= 75) ? 'Teriakan Terdeteksi!' : (($sensorNoise >= 55) ? 'Aktivitas Suara' : 'Normal')),
                'lux' => $sensorLux,
                'lux_status' => $isStale ? 'Standby (Offline)' : (($sensorLux < 20) ? 'Gelap (Malam Hari)' : 'Terang Cukup'),
                'buzzer_active' => !empty($fbTelemetry['actuators']['siren_active']),
                'begal_active' => $isBegal,
                'led_active' => !empty($fbTelemetry['actuators']['led_active']),
                'is_connected' => $fbTelemetry['is_connected'],
                'is_stale' => $isStale,
                'age_seconds' => $fbTelemetry['age_seconds'] ?? 0,
                'mode' => $fbTelemetry['status']['mode'] ?? 'AUTO',
                'latitude' => $lat,
                'longitude' => $lng,
                'timestamp' => now()->format('H:i:s d/m/Y'),
            ],
            'proximity' => $proximity,
            'fuzzy' => $fuzzyResult,
        ]);
    }

    /**
     * Endpoint AJAX: Menyelaraskan koordinat lokasi CCTV dengan posisi GPS riil IoT terkini.
     * Berguna saat alat IoT dipasang bersamaan di tiang kamera CCTV agar jaraknya menjadi 0 meter.
     * POST /admin/cctvs/{cctv}/sync-gps
     */
    public function syncGpsCoordinates(Cctv $cctv): JsonResponse
    {
        $fbTelemetry = $this->firebaseService->fetchDeviceTelemetry('BEGAL-VISION-01');
        $lat = $fbTelemetry['gps']['latitude'];
        $lng = $fbTelemetry['gps']['longitude'];

        if ($lat && $lng) {
            if ($cctv->lokasi) {
                $cctv->lokasi->update([
                    'latitude' => $lat,
                    'longitude' => $lng,
                ]);
            } else {
                $loc = Location::create([
                    'nama_lokasi' => 'Titik Kamera: ' . $cctv->nama,
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'status_kerawanan' => 'rawan',
                ]);
                $cctv->update(['lokasi_id' => $loc->id]);
            }

            return response()->json([
                'success' => true,
                'message' => "Koordinat CCTV {$cctv->nama} berhasil disamakan dengan GPS IoT: {$lat}, {$lng} (Jarak: 0 meter / Terdeteksi).",
                'latitude' => $lat,
                'longitude' => $lng,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Gagal menyamakan koordinat: GPS IoT belum mengunci sinyal (latitude/longitude kosong).',
        ], 422);
    }

    /**
     * Webhook Endpoint: Menerima laporan otomatis dari Flask AI Server saat Begal / Senjata terdeteksi.
     * Menggabungkan data sensor Firebase dengan hasil AI melalui Fuzzy Logic multi-modal.
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
        $fbTelemetry = $this->firebaseService->fetchDeviceTelemetry('BEGAL-VISION-01');

        $fuzzyResult = $this->fuzzyService->evaluate([
            'visual_label' => $data['label'],
            'visual_confidence' => (float) $data['confidence'],
            'noise_db' => $fbTelemetry['sensors']['noise_db'] ?? 42.0,
            'lux' => $fbTelemetry['sensors']['lux'] ?? 24.0,
            'pir' => !empty($fbTelemetry['sensors']['pir_motion']),
        ]);

        // Jika skor >= 70%, bunyikan alarm fisik di ESP-32
        if ($fuzzyResult['is_alarm_triggered']) {
            $this->firebaseService->updateDeviceActuator('BEGAL-VISION-01', 'alarm', true);
        }

        $lat = $data['latitude'] ?? $fbTelemetry['gps']['latitude'] ?? $cctv?->lokasi?->latitude ?? -8.133182;
        $lng = $data['longitude'] ?? $fbTelemetry['gps']['longitude'] ?? $cctv?->lokasi?->longitude ?? 113.224522;

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
            'catatan_petugas' => "Otomatis terdeteksi oleh Multi-Modal Fuzzy ({$data['label']}, Conf: {$confPct}%, Skor: {$fuzzyResult['score']}%, Rule: {$fuzzyResult['dominant_rule']['code']}). " . ($data['keterangan'] ?? ''),
            'telemetri' => [
                'source' => 'begalvision_ai_fuzzy',
                'cctv_id' => $cctv?->id,
                'label' => $data['label'],
                'confidence' => $data['confidence'],
                'snapshot' => $data['snapshot'] ?? null,
                'fuzzy' => $fuzzyResult,
                'sensors' => $fbTelemetry['sensors'] ?? null,
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
