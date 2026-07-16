<?php

namespace Tests\Unit;

use App\Services\SpatialService;
use PHPUnit\Framework\TestCase;

class SpatialServiceTest extends TestCase
{
    private SpatialService $spatialService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->spatialService = new SpatialService();
    }

    public function testHaversineDistance(): void
    {
        $lat1 = -6.2088;
        $lon1 = 106.8456;
        $lat2 = -6.2088;
        $lon2 = 106.8500;

        $distance = $this->spatialService->haversineDistance($lat1, $lon1, $lat2, $lon2);

        $this->assertGreaterThan(0.4, $distance);
        $this->assertLessThan(0.6, $distance);
    }

    public function testIsPointInPolygonInside(): void
    {
        $geometry = [
            "type" => "Polygon",
            "coordinates" => [
                [
                    [106.8, -6.2],
                    [106.9, -6.2],
                    [106.9, -6.3],
                    [106.8, -6.3],
                    [106.8, -6.2]
                ]
            ]
        ];

        $latInside = -6.25;
        $lonInside = 106.85;

        $this->assertTrue($this->spatialService->isPointInPolygon($latInside, $lonInside, $geometry));
    }

    public function testIsPointInPolygonOutside(): void
    {
        $geometry = [
            "type" => "Polygon",
            "coordinates" => [
                [
                    [106.8, -6.2],
                    [106.9, -6.2],
                    [106.9, -6.3],
                    [106.8, -6.3],
                    [106.8, -6.2]
                ]
            ]
        ];

        $latOutside = -6.15;
        $lonOutside = 106.85;

        $this->assertFalse($this->spatialService->isPointInPolygon($latOutside, $lonOutside, $geometry));
    }

    public function testIsPointInCircleInside(): void
    {
        $lat1 = -6.2088;
        $lon1 = 106.8456;
        $lat2 = -6.2088;
        $lon2 = 106.8460;

        $this->assertTrue($this->spatialService->isPointInCircle($lat1, $lon1, $lat2, $lon2, 100));
    }

    public function testIsPointInCircleOutside(): void
    {
        $lat1 = -6.2088;
        $lon1 = 106.8456;
        $lat2 = -6.2088;
        $lon2 = 106.8550;

        $this->assertFalse($this->spatialService->isPointInCircle($lat1, $lon1, $lat2, $lon2, 100));
    }

    public function testGetDistanceToRoute(): void
    {
        $route = [
            [-6.2088, 106.8456],
            [-6.2088, 106.8500],
            [-6.2150, 106.8500]
        ];

        $latP = -6.2088;
        $lonP = 106.8478;

        $distance = $this->spatialService->getDistanceToRoute($latP, $lonP, $route);

        $this->assertLessThan(5, $distance);
    }
}
