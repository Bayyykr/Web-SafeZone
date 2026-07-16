<?php

namespace App\Services;

use App\Models\Polsek;

class SpatialService
{
    public function haversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371;
        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);

        $a = sin($latDelta / 2) ** 2 +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($lonDelta / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    public function findNearestPolsek(float $latitude, float $longitude): array
    {
        $nearest = null;
        $nearestDistance = null;

        Polsek::query()
            ->with("lokasi")
            ->get()
            ->each(function (Polsek $polsek) use ($latitude, $longitude, &$nearest, &$nearestDistance) {
                if (!$polsek->lokasi?->latitude || !$polsek->lokasi?->longitude) {
                    return;
                }

                $distance = $this->haversineDistance(
                    $latitude,
                    $longitude,
                    (float) $polsek->lokasi->latitude,
                    (float) $polsek->lokasi->longitude
                );

                if ($nearestDistance === null || $distance < $nearestDistance) {
                    $nearest = $polsek;
                    $nearestDistance = $distance;
                }
            });

        return [
            "polsek" => $nearest,
            "distance" => $nearestDistance !== null ? round($nearestDistance, 2) : null,
        ];
    }

    public function isPointInPolygon(float $latitude, float $longitude, array $geometry): bool
    {
        if (!isset($geometry["type"]) || !isset($geometry["coordinates"])) {
            return false;
        }

        $rings = [];
        if ($geometry["type"] === "Polygon") {
            $rings = $geometry["coordinates"];
        } elseif ($geometry["type"] === "MultiPolygon") {
            foreach ($geometry["coordinates"] as $polygon) {
                foreach ($polygon as $ring) {
                    $rings[] = $ring;
                }
            }
        }

        $inside = false;
        foreach ($rings as $ring) {
            $n = count($ring);
            for ($i = 0, $j = $n - 1; $i < $n; $j = $i++) {
                $xi = (float) $ring[$i][1];
                $yi = (float) $ring[$i][0];
                $xj = (float) $ring[$j][1];
                $yj = (float) $ring[$j][0];

                $intersect = (($yi > $longitude) !== ($yj > $longitude))
                    && ($latitude < ($xj - $xi) * ($longitude - $yi) / ($yj - $yi) + $xi);
                if ($intersect) {
                    $inside = !$inside;
                }
            }
        }

        return $inside;
    }

    public function isPointInCircle(float $lat1, float $lon1, float $lat2, float $lon2, float $radiusInMeters): bool
    {
        return ($this->haversineDistance($lat1, $lon1, $lat2, $lon2) * 1000) <= $radiusInMeters;
    }

    public function getDistanceToRoute(float $latitude, float $longitude, array $routePoints): float
    {
        if (empty($routePoints)) {
            return 0.0;
        }

        if (count($routePoints) === 1) {
            return $this->haversineDistance($latitude, $longitude, $routePoints[0][0], $routePoints[0][1]) * 1000;
        }

        $minDistance = null;

        for ($i = 0; $i < count($routePoints) - 1; $i++) {
            $latA = (float) $routePoints[$i][0];
            $lonA = (float) $routePoints[$i][1];
            $latB = (float) $routePoints[$i + 1][0];
            $lonB = (float) $routePoints[$i + 1][1];

            $distance = $this->getDistanceToSegment($latitude, $longitude, $latA, $lonA, $latB, $lonB);

            if ($minDistance === null || $distance < $minDistance) {
                $minDistance = $distance;
            }
        }

        return $minDistance ?? 0.0;
    }

    private function getDistanceToSegment(float $latP, float $lonP, float $latA, float $lonA, float $latB, float $lonB): float
    {
        $latRadA = deg2rad($latA);
        $latRadB = deg2rad($latB);

        $cosLat = cos(($latRadA + $latRadB) / 2);

        $ax = $lonA * $cosLat;
        $ay = $latA;
        $bx = $lonB * $cosLat;
        $by = $latB;
        $px = $lonP * $cosLat;
        $py = $latP;

        $dx = $bx - $ax;
        $dy = $by - $ay;

        if ($dx === 0.0 && $dy === 0.0) {
            return $this->haversineDistance($latP, $lonP, $latA, $lonA) * 1000;
        }

        $t = (($px - $ax) * $dx + ($py - $ay) * $dy) / ($dx * $dx + $dy * $dy);
        $t = max(0.0, min(1.0, $t));

        $closestLat = $latA + $t * ($latB - $latA);
        $closestLon = $lonA + $t * ($lonB - $lonA);

        return $this->haversineDistance($latP, $lonP, $closestLat, $closestLon) * 1000;
    }
}
