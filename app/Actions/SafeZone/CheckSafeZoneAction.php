<?php

namespace App\Actions\SafeZone;

use App\Models\Notification;
use App\Models\User;
use App\Services\FirebaseService;
use App\Services\SpatialService;

class CheckSafeZoneAction
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
        $safeZones = \App\Models\Location::query()
            ->where("status_kerawanan", "Aman")
            ->whereNotNull("polygon_geojson")
            ->get();

        if ($safeZones->isEmpty()) {
            return ["safe" => false]; // Or whatever default makes sense if there are no safe zones
        }

        $enteredZone = null;
        foreach ($safeZones as $zone) {
            $geometry = json_decode($zone->polygon_geojson, true);
            if ($geometry && $this->spatialService->isPointInPolygon($latitude, $longitude, $geometry)) {
                $enteredZone = $zone;
                break;
            }
        }

        if ($enteredZone) {
            $title = "Zona Aman Terdeteksi";
            $message = "Anda telah masuk ke dalam Zona Aman: " . $enteredZone->nama_lokasi . ".";

            Notification::create([
                "user_id" => $user->id,
                "title" => $title,
                "message" => $message,
                "type" => "safe_zone_alert",
            ]);

            if ($user->fcm_token) {
                $this->firebaseService->sendNotification(
                    $user->fcm_token,
                    $title,
                    $message,
                    ["type" => "safe_zone_alert"]
                );
            }

            return [
                "safe" => true,
                "message" => $message,
                "zone" => $enteredZone,
            ];
        }

        return ["safe" => false];
    }
}
