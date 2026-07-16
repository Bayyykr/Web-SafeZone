<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    protected CategoryService $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function index(Request $request): View
    {
        $jenis = $this->resolveJenis($request);

        $items = Category::query()
            ->where('jenis', $jenis)
            ->when(
                $request->search,
                fn($query, $search) => $query->where(function ($q) use ($search) {
                    $q->where('nama_kategori', 'like', "%{$search}%")
                        ->orWhere('warna_marker', 'like', "%{$search}%");
                }),
            )
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'total' => Category::count(),
            'kejahatan' => Category::where('jenis', 'kejahatan')->count(),
            'kecelakaan' => Category::where('jenis', 'kecelakaan')->count(),
        ];

        return view('admin.master.kategori.index', compact('items', 'jenis', 'stats'));
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $this->categoryService->store($data);

        return redirect()
            ->route('admin.categories.index', ['jenis' => $data['jenis']])
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $data = $request->validated();
        $this->categoryService->update($category, $data);

        return redirect()
            ->route('admin.categories.index', ['jenis' => $data['jenis']])
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $jenis = $category->jenis;
        $this->categoryService->destroy($category);

        return redirect()
            ->route('admin.categories.index', ['jenis' => $jenis])
            ->with('success', 'Kategori berhasil dihapus.');
    }

    private function resolveJenis(Request $request): string
    {
        $jenis = $request->query('jenis', $request->query('tipe'));

        return in_array($jenis, ['kejahatan', 'kecelakaan'], true) ? $jenis : 'kejahatan';
    }
}
