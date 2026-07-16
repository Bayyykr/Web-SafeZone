<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Services\FirebaseService;
use App\Services\SpatialService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SafeZoneController extends Controller
{
    public function __construct()
    {
    }

    public function check(\App\Http\Requests\Api\CheckSafeZoneRequest $request, \App\Actions\SafeZone\CheckSafeZoneAction $checkAction): JsonResponse
    {
        $result = $checkAction->execute(
            $request->user(),
            (float) $request->latitude,
            (float) $request->longitude
        );

        return response()->json($result);
    }
}
