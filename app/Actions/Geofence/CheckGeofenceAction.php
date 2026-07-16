<?php

namespace App\Actions\Geofence;

use App\Models\Location;
use App\Models\Notification;
use App\Models\User;
use App\Services\FirebaseService;
use App\Services\SpatialService;

class CheckGeofenceAction
{
    protected SpatialService $spatialService;
    protected FirebaseService $firebaseService;

    public function __construct(SpatialService $spatialService, FirebaseService $firebaseService)
    {
        $this->spatialService = $spatialService;
        $this->firebaseService = $firebaseService;
    }

    public function execute(User $user, float $latitude, float $longitude): array
    {
        $geofences = Location::query()
            ->whereNotNull("polygon_geojson")
            ->whereIn("status_kerawanan", ["Rawan", "Sangat Rawan"])
            ->get();

        foreach ($geofences as $geofence) {
            $geometry = json_decode($geofence->polygon_geojson, true);
            if ($geometry && $this->spatialService->isPointInPolygon($latitude, $longitude, $geometry)) {
                $title = "Zona Rawan Terdeteksi";
                $message = "Perhatian! Anda memasuki wilayah " . $geofence->nama_lokasi . " yang berstatus " . $geofence->status_kerawanan . ".";

                Notification::create([
                    "user_id" => $user->id,
                    "title" => $title,
                    "message" => $message,
                    "type" => "geofence_alert",
                ]);

                if ($user->fcm_token) {
                    $this->firebaseService->sendNotification(
                        $user->fcm_token,
                        $title,
                        $message,
                        [
                            "type" => "geofence_alert",
                            "location_id" => (string) $geofence->id,
                        ]
                    );
                }

                return [
                    "inside" => true,
                    "geofence" => $geofence,
                ];
            }
        }

        return ["inside" => false];
    }
}
