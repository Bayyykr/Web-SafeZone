<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBeritaRequest;
use App\Http\Requests\Admin\UpdateBeritaRequest;
use App\Models\Berita;
use App\Models\Location;
use App\Services\BeritaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BeritaController extends Controller
{
    protected BeritaService $beritaService;

    public function __construct(BeritaService $beritaService)
    {
        $this->beritaService = $beritaService;
    }

    public function index(Request $request): View
    {
        $items = Berita::query()
            ->with(['penulis', 'lokasi'])
            ->when($request->search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where('judul', 'like', "%{$search}%")
                        ->orWhere('isi_berita', 'like', "%{$search}%")
                        ->orWhereHas('penulis', fn($query) => $query->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('lokasi', fn($query) => $query->where('nama_lokasi', 'like', "%{$search}%"));
                });
            })
            ->when($request->status, fn($query, $status) => $query->where('status', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $locations = Location::query()->orderBy('nama_lokasi')->get();
        $createItem = new Berita(['status' => 'draft']);

        return view('admin.layanan.berita.index', compact('items', 'locations', 'createItem'));
    }

    public function store(StoreBeritaRequest $request): RedirectResponse
    {
        $this->beritaService->store(
            $request->validated(),
            $request->file('foto'),
            Auth::id()
        );

        return redirect()
            ->route('admin.berita.index')
            ->with('success', 'Berita berhasil ditambahkan.');
    }

    public function update(UpdateBeritaRequest $request, Berita $beritum): RedirectResponse
    {
        $this->beritaService->update(
            $beritum,
            $request->validated(),
            $request->file('foto')
        );

        return redirect()
            ->route('admin.berita.index')
            ->with('success', 'Berita berhasil diperbarui.');
    }

    public function destroy(Berita $beritum): RedirectResponse
    {
        $this->beritaService->destroy($beritum);

        return redirect()
            ->route('admin.berita.index')
            ->with('success', 'Berita berhasil dihapus.');
    }

    public function publish(Berita $berita): RedirectResponse
    {
        $this->beritaService->publish($berita);

        return redirect()
            ->route('admin.berita.index')
            ->with('success', 'Berita berhasil dipublikasikan.');
    }

    public function draft(Berita $berita): RedirectResponse
    {
        $this->beritaService->draft($berita);

        return redirect()
            ->route('admin.berita.index')
            ->with('success', 'Berita dikembalikan ke draft.');
    }
}
