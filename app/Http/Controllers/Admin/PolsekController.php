<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PolsekRequest;
use App\Models\Location;
use App\Models\Polsek;
use App\Services\PolsekService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PolsekController extends Controller
{
    protected PolsekService $polsekService;

    public function __construct(PolsekService $polsekService)
    {
        $this->polsekService = $polsekService;
    }

    public function index(Request $request): View
    {
        $items = Polsek::query()
            ->with('lokasi')
            ->when(
                $request->search,
                fn($query, $search) => $query->where(function ($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                        ->orWhere('alamat', 'like', "%{$search}%")
                        ->orWhere('telepon', 'like', "%{$search}%")
                        ->orWhereHas('lokasi', fn($q) => $q->where('nama_lokasi', 'like', "%{$search}%"));
                }),
            )
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $locations = Location::orderBy('nama_lokasi')->get();

        $stats = [
            'total' => Polsek::count(),
            'berLokasi' => Polsek::whereNotNull('lokasi_id')->count(),
        ];

        return view('admin.master.polsek.index', compact('items', 'locations', 'stats'));
    }

    public function store(PolsekRequest $request): RedirectResponse
    {
        $this->polsekService->store($request->validated());

        return redirect()->route('admin.polseks.index')->with('success', 'Polsek berhasil ditambahkan.');
    }

    public function update(PolsekRequest $request, Polsek $polsek): RedirectResponse
    {
        $this->polsekService->update($polsek, $request->validated());

        return redirect()->route('admin.polseks.index')->with('success', 'Polsek berhasil diperbarui.');
    }

    public function destroy(Polsek $polsek): RedirectResponse
    {
        $this->polsekService->destroy($polsek);

        return redirect()->route('admin.polseks.index')->with('success', 'Polsek berhasil dihapus.');
    }
}
