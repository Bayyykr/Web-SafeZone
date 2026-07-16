<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\CctvResource;
use App\Http\Resources\LocationResource;
use App\Models\Category;
use App\Models\Cctv;
use App\Models\Laporan;
use App\Models\Location;
use Illuminate\Http\JsonResponse;

class HomeController extends Controller
{
    public function home(): JsonResponse
    {
        $categories = CategoryResource::collection(
            Category::query()->orderBy("nama_kategori")->get()
        );
        $locations = LocationResource::collection(
            Location::query()
                ->whereNotNull("polygon_geojson")
                ->withCount("laporans")
                ->orderByDesc("laporans_count")
                ->get()
        );
        $cctvs = CctvResource::collection(
            Cctv::query()->with("lokasi")->latest()->limit(6)->get()
        );

        $mapPoints = Laporan::query()
            ->with("kategori")
            ->whereNotNull("latitude")
            ->whereNotNull("longitude")
            ->where("status", "!=", "ditolak")
            ->latest()
            ->limit(40)
            ->get()
            ->map(
                fn(Laporan $laporan) => [
                    "title" => $laporan->judul_laporan,
                    "type" => $laporan->kategori?->jenis ?? "laporan",
                    "category" => $laporan->kategori?->nama_kategori ?? "Laporan",
                    "status" => $laporan->status,
                    "lat" => (float) $laporan->latitude,
                    "lng" => (float) $laporan->longitude,
                    "color" => $laporan->kategori?->warna_marker ?: "#3159d4",
                ]
            );

        return response()->json([
            "categories" => $categories,
            "locations" => $locations,
            "cctvs" => $cctvs,
            "map_points" => $mapPoints,
        ]);
    }

    public function reportOptions(): JsonResponse
    {
        $categories = CategoryResource::collection(
            Category::query()->orderBy("nama_kategori")->get()
        );
        $locations = LocationResource::collection(
            Location::query()->orderBy("nama_lokasi")->get()
        );

        return response()->json([
            "categories" => $categories,
            "locations" => $locations,
        ]);
    }
}
