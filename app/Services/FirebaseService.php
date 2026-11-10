<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirebaseService
{
    private function httpClient()
    {
        return Http::withOptions([
            'curl' => [
                CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4
            ]
        ]);
    }

    public function getCredentials(): ?array
    {
        // 1. Cek dari environment variable FIREBASE_CREDENTIALS_JSON (raw JSON string)
        $rawJson = env("FIREBASE_CREDENTIALS_JSON");
        if ($rawJson) {
            $data = json_decode($rawJson, true);
            if ($data && !empty($data["client_email"]) && !empty($data["private_key"])) {
                return $data;
            }
        }

        // 2. Cek dari environment variable FIREBASE_CREDENTIALS_BASE64
        $base64 = env("FIREBASE_CREDENTIALS_BASE64");
        if ($base64) {
            $decoded = base64_decode($base64, true);
            if ($decoded) {
                $data = json_decode($decoded, true);
                if ($data && !empty($data["client_email"]) && !empty($data["private_key"])) {
                    return $data;
                }
            }
        }

        // 3. Fallback ke file lokal jika ada
        $path = env("FIREBASE_CREDENTIALS_PATH", "storage/app/firebase-service-account.json");
        if ($path) {
            $fullPath = base_path($path);
            $target = file_exists($fullPath) ? $fullPath : (file_exists($path) ? $path : null);
            if ($target) {
                $content = @file_get_contents($target);
                if ($content) {
                    $data = json_decode($content, true);
                    if ($data && !empty($data["client_email"]) && !empty($data["private_key"])) {
                        return $data;
                    }
                }
            }
        }

        return null;
    }

    public function isConfigured(): bool
    {
        return $this->getCredentials() !== null;
    }

    private function getAccessToken(string $scope): ?string
    {
        $config = $this->getCredentials();
        if (!$config) {
            return null;
        }

        $clientEmail = $config["client_email"] ?? null;
        $privateKey = $config["private_key"] ?? null;
        if (!$clientEmail || !$privateKey) {
            return null;
        }

        $header = json_encode(["alg" => "RS256", "typ" => "JWT"]);
        $payload = json_encode([
            "iss" => $clientEmail,
            "scope" => $scope,
            "aud" => "https://oauth2.googleapis.com/token",
            "exp" => time() + 3600,
            "iat" => time(),
        ]);

        $base64Header = str_replace(["+", "/", "="], ["-", "_", ""], base64_encode($header));
        $base64Payload = str_replace(["+", "/", "="], ["-", "_", ""], base64_encode($payload));

        $signature = "";
        if (!openssl_sign($base64Header . "." . $base64Payload, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
            return null;
        }

        $base64Signature = str_replace(["+", "/", "="], ["-", "_", ""], base64_encode($signature));
        $jwt = $base64Header . "." . $base64Payload . "." . $base64Signature;

        $response = $this->httpClient()->asForm()->post("https://oauth2.googleapis.com/token", [
            "grant_type" => "urn:ietf:params:oauth:grant-type:jwt-bearer",
            "assertion" => $jwt,
        ]);

        if ($response->failed()) {
            return null;
        }

        return $response->json()["access_token"] ?? null;
    }

    public function sendNotification(string $deviceToken, string $title, string $body, array $data = []): bool
    {
        $config = $this->getCredentials();
        if (!$config) {
            Log::warning("Firebase credentials not found or not configured.");
            return false;
        }

        $projectId = $config["project_id"] ?? null;
        if (!$projectId) {
            Log::warning("Firebase project ID not found in credentials.");
            return false;
        }

        $accessToken = $this->getAccessToken("https://www.googleapis.com/auth/firebase.messaging");
        if (!$accessToken) {
            Log::warning("Failed to obtain Firebase access token.");
            return false;
        }

        $response = $this->httpClient()->withToken($accessToken)->post(
            "https://fcm.googleapis.com/v1/projects/" . $projectId . "/messages:send",
            [
                "message" => [
                    "token" => $deviceToken,
                    "notification" => [
                        "title" => $title,
                        "body" => $body,
                    ],
                    "data" => array_merge($data, [
                        "click_action" => "FLUTTER_NOTIFICATION_CLICK",
                    ]),
                ],
            ]
        );

        return $response->successful();
    }

    public function getFirebaseUidByEmail(string $email): ?string
    {
        $config = $this->getCredentials();
        if (!$config) {
            return null;
        }

        $projectId = $config["project_id"] ?? null;
        if (!$projectId) {
            return null;
        }

        $accessToken = $this->getAccessToken("https://www.googleapis.com/auth/cloud-platform");
        if (!$accessToken) {
            return null;
        }

        $lookupResponse = $this->httpClient()->withToken($accessToken)->post(
            "https://identitytoolkit.googleapis.com/v1/projects/" . $projectId . "/accounts:lookup",
            [
                "email" => [$email],
            ]
        );

        if ($lookupResponse->failed() || empty($lookupResponse->json()["users"])) {
            return null;
        }

        return $lookupResponse->json()["users"][0]["localId"] ?? null;
    }

    public function createUserInFirebaseAuth(string $email, string $password, string $displayName): ?string
    {
        $config = $this->getCredentials();
        if (!$config) {
            Log::warning("Firebase credentials not found or not configured.");
            return null;
        }

        $projectId = $config["project_id"] ?? null;
        if (!$projectId) {
            Log::warning("Firebase project ID not found in credentials.");
            return null;
        }

        $accessToken = $this->getAccessToken("https://www.googleapis.com/auth/cloud-platform");
        if (!$accessToken) {
            Log::warning("Failed to obtain Firebase access token.");
            return null;
        }

        $response = $this->httpClient()->withToken($accessToken)->post(
            "https://identitytoolkit.googleapis.com/v1/projects/" . $projectId . "/accounts",
            [
                "email" => $email,
                "password" => $password,
                "displayName" => $displayName,
                "emailVerified" => true,
            ]
        );

        if ($response->successful()) {
            return $response->json()["localId"] ?? null;
        }

        if ($response->status() === 409 || ($response->status() === 400 && str_contains($response->body(), "EMAIL_EXISTS"))) {
            return $this->getFirebaseUidByEmail($email);
        }

        Log::warning("Firebase Auth Error for " . $email . ": " . $response->body());
        return null;
    }

    public function updateUserPasswordInFirebaseAuth(string $email, string $newPassword): bool
    {
        $config = $this->getCredentials();
        if (!$config) {
            return false;
        }

        $projectId = $config["project_id"] ?? null;
        if (!$projectId) {
            return false;
        }

        $accessToken = $this->getAccessToken("https://www.googleapis.com/auth/cloud-platform");
        if (!$accessToken) {
            return false;
        }

        $localId = $this->getFirebaseUidByEmail($email);
        if (!$localId) {
            return false;
        }

        $updateResponse = $this->httpClient()->withToken($accessToken)->post(
            "https://identitytoolkit.googleapis.com/v1/projects/" . $projectId . "/accounts:update",
            [
                "localId" => $localId,
                "password" => $newPassword,
            ]
        );

        return $updateResponse->successful();
    }

    /**
     * Mengambil URL basis Firebase Realtime Database.
     */
    public function getDatabaseUrl(): string
    {
        return rtrim(
            config('services.firebase.database_url') ?: env('FIREBASE_DATABASE_URL', 'https://safezone-9088b-default-rtdb.asia-southeast1.firebasedatabase.app/'),
            '/'
        );
    }

    /**
     * Membaca data node dari Firebase Realtime Database melalui REST API.
     */
    public function getDatabaseNode(string $path = '/'): ?array
    {
        $cleanPath = '/' . ltrim($path, '/');
        $url = $this->getDatabaseUrl() . $cleanPath . '.json';

        try {
            $response = Http::withoutVerifying()
                ->timeout(5.0)
                ->get($url);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning("Firebase RTDB GET failed [{$url}]: " . $response->status());
        } catch (\Throwable $e) {
            Log::info("Firebase RTDB GET exception [{$url}]: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Menulis / mengupdate data node di Firebase Realtime Database melalui REST PATCH/PUT.
     */
    public function patchDatabaseNode(string $path, array $data): bool
    {
        $cleanPath = '/' . ltrim($path, '/');
        $url = $this->getDatabaseUrl() . $cleanPath . '.json';

        try {
            $response = Http::withoutVerifying()
                ->timeout(5.0)
                ->patch($url, $data);

            if ($response->successful()) {
                return true;
            }

            Log::warning("Firebase RTDB PATCH failed [{$url}]: " . $response->status());
        } catch (\Throwable $e) {
            Log::error("Firebase RTDB PATCH exception [{$url}]: " . $e->getMessage());
        }

        return false;
    }

    /**
     * Mengambil data telemetri real-time sensor ESP-32 (audio, lux, pir, status, gps).
     */
    public function fetchDeviceTelemetry(string $deviceId = 'BEGAL-VISION-01'): array
    {
        $deviceData = $this->getDatabaseNode("/devices/{$deviceId}");

        $fuzzyService = app(MultiModalFuzzyService::class);

        $sensors = $deviceData['sensors'] ?? [];
        $status = $deviceData['status'] ?? [];
        $gps = $deviceData['gps'] ?? [];
        $gpsHistory = $deviceData['gps_history'] ?? [];

        // 1. Ekstrak timestamp data terakhir dari perangkat IoT
        $lastTimestamp = null;
        if (!empty($deviceData['updated_at'])) {
            $ts = $deviceData['updated_at'];
            if (is_numeric($ts)) {
                $lastTimestamp = ($ts > 100000000000) ? (int) ($ts / 1000) : (int) $ts;
            } elseif (is_string($ts)) {
                $lastTimestamp = strtotime($ts);
            }
        } elseif (!empty($sensors['timestamp'])) {
            $ts = $sensors['timestamp'];
            $lastTimestamp = ($ts > 100000000000) ? (int) ($ts / 1000) : (int) $ts;
        } elseif (!empty($gpsHistory) && is_array($gpsHistory)) {
            $latestHist = end($gpsHistory);
            if (!empty($latestHist['timestamp'])) {
                $ts = $latestHist['timestamp'];
                $lastTimestamp = ($ts > 100000000000) ? (int) ($ts / 1000) : (int) $ts;
            }
        }

        // Cek timeout 20 detik: jika alat mati / tidak ada data terbaru melebihi 20 detik
        $currentTime = time();
        $ageSeconds = $lastTimestamp ? abs($currentTime - $lastTimestamp) : 9999;
        $isStale = ($ageSeconds > 20);

        // Ambil koordinat GPS terkini jika valid, atau dari riwayat GPS terakhir
        $lat = null;
        $lng = null;
        if (!empty($gps['valid']) && isset($gps['latitude'], $gps['longitude'])) {
            $lat = (float) $gps['latitude'];
            $lng = (float) $gps['longitude'];
        } elseif (!empty($gpsHistory) && is_array($gpsHistory)) {
            $latestHist = end($gpsHistory);
            if (!empty($latestHist['latitude']) && !empty($latestHist['longitude'])) {
                $lat = (float) $latestHist['latitude'];
                $lng = (float) $latestHist['longitude'];
            }
        }

        if ($isStale) {
            // NORMALISASI: Alat mati / data usang > 20 detik
            // Kembalikan ke nilai idle/normal agar tidak membunyikan alarm palsu
            $rawAudio = null;
            $noiseDb = 40.0; // Hening / Normal
            $lux = 50.0;     // Netral
            $pir = false;    // Diam (Tidak ada gerakan)
            $sirenActive = false;
            $isWarning = false;
            $isConnected = false;

            // Bersihkan data alarm usang di Firebase jika masih tersimpan aktif
            if (!empty($status['begal']) || !empty($status['siren_active']) || (($sensors['audio_level'] ?? 0) > 1000)) {
                try {
                    $this->patchDatabaseNode("/devices/{$deviceId}/status", [
                        'begal' => false,
                        'siren_active' => false,
                        'is_warning' => false,
                        'threat_score' => 10,
                    ]);
                    $this->patchDatabaseNode("/devices/{$deviceId}", [
                        'begal' => false,
                    ]);
                    $this->patchDatabaseNode("/devices/{$deviceId}/actuators", [
                        'alarm' => false,
                        'buzzer' => false,
                    ]);
                } catch (\Throwable $e) {}
            }
        } else {
            // Perangkat aktif dan mengirim data dalam kurun waktu <= 20 detik
            $rawAudio = $sensors['audio_level'] ?? ($sensors['sound_db'] ?? null);
            $noiseDb = $fuzzyService->convertAudioLevelToDb($rawAudio);
            $lux = isset($sensors['lux']) ? (float) $sensors['lux'] : 24.0;
            $pir = !empty($sensors['pir']);
            $sirenActive = !empty($status['siren_active']);
            $isWarning = !empty($status['is_warning']);
            $isConnected = true;
        }

        return [
            'device_id' => $deviceId,
            'is_connected' => $isConnected,
            'is_stale' => $isStale,
            'age_seconds' => $ageSeconds,
            'sensors' => [
                'raw_audio_level' => $rawAudio,
                'noise_db' => $noiseDb,
                'lux' => $lux,
                'pir_motion' => $pir,
            ],
            'actuators' => [
                'siren_active' => $sirenActive,
                'led_active' => !empty($status['led_active']),
            ],
            'status' => [
                'mode' => $status['mode'] ?? 'AUTO',
                'is_warning' => $isWarning,
                'ip' => $status['ip'] ?? null,
                'wifi_rssi' => $status['wifi_rssi'] ?? null,
                'uptime_s' => $status['uptime_s'] ?? null,
                'age_seconds' => $ageSeconds,
            ],
            'gps' => [
                'latitude' => $lat,
                'longitude' => $lng,
                'valid' => !empty($lat) && !empty($lng),
                'satellites' => $gps['satellites'] ?? 0,
            ],
            'last_sos' => $deviceData['last_sos'] ?? null,
            'raw' => $deviceData,
        ];
    }

    /**
     * Mengatur aktuator fisik ESP-32 (Sirine Alarm atau Lampu LED) melalui Firebase RTDB.
     */
    public function updateDeviceActuator(string $deviceId, string $device, bool $state): bool
    {
        $normalizedDevice = strtolower(trim($device));

        if (in_array($normalizedDevice, ['buzzer', 'alarm', 'siren', 'begal'])) {
            // Update status node dengan parameter begal dan sirine
            $this->patchDatabaseNode("/devices/{$deviceId}/status", [
                'begal' => $state,
                'siren_active' => $state,
                'is_warning' => $state,
                'updated_at' => now()->toIso8601String(),
            ]);

            // Update level root device agar IoT ESP-32 dapat membaca /devices/{deviceId}/begal secara langsung
            $this->patchDatabaseNode("/devices/{$deviceId}", [
                'begal' => $state,
            ]);

            // Update actuators node untuk kompatibilitas skema alternatif ESP-32
            $this->patchDatabaseNode("/devices/{$deviceId}/actuators", [
                'alarm' => $state,
                'buzzer' => $state,
                'updated_at' => now()->toIso8601String(),
            ]);

            return true;
        }

        if (in_array($normalizedDevice, ['led', 'lamp', 'lampu'])) {
            $this->patchDatabaseNode("/devices/{$deviceId}/status", [
                'led_active' => $state,
            ]);

            $this->patchDatabaseNode("/devices/{$deviceId}/actuators", [
                'led' => $state,
                'updated_at' => now()->toIso8601String(),
            ]);

            return true;
        }

        return false;
    }

    /**
     * Mengirimkan notifikasi bahaya begal langsung ke Firebase RTDB
     * agar dapat dibaca oleh alat IoT ESP-32 (parameter: begal = true / false).
     */
    public function setBegalAlert(string $deviceId, bool $begalState, array $extra = []): bool
    {
        $payload = array_merge([
            'begal' => $begalState,
            'siren_active' => $begalState,
            'is_warning' => $begalState,
            'alert_time' => now()->toIso8601String(),
        ], $extra);

        $this->patchDatabaseNode("/devices/{$deviceId}/status", $payload);

        $this->patchDatabaseNode("/devices/{$deviceId}", [
            'begal' => $begalState,
        ]);

        $this->patchDatabaseNode("/devices/{$deviceId}/actuators", [
            'alarm' => $begalState,
            'buzzer' => $begalState,
            'updated_at' => now()->toIso8601String(),
        ]);

        return true;
    }

    /**
     * Menghitung jarak fisik geodesik (dalam meter) antara dua titik koordinat GPS
     * menggunakan Rumus Haversine.
     */
    public function calculateHaversineDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000.0; // Radius rata-rata bumi dalam meter

        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lng1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lng2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(
            pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)
        ));

        return $angle * $earthRadius;
    }

    /**
     * Memeriksa apakah alat IoT berada di lokasi CCTV tertentu
     * berdasarkan kecocokan koordinat GPS dengan toleransi jarak maksimum (default 10 meter).
     */
    public function checkDeviceProximity(float $cctvLat, float $cctvLng, ?float $deviceLat, ?float $deviceLng, float $maxThresholdMeters = 10.0): array
    {
        if ($deviceLat === null || $deviceLng === null) {
            return [
                'is_matched' => false,
                'distance_meters' => null,
                'max_threshold_meters' => $maxThresholdMeters,
                'message' => 'Koordinat GPS IoT belum terkunci (satelit standby)',
            ];
        }

        $distance = $this->calculateHaversineDistance($cctvLat, $cctvLng, $deviceLat, $deviceLng);
        $isMatched = ($distance <= $maxThresholdMeters);

        return [
            'is_matched' => $isMatched,
            'distance_meters' => round($distance, 1),
            'max_threshold_meters' => $maxThresholdMeters,
            'message' => $isMatched
                ? "Alat IoT terdeteksi berada di lokasi CCTV ini (Jarak: " . round($distance, 1) . " m ≤ {$maxThresholdMeters} m)"
                : "Alat IoT berada di luar jangkauan CCTV ini (Jarak: " . round($distance, 1) . " m > {$maxThresholdMeters} m)",
        ];
    }
}
