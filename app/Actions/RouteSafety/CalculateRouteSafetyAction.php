<?php

namespace App\Actions\RouteSafety;

use App\Models\Laporan;
use App\Models\Location;
use App\Services\SpatialService;
use Illuminate\Support\Facades\Http;
use Exception;

class CalculateRouteSafetyAction
{
    protected SpatialService $spatialService;

    public function __construct(SpatialService $spatialService)
    {
        $this->spatialService = $spatialService;
    }

    public function execute(float $originLat, float $originLng, float $destLat, float $destLng): array
    {
        $url = "https://router.project-osrm.org/route/v1/driving/"
            . $originLng . "," . $originLat . ";" . $destLng . "," . $destLat
            . "?geometries=geojson&overview=full";

        $response = Http::get($url);
        if ($response->failed()) {
            throw new Exception("Failed to fetch routing data", 502);
        }

        $data = $response->json();
        if (empty($data["routes"])) {
            throw new Exception("No route found", 404);
        }

        $geometry = $data["routes"][0]["geometry"];
        $rawCoords = $geometry["coordinates"];
        $routePoints = [];
        foreach ($rawCoords as $coord) {
            $routePoints[] = [(float) $coord[1], (float) $coord[0]];
        }

        $crimes = Laporan::query()
            ->whereIn("status", ["dikonfirmasi", "selesai"])
            ->whereNotNull("latitude")
            ->whereNotNull("longitude")
            ->get();

        $crimeCount = 0;
        foreach ($crimes as $crime) {
            $distance = $this->spatialService->getDistanceToRoute(
                (float) $crime->latitude,
                (float) $crime->longitude,
                $routePoints
            );
            if ($distance <= 200) {
                $crimeCount++;
            }
        }

        $score = 100 - ($crimeCount * 10);

        $geofences = Location::query()
            ->whereNotNull("polygon_geojson")
            ->whereIn("status_kerawanan", ["Rawan", "Sangat Rawan"])
            ->get();

        $intersectedRawan = false;
        $intersectedSangatRawan = false;

        foreach ($routePoints as $point) {
            foreach ($geofences as $geofence) {
                $geoJson = json_decode($geofence->polygon_geojson, true);
                if ($geoJson && $this->spatialService->isPointInPolygon($point[0], $point[1], $geoJson)) {
                    if ($geofence->status_kerawanan === "Sangat Rawan") {
                        $intersectedSangatRawan = true;
                    } elseif ($geofence->status_kerawanan === "Rawan") {
                        $intersectedRawan = true;
                    }
                }
            }
        }

        if ($intersectedSangatRawan) {
            $score -= 30;
        } elseif ($intersectedRawan) {
            $score -= 15;
        }

        $score = max(0, $score);

        $color = "Green";
        if ($score < 50) {
            $color = "Red";
        } elseif ($score < 80) {
            $color = "Yellow";
        }

        return [
            "route" => $routePoints,
            "safety_score" => $score,
            "color" => $color,
            "nearby_crimes_count" => $crimeCount,
            "distance_meters" => $data["routes"][0]["distance"] ?? 0,
            "duration_seconds" => $data["routes"][0]["duration"] ?? 0,
        ];
    }
}
