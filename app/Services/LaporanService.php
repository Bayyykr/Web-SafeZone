<?php

namespace App\Services;

use App\Models\Category;
use App\Models\EmergencyReport;
use App\Models\Laporan;
use App\Models\Location;
use Illuminate\Support\Carbon;

class LaporanService
{
    public function getInfografikData(string $selectedMonth, ?string $rawStartDate, ?string $rawEndDate): array
    {
        try {
            $selectedMonthDate = Carbon::createFromFormat("Y-m", $selectedMonth)->startOfMonth();
        } catch (\Throwable) {
            $selectedMonthDate = now()->startOfMonth();
        }

        $year = $selectedMonthDate->year;
        $startDate = $rawStartDate ? Carbon::parse($rawStartDate)->startOfDay() : $selectedMonthDate->copy()->startOfMonth();
        $endDate = $rawEndDate ? Carbon::parse($rawEndDate)->endOfDay() : $selectedMonthDate->copy()->endOfMonth();

        if ($endDate->lt($startDate)) {
            [$startDate, $endDate] = [$endDate->copy()->startOfDay(), $startDate->copy()->endOfDay()];
        }

        $monthlyCrime = array_fill(0, 12, 0);
        $monthlyAccident = array_fill(0, 12, 0);

        Laporan::query()
            ->join("categories", "categories.id", "=", "laporans.kategori_id")
            ->selectRaw("MONTH(laporans.created_at) as month_number, categories.jenis, COUNT(*) as total")
            ->whereYear("laporans.created_at", $year)
            ->groupBy("month_number", "categories.jenis")
            ->get()
            ->each(function ($row) use (&$monthlyCrime, &$monthlyAccident) {
                $index = ((int) $row->month_number) - 1;
                if ($row->jenis === "kejahatan") {
                    $monthlyCrime[$index] = (int) $row->total;
                }
                if ($row->jenis === "kecelakaan") {
                    $monthlyAccident[$index] = (int) $row->total;
                }
            });

        $locationRows = Location::query()
            ->select("locations.id", "locations.nama_lokasi")
            ->selectSub(function ($query) use ($startDate, $endDate) {
                $query->from("laporans")
                    ->join("categories", "categories.id", "=", "laporans.kategori_id")
                    ->selectRaw("COUNT(*)")
                    ->whereColumn("laporans.lokasi_id", "locations.id")
                    ->where("categories.jenis", "kejahatan")
                    ->whereBetween("laporans.created_at", [$startDate, $endDate]);
            }, "kejahatan_total")
            ->selectSub(function ($query) use ($startDate, $endDate) {
                $query->from("laporans")
                    ->join("categories", "categories.id", "=", "laporans.kategori_id")
                    ->selectRaw("COUNT(*)")
                    ->whereColumn("laporans.lokasi_id", "locations.id")
                    ->where("categories.jenis", "kecelakaan")
                    ->whereBetween("laporans.created_at", [$startDate, $endDate]);
            }, "kecelakaan_total")
            ->orderBy("nama_lokasi")
            ->get();

        $categoryRows = Category::query()
            ->select("categories.id", "categories.nama_kategori", "categories.jenis", "categories.warna_marker")
            ->selectSub(function ($query) use ($startDate, $endDate) {
                $query->from("laporans")
                    ->selectRaw("COUNT(*)")
                    ->whereColumn("laporans.kategori_id", "categories.id")
                    ->whereBetween("laporans.created_at", [$startDate, $endDate]);
            }, "laporan_total")
            ->orderByDesc("laporan_total")
            ->orderBy("nama_kategori")
            ->get();

        return [
            "selectedMonth" => $selectedMonthDate->format("Y-m"),
            "startDate" => $startDate->toDateString(),
            "endDate" => $endDate->toDateString(),
            "chartData" => [
                "monthly" => [
                    "labels" => ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
                    "kejahatan" => $monthlyCrime,
                    "kecelakaan" => $monthlyAccident,
                ],
                "locations" => [
                    "labels" => $locationRows->pluck("nama_lokasi")->values(),
                    "kejahatan" => $locationRows->pluck("kejahatan_total")->map(fn($value) => (int) $value)->values(),
                    "kecelakaan" => $locationRows->pluck("kecelakaan_total")->map(fn($value) => (int) $value)->values(),
                ],
                "categories" => [
                    "labels" => $categoryRows->pluck("nama_kategori")->values(),
                    "totals" => $categoryRows->pluck("laporan_total")->map(fn($value) => (int) $value)->values(),
                    "colors" => $categoryRows->map(fn($cat) => $cat->warna_marker ?: ($cat->jenis === "kecelakaan" ? "#45b8a9" : "#2952e3"))->values(),
                ],
            ]
        ];
    }

    public function getSummaryCards(): array
    {
        return [
            "total" => Laporan::where("status", "selesai")->count() +
                EmergencyReport::whereIn("status", ["selesai", "arsip"])->count(),
            "pending" => Laporan::where("status", "pending")->count(),
            "confirmed" => Laporan::where("status", "dikonfirmasi")->count(),
            "done" => Laporan::where("status", "selesai")->count(),
        ];
    }
}
