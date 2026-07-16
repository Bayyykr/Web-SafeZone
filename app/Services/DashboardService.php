<?php

namespace App\Services;

use App\Models\Category;
use App\Models\EmergencyReport;
use App\Models\Laporan;
use App\Models\Location;

class DashboardService
{
    public function getChartData(): array
    {
        $year = now()->year;
        $monthLabels = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

        $laporansThisYear = Laporan::query()
            ->with(["kategori", "lokasi", "polsek"])
            ->whereYear("created_at", $year)
            ->get();

        $monthlyCrime = array_fill(0, 12, 0);
        $monthlyAccident = array_fill(0, 12, 0);

        foreach ($laporansThisYear as $laporan) {
            $monthIndex = ((int) $laporan->created_at->format("n")) - 1;

            if ($laporan->kategori?->jenis === "kejahatan") {
                $monthlyCrime[$monthIndex]++;
            }

            if ($laporan->kategori?->jenis === "kecelakaan") {
                $monthlyAccident[$monthIndex]++;
            }
        }

        $locationRows = Location::query()
            ->withCount([
                "laporans as laporan_total" => fn($query) => $query->whereYear("created_at", $year),
            ])
            ->orderByDesc("laporan_total")
            ->orderBy("nama_lokasi")
            ->limit(5)
            ->get();

        $categoryRows = Category::query()
            ->withCount([
                "laporans as laporan_total" => fn($query) => $query->whereYear("created_at", $year),
            ])
            ->orderByDesc("laporan_total")
            ->orderBy("nama_kategori")
            ->limit(6)
            ->get();

        return [
            "monthly" => [
                "labels" => $monthLabels,
                "kejahatan" => $monthlyCrime,
                "kecelakaan" => $monthlyAccident,
            ],
            "locations" => [
                "labels" => $locationRows->pluck("nama_lokasi")->values()->toArray(),
                "totals" => $locationRows->pluck("laporan_total")->map(fn($value) => (int) $value)->values()->toArray(),
            ],
            "categories" => [
                "labels" => $categoryRows->pluck("nama_kategori")->values()->toArray(),
                "totals" => $categoryRows->pluck("laporan_total")->map(fn($value) => (int) $value)->values()->toArray(),
                "colors" => $categoryRows->map(fn($category) => $category->warna_marker ?: ($category->jenis === "kecelakaan" ? "#45b8a9" : "#2952e3"))->values()->toArray(),
            ],
        ];
    }

    public function getMapData(): array
    {
        $currentMonthStart = now()->startOfMonth();
        $currentMonthEnd = now()->endOfMonth();
        $previousMonthStart = now()->subMonth()->startOfMonth();
        $previousMonthEnd = now()->subMonth()->endOfMonth();

        $activeEmergencyReports = EmergencyReport::query()
            ->with(["user", "nearestPolsek"])
            ->whereIn("status", ["aktif", "dalam_penanganan"])
            ->latest("waktu_sos")
            ->get();

        $latestLaporans = Laporan::query()
            ->with(["kategori", "lokasi", "polsek"])
            ->whereIn("status", ["pending", "dikonfirmasi"])
            ->whereNotNull("latitude")
            ->whereNotNull("longitude")
            ->latest()
            ->limit(30)
            ->get();

        $mapPoints = $latestLaporans
            ->map(fn($laporan) => [
                "type" => "laporan",
                "title" => $laporan->judul_laporan,
                "subtitle" => $laporan->kategori?->nama_kategori ?? "Laporan",
                "status" => $laporan->status,
                "lat" => (float) $laporan->latitude,
                "lng" => (float) $laporan->longitude,
                "color" => $laporan->kategori?->warna_marker ?: ($laporan->kategori?->jenis === "kecelakaan" ? "#45b8a9" : "#2952e3"),
            ])
            ->merge(
                $activeEmergencyReports->map(fn($report) => [
                    "type" => "sos",
                    "title" => $report->kode_darurat,
                    "subtitle" => $report->alamat_terdeteksi ?? "SOS Darurat",
                    "status" => $report->status,
                    "lat" => (float) $report->latitude,
                    "lng" => (float) $report->longitude,
                    "color" => "#ef4444",
                ])
            )
            ->values()
            ->toArray();

        $locationRiskRows = Location::query()
            ->withCount([
                "laporans as monthly_valid_reports_count" => fn($query) => $query
                    ->whereBetween("created_at", [$currentMonthStart, $currentMonthEnd])
                    ->where("status", "!=", "ditolak"),
                "laporans as previous_month_valid_reports_count" => fn($query) => $query
                    ->whereBetween("created_at", [$previousMonthStart, $previousMonthEnd])
                    ->where("status", "!=", "ditolak"),
            ])
            ->whereNotNull("latitude")
            ->whereNotNull("longitude")
            ->get();

        $mapAreas = $locationRiskRows
            ->filter(fn($location) => $location->monthly_valid_reports_count > 0 || $location->previous_month_valid_reports_count > 0)
            ->map(function ($location) {
                $count = (int) $location->monthly_valid_reports_count;
                $previousCount = (int) $location->previous_month_valid_reports_count;
                $difference = $count - $previousCount;
                $trend = $difference > 0 ? "meningkat" : ($difference < 0 ? "menurun" : "stabil");
                $visualCount = max($count, 1);

                if ($visualCount >= 20) {
                    $status = "Kritis";
                    $color = "#7f1d1d";
                    $opacity = 0.52;
                    $radius = 4500;
                } elseif ($visualCount >= 10) {
                    $status = "Sangat Rawan";
                    $color = "#dc2626";
                    $opacity = 0.38;
                    $radius = 3800;
                } else {
                    $status = "Rawan";
                    $color = "#f97316";
                    $opacity = 0.16 + min($visualCount / 10, 1) * 0.14;
                    $radius = 1800 + min($visualCount / 10, 1) * 1400;
                }

                return [
                    "name" => $location->nama_lokasi,
                    "status" => $status,
                    "range" => $visualCount >= 20 ? ">= 20 laporan" : ($visualCount >= 10 ? "10-19 laporan" : "1-9 laporan"),
                    "total" => $count,
                    "previous_total" => $previousCount,
                    "difference" => $difference,
                    "trend" => $trend,
                    "lat" => (float) $location->latitude,
                    "lng" => (float) $location->longitude,
                    "radius" => $radius,
                    "color" => $color,
                    "opacity" => $opacity,
                    "geojson" => $location->polygon_geojson ? json_decode($location->polygon_geojson, true) : null,
                ];
            })
            ->values()
            ->toArray();

        return [
            "points" => $mapPoints,
            "areas" => $mapAreas,
        ];
    }

    public function getLaporanStats(): array
    {
        return [
            "ditangani" => Laporan::where("status", "dikonfirmasi")->count(),
            "menunggu" => Laporan::where("status", "pending")->count(),
            "selesai" => Laporan::where("status", "selesai")->count(),
        ];
    }
}
