<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreReportRequest;
use App\Http\Resources\LaporanResource;
use App\Models\Laporan;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function index(): JsonResponse
    {
        $laporans = LaporanResource::collection(
            Laporan::query()
                ->with("kategori")
                ->where("user_id", Auth::id())
                ->latest()
                ->get()
        );

        return response()->json($laporans);
    }

    public function store(StoreReportRequest $request, \App\Actions\Report\CreateReportAction $action): JsonResponse
    {
        $laporan = $action->execute(
            Auth::user(),
            $request->validated(),
            $request->file("foto_kejadian")
        );

        return response()->json(
            [
                "message" => "Laporan berhasil dikirim.",
                "laporan" => new LaporanResource($laporan),
            ],
            201
        );
    }
}
