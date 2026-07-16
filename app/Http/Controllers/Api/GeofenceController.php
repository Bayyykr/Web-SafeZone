<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Notification;
use App\Services\FirebaseService;
use App\Services\SpatialService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GeofenceController extends Controller
{
    public function __construct()
    {
    }

    public function check(\App\Http\Requests\Api\CheckGeofenceRequest $request, \App\Actions\Geofence\CheckGeofenceAction $checkAction): JsonResponse
    {
        $result = $checkAction->execute(
            $request->user(),
            (float) $request->latitude,
            (float) $request->longitude
        );

        return response()->json($result);
    }
}
