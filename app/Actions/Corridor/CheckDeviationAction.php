<?php

namespace App\Actions\Corridor;

use App\Models\CorridorSession;
use App\Models\Laporan;
use App\Models\Location;
use App\Models\Notification;
use App\Models\User;
use App\Services\FirebaseService;
use App\Services\SpatialService;
use Exception;

class CheckDeviationAction
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
        $session = CorridorSession::query()
            ->where("user_id", $user->id)
            ->where("status", "active")
            ->first();

        if (!$session) {
            throw new Exception("No active corridor session found", 404);
        }

        $distance = $this->spatialService->getDistanceToRoute($latitude, $longitude, $session->route_points);

        if ($distance > 100) {
            $isDangerous = false;

            $crimes = Laporan::query()
                ->whereIn("status", ["dikonfirmasi", "selesai"])
                ->whereNotNull("latitude")
                ->whereNotNull("longitude")
                ->get();

            foreach ($crimes as $crime) {
                $crimeDist = $this->spatialService->haversineDistance($latitude, $longitude, (float) $crime->latitude, (float) $crime->longitude) * 1000;
                if ($crimeDist <= 100) {
                    $isDangerous = true;
                    break;
                }
            }

            if (!$isDangerous) {
                $geofences = Location::query()
                    ->whereNotNull("polygon_geojson")
                    ->whereIn("status_kerawanan", ["Rawan", "Sangat Rawan"])
                    ->get();

                foreach ($geofences as $geofence) {
                    $geoJson = json_decode($geofence->polygon_geojson, true);
                    if ($geoJson && $this->spatialService->isPointInPolygon($latitude, $longitude, $geoJson)) {
                        $isDangerous = true;
                        break;
                    }
                }
            }

            if ($isDangerous) {
                $title = "Peringatan Koridor Rute";
                $message = "Anda terdeteksi menyimpang sejauh " . round($distance) . "m dari rute aman ke wilayah rawan bahaya!";

                Notification::create([
                    "user_id" => $user->id,
                    "title" => $title,
                    "message" => $message,
                    "type" => "deviation_alert",
                ]);

                if ($user->fcm_token) {
                    $this->firebaseService->sendNotification(
                        $user->fcm_token,
                        $title,
                        $message,
                        [
                            "type" => "deviation_alert",
                            "deviation_distance" => (string) $distance,
                        ]
                    );
                }

                return [
                    "deviated" => true,
                    "distance" => $distance,
                    "dangerous" => true,
                    "message" => "Anda menyimpang dari rute aman ke wilayah berbahaya!",
                ];
            }

            return [
                "deviated" => true,
                "distance" => $distance,
                "dangerous" => false,
                "message" => "Anda menyimpang dari rute aman.",
            ];
        }

        return [
            "deviated" => false,
            "distance" => $distance,
            "dangerous" => false,
        ];
    }
}
