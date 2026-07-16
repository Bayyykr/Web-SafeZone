<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CctvRequest;
use App\Models\Cctv;
use App\Models\Location;
use App\Services\CctvService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CctvController extends Controller
{
    protected CctvService $cctvService;

    public function __construct(CctvService $cctvService)
    {
        $this->cctvService = $cctvService;
    }

    public function index(Request $request): View
    {
        $items = Cctv::query()
            ->with('lokasi')
            ->when($request->search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where('nama', 'like', "%{$search}%")
                        ->orWhere('keterangan', 'like', "%{$search}%")
                        ->orWhereHas(
                            'lokasi',
                            fn($query) => $query->where(
                                'nama_lokasi',
                                'like',
                                "%{$search}%",
                            ),
                        );
                });
            })
            ->latest()
            ->paginate(8)
            ->withQueryString();

        $locations = Location::query()->orderBy('nama_lokasi')->get();
        $createItem = new Cctv(['aktif' => true]);

        $stats = [
            'total' => Cctv::count(),
            'aktif' => Cctv::where('aktif', true)->count(),
            'nonaktif' => Cctv::where('aktif', false)->count(),
        ];

        return view(
            'admin.master.cctv.index',
            compact('items', 'locations', 'createItem', 'stats'),
        );
    }

    public function create(): View
    {
        return view('admin.master.cctv.form', [
            'item' => new Cctv(['aktif' => true]),
            'locations' => Location::query()->orderBy('nama_lokasi')->get(),
        ]);
    }

    public function show(Cctv $cctv): View
    {
        return view('admin.master.cctv.show', [
            'item' => $cctv->load('lokasi'),
        ]);
    }

    public function store(CctvRequest $request): RedirectResponse
    {
        $this->cctvService->store($request->validated());

        return redirect()
            ->route('admin.cctvs.index')
            ->with('success', 'CCTV berhasil ditambahkan.');
    }

    public function edit(Cctv $cctv): View
    {
        return view('admin.master.cctv.form', [
            'item' => $cctv,
            'locations' => Location::query()->orderBy('nama_lokasi')->get(),
        ]);
    }

    public function update(CctvRequest $request, Cctv $cctv): RedirectResponse
    {
        $this->cctvService->update($cctv, $request->validated());

        return redirect()
            ->route('admin.cctvs.index')
            ->with('success', 'CCTV berhasil diperbarui.');
    }

    public function destroy(Cctv $cctv): RedirectResponse
    {
        $this->cctvService->destroy($cctv);

        return redirect()
            ->route('admin.cctvs.index')
            ->with('success', 'CCTV berhasil dihapus.');
    }
}
