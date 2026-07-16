<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LocationRequest;
use App\Models\Location;
use App\Services\LocationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LocationController extends Controller
{
    protected LocationService $locationService;

    public function __construct(LocationService $locationService)
    {
        $this->locationService = $locationService;
    }

    public function index(Request $request): View
    {
        $items = Location::query()
            ->when(
                $request->search,
                fn($query, $search) => $query->where(function ($query) use ($search, ) {
                    $query
                        ->where("nama_lokasi", "like", "%{$search}%")
                        ->orWhere("status_kerawanan", "like", "%{$search}%");
                }),
            )
            ->when(
                $request->status_kerawanan,
                fn($query, $status) => $query->where(
                    "status_kerawanan",
                    $status,
                ),
            )
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'total' => Location::count(),
            'aman' => Location::where('status_kerawanan', 'Aman')->count(),
            'rawan' => Location::where('status_kerawanan', 'Rawan')->count(),
            'sangat_rawan' => Location::where('status_kerawanan', 'Sangat Rawan')->count(),
        ];

        return view("admin.master.lokasi.index", compact("items", "stats"));
    }

    public function store(LocationRequest $request): RedirectResponse
    {
        $this->locationService->store($request->validated());

        return redirect()
            ->route("admin.locations.index")
            ->with("success", "Lokasi berhasil ditambahkan.");
    }

    public function update(
        LocationRequest $request,
        Location $location,
    ): RedirectResponse {
        $this->locationService->update($location, $request->validated());

        return redirect()
            ->route("admin.locations.index")
            ->with("success", "Lokasi berhasil diperbarui.");
    }

    public function destroy(Location $location): RedirectResponse
    {
        $this->locationService->destroy($location);

        return redirect()
            ->route("admin.locations.index")
            ->with("success", "Lokasi berhasil dihapus.");
    }
}
