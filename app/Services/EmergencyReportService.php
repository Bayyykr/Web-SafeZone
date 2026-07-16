<?php

namespace App\Services;

use App\Models\EmergencyReport;
use Illuminate\Support\Facades\Auth;

class EmergencyReportService
{
    protected SpatialService $spatialService;

    public function __construct(SpatialService $spatialService)
    {
        $this->spatialService = $spatialService;
    }

    public function generateEmergencyCode(): string
    {
        $nextNumber = EmergencyReport::whereDate("created_at", today())->count() + 1;

        return "SOS-" .
            now()->format("Ymd") .
            "-" .
            str_pad((string) $nextNumber, 3, "0", STR_PAD_LEFT);
    }

    public function dispatch(EmergencyReport $emergencyReport): bool
    {
        if ($emergencyReport->status === "aktif") {
            return $emergencyReport->update([
                "status" => "dalam_penanganan",
                "waktu_dispatch" => now(),
                "petugas_penanganan" => Auth::user()?->name,
            ]);
        }

        return false;
    }

    public function complete(EmergencyReport $emergencyReport, array $data): bool
    {
        return $emergencyReport->update([
            "status" => "selesai",
            "waktu_selesai" => now(),
            "petugas_penanganan" => $emergencyReport->petugas_penanganan ?: Auth::user()?->name,
            "catatan_petugas" => $data["catatan_petugas"],
        ]);
    }

    public function store(array $data): EmergencyReport
    {
        $nearest = $this->spatialService->findNearestPolsek(
            (float) $data["latitude"],
            (float) $data["longitude"]
        );

        return EmergencyReport::create([
            "user_id" => Auth::id(),
            "nearest_polsek_id" => $nearest["polsek"]?->id,
            "kode_darurat" => $this->generateEmergencyCode(),
            "status" => "aktif",
            "latitude" => $data["latitude"],
            "longitude" => $data["longitude"],
            "alamat_terdeteksi" => $data["alamat_terdeteksi"] ?? null,
            "jarak_polsek_km" => $nearest["distance"],
            "waktu_sos" => now(),
            "telemetri" => [
                [
                    "lat" => (float) $data["latitude"],
                    "lng" => (float) $data["longitude"],
                    "time" => now()->toIso8601String(),
                ],
            ],
        ]);
    }

    public function storeApi(array $data): EmergencyReport
    {
        $nearest = $this->spatialService->findNearestPolsek(
            (float) $data["latitude"],
            (float) $data["longitude"]
        );

        return EmergencyReport::create([
            "user_id" => Auth::id(),
            "nearest_polsek_id" => $nearest["polsek"]?->id,
            "kode_darurat" => "SOS-" . now()->format("ymdHis") . "-" . \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(4)),
            "status" => "aktif",
            "latitude" => $data["latitude"],
            "longitude" => $data["longitude"],
            "alamat_terdeteksi" => $data["alamat_terdeteksi"] ?? null,
            "jarak_polsek_km" => $nearest["distance"],
            "waktu_sos" => now(),
            "telemetri" => [
                "catatan_pengguna" => $data["catatan"] ?? null,
            ],
        ]);
    }
}
