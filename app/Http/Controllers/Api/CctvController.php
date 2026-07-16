<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CctvResource;
use App\Models\Cctv;
use Illuminate\Http\JsonResponse;

class CctvController extends Controller
{
    public function index(): JsonResponse
    {
        $cctvs = CctvResource::collection(
            Cctv::query()
                ->with("lokasi")
                ->orderByDesc("aktif")
                ->orderBy("nama")
                ->get()
        );
        return response()->json($cctvs);
    }
}
