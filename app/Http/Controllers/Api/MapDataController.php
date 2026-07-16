<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MapDataController extends Controller
{
    public function getStatistics(Request $request)
    {
        $year = $request->input('year');
        $month = $request->input('month');
        $category = $request->input('category');
        $status = $request->input('status');

        $locations = Location::all();

        $stats = $locations->map(function ($location) use ($year, $month, $category, $status) {
            $query = $location->laporans()->with('kategori');

            if ($year) {
                $query->whereYear('created_at', $year);
            }
            if ($month) {
                $query->whereMonth('created_at', $month);
            }
            if ($category) {
                $query->where('category_id', $category);
            }
            if ($status) {
                $query->where('status', $status);
            }

            $count = $query->count();

            return [
                'id' => $location->id,
                'nama_lokasi' => $location->nama_lokasi,
                'polygon_geojson' => json_decode($location->polygon_geojson),
                'total_laporan' => $count,
                'reports' => $query->get(['id', 'latitude', 'longitude', 'judul_laporan', 'status', 'kategori_id']),
            ];
        });

        return response()->json($stats);
    }
}
