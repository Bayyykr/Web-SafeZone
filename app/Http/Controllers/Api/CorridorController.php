<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CorridorSession;
use App\Models\Laporan;
use App\Models\Location;
use App\Models\Notification;
use App\Services\FirebaseService;
use App\Services\SpatialService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CorridorController extends Controller
{
    public function __construct()
    {
    }

    public function start(\App\Http\Requests\Api\StartCorridorRequest $request): JsonResponse
    {
        CorridorSession::query()
            ->where("user_id", $request->user()->id)
            ->where("status", "active")
            ->update(["status" => "cancelled"]);

        $session = CorridorSession::create([
            "user_id" => $request->user()->id,
            "route_points" => $request->route_points,
            "status" => "active",
        ]);

        return response()->json([
            "message" => "Corridor session started successfully",
            "data" => $session,
        ]);
    }

    public function end(\App\Http\Requests\Api\EndCorridorRequest $request): JsonResponse
    {
        $session = CorridorSession::query()
            ->where("user_id", $request->user()->id)
            ->where("status", "active")
            ->first();

        if (!$session) {
            return response()->json([
                "message" => "No active session found",
            ], 404);
        }

        $session->update([
            "status" => $request->status,
        ]);

        return response()->json([
            "message" => "Corridor session ended successfully",
        ]);
    }

    public function checkDeviation(\App\Http\Requests\Api\CheckDeviationRequest $request, \App\Actions\Corridor\CheckDeviationAction $action): JsonResponse
    {
        try {
            $result = $action->execute(
                $request->user(),
                (float) $request->latitude,
                (float) $request->longitude
            );
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                "message" => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }
}
