<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Laporan;
use App\Models\Location;
use App\Services\SpatialService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class RouteSafetyController extends Controller
{
    public function __construct()
    {
    }

    public function calculate(\App\Http\Requests\Api\CalculateRouteSafetyRequest $request, \App\Actions\RouteSafety\CalculateRouteSafetyAction $action): JsonResponse
    {
        try {
            $result = $action->execute(
                (float) $request->origin_latitude,
                (float) $request->origin_longitude,
                (float) $request->destination_latitude,
                (float) $request->destination_longitude
            );
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                "message" => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }
}
