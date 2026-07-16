<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreSosRequest;
use App\Http\Resources\EmergencyReportResource;
use App\Http\Resources\PolsekResource;
use App\Models\EmergencyReport;
use App\Models\Polsek;
use App\Services\EmergencyReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class SosController extends Controller
{
    protected EmergencyReportService $emergencyService;

    public function __construct(EmergencyReportService $emergencyService)
    {
        $this->emergencyService = $emergencyService;
    }

    public function index(): JsonResponse
    {
        $latestEmergency = null;
        $polseks = PolsekResource::collection(
            Polsek::query()->with("lokasi")->get()
        );

        if (Auth::check()) {
            $latestEmergency = EmergencyReport::query()
                ->where("user_id", Auth::id())
                ->latest("waktu_sos")
                ->first();
            $latestEmergency = $latestEmergency
                ? new EmergencyReportResource($latestEmergency)
                : null;
        }

        return response()->json([
            "latest_emergency" => $latestEmergency,
            "polseks" => $polseks,
        ]);
    }

    public function store(StoreSosRequest $request): JsonResponse
    {
        $emergencyReport = $this->emergencyService->storeApi($request->validated());

        return response()->json(
            [
                "message" => "SOS terkirim. Lokasi Anda sudah dicatat untuk ditindaklanjuti petugas.",
                "emergency_report" => new EmergencyReportResource($emergencyReport),
            ],
            201
        );
    }
}
