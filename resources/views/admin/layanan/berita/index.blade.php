<x-admin-layout>
    @push('styles')
        @vite('resources/css/admin/pages/master.css')
    @endpush
    <x-slot name="header">Kelola Berita</x-slot>

    @php
        $toastType = session('success') ? 'success' : (session('error') || $errors->any() ? 'error' : null);
        $toastMessage = session('success') ?: session('error') ?: ($errors->any() ? $errors->first() : null);
        $statusClass = [
            'published' => 'bg-green-100 text-green-700',
            'draft' => 'bg-yellow-100 text-yellow-700'
        ];
        $stats = [
            'total' => \App\Models\Berita::count(),
            'published' => \App\Models\Berita::where('status', 'published')->count(),
            'draft' => \App\Models\Berita::where('status', 'draft')->count(),
        ];
    @endphp

    @if ($toastType && $toastMessage)
        <div class="toast-notification {{ $toastType }}" data-toast>
            <div class="toast-icon">{{ $toastType === 'success' ? '✓' : '!' }}</div>
            <div>
                <p class="toast-title">{{ $toastType === 'success' ? 'Berhasil' : 'Gagal' }}</p>
                <p class="toast-message">{{ $toastMessage }}</p>
            </div>
            <button type="button" data-toast-close aria-label="Tutup notifikasi">×</button>
        </div>
    @endif

    <div class="user-page">
        <div class="user-hero">
            <div class="user-hero-left">
                <div class="user-hero-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 18V6a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 6.25V7.5M6.75 6.75h.75m-.75 3h.75m-.75 0h.75m-3-3h.75m-.75 3h.75m-.75 0h.75" />
                    </svg>
                </div>
                <div>
                    <div class="user-hero-title">Kelola Berita</div>
                    <div class="user-hero-subtitle">Publikasikan informasi dan berita seputar keamanan wilayah</div>
                </div>
            </div>
            <div class="user-stats-row">
                <div class="user-stat-pill">
                    <strong>{{ $stats['total'] }}</strong> Total Berita
                </div>
                <div class="user-stat-pill pill-success">
                    <strong>{{ $stats['published'] }}</strong> Published
                </div>
                <div class="user-stat-pill pill-accent">
                    <strong>{{ $stats['draft'] }}</strong> Draft
                </div>
            </div>
        </div>

        <div class="user-toolbar">
            <form id="berita-search-form" method="GET" action="{{ route('admin.berita.index') }}">
                <div class="flex gap-2">
                    <div class="user-search-wrap">
                        <svg class="user-search-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                        <input id="berita-search-input" class="user-search-input" name="search" value="{{ request('search') }}" placeholder="Cari berita..." autocomplete="off">
                    </div>
                    <select id="berita-status-filter" class="form-select" name="status" style="height: 40px; border-radius: 10px; border: 1px solid var(--border); width: 160px;">
                        <option value="">Semua Status</option>
                        <option value="published" @selected(request('status') === 'published')>Published</option>
                        <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                    </select>
                </div>
            </form>
            <button class="btn-primary" type="button" data-modal-target="create-berita-modal">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width: 16px; height: 16px; margin-right: 4px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Buat Berita Baru
            </button>
        </div>

        <div id="berita-results">
            <div class="user-table-card">
                <table class="user-table">
                    <colgroup>
                        <col style="width: 50px;">
                        <col style="width: 35%;">
                        <col style="width: 15%;">
                        <col style="width: 15%;">
                        <col style="width: 12%;">
                        <col style="width: 10%;">
                        <col style="width: 13%;">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Judul & Isi Berita</th>
                            <th>Penulis</th>
                            <th>Wilayah</th>
                            <th>Tanggal Terbit</th>
                            <th>Status</th>
                            <th style="text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($items as $item)
                            <tr>
                                <td class="ut-no">{{ $items->firstItem() + $loop->index }}</td>
                                <td>
                                    <div class="font-bold text-slate-800 truncate" style="max-width: 320px;" title="{{ $item->judul }}">{{ $item->judul }}</div>
                                    <div class="text-xs text-slate-500 line-clamp-2 mt-1" style="max-width: 320px;" title="{{ $item->isi_berita }}">{{ $item->isi_berita }}</div>
                                </td>
                                <td>
                                    <div class="font-semibold text-slate-700 truncate" title="{{ $item->penulis?->name ?? '-' }}">{{ $item->penulis?->name ?? '-' }}</div>
                                </td>
                                <td>
                                    <span class="polsek-lokasi-badge">{{ $item->lokasi?->nama_lokasi ?? '-' }}</span>
                                </td>
                                <td>
                                    <span class="text-slate-600 font-semibold">{{ $item->published_at?->format('d/m/Y') ?? '-' }}</span>
                                </td>
                                <td>
                                    <span class="status-badge {{ $item->status === 'published' ? 'active' : 'inactive' }}">
                                        {{ ucfirst($item->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="grid grid-cols-2 gap-1 justify-center mx-auto" style="width: max-content;">
                                        <button class="btn-edit" type="button" title="Edit Berita" data-modal-target="edit-berita-modal-{{ $item->id }}" style="background: #1e3a8a;">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 14px; height: 14px;">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                                            </svg>
                                        </button>
                                        <button class="btn-edit" type="button" title="Lihat Detail" data-modal-target="detail-berita-modal-{{ $item->id }}" style="background: #0284c7;">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 14px; height: 14px;">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                            </svg>
                                        </button>
                                        @if ($item->status === 'published')
                                            <button class="btn-edit" type="button" title="Kembalikan ke Draft" data-modal-target="draft-berita-modal-{{ $item->id }}" style="background: #0ea5e9;">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 14px; height: 14px;">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
                                                </svg>
                                            </button>
                                        @else
                                            <button class="btn-edit" type="button" title="Publish Berita" data-modal-target="publish-berita-modal-{{ $item->id }}" style="background: #3b82f6;">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 14px; height: 14px;">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                                </svg>
                                            </button>
                                        @endif
                                        <button class="btn-delete" type="button" title="Hapus" data-modal-target="delete-berita-modal-{{ $item->id }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 14px; height: 14px;">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-slate-400 py-8 font-semibold">Data berita belum tersedia.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="user-table-footer">
                {{ $items->links() }}
            </div>

            <div id="create-berita-modal" class="modal-backdrop" hidden>
                <div class="modal-card">
                    <div class="modal-header">
                        <h2>Buat Berita Baru</h2>
                        <button type="button" data-modal-close="create-berita-modal">×</button>
                    </div>
                    <form class="modal-body space-y-4" method="POST" action="{{ route('admin.berita.store') }}" enctype="multipart/form-data">
                        @csrf
                        @include('admin.layanan.berita.partials.fields', ['item' => $createItem])
                        <div class="modal-footer">
                            <button class="btn-secondary" type="button" data-modal-close="create-berita-modal">Batal</button>
                            <button class="btn-primary" type="submit">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>

            @foreach ($items as $item)
                <div id="edit-berita-modal-{{ $item->id }}" class="modal-backdrop" hidden>
                    <div class="modal-card">
                        <div class="modal-header">
                            <h2>Edit Berita</h2>
                            <button type="button" data-modal-close="edit-berita-modal-{{ $item->id }}">×</button>
                        </div>
                        <form class="modal-body space-y-4" method="POST" action="{{ route('admin.berita.update', $item) }}" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            @include('admin.layanan.berita.partials.fields', ['item' => $item])
                            <div class="modal-footer">
                                <button class="btn-secondary" type="button" data-modal-close="edit-berita-modal-{{ $item->id }}">Batal</button>
                                <button class="btn-primary" type="submit">Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div id="detail-berita-modal-{{ $item->id }}" class="modal-backdrop" hidden>
                    <div class="modal-card">
                        <div class="modal-header">
                            <h2>Detail Berita</h2>
                            <button type="button" data-modal-close="detail-berita-modal-{{ $item->id }}">×</button>
                        </div>
                        <div class="modal-body space-y-4 text-slate-700">
                            @if ($item->foto)
                                <img class="max-h-72 w-full rounded-xl object-cover border border-slate-200" src="{{ asset('storage/' . $item->foto) }}" alt="{{ $item->judul }}">
                            @endif
                            <div>
                                <h3 class="text-lg font-bold text-slate-800">{{ $item->judul }}</h3>
                                <p class="text-xs text-slate-400 font-semibold mt-1">{{ $item->penulis?->name ?? '-' }} • {{ $item->lokasi?->nama_lokasi ?? '-' }} • {{ $item->published_at?->format('d/m/Y H:i') ?? 'Belum terbit' }}</p>
                            </div>
                            <div class="border-t border-slate-100 pt-3">
                                <p class="whitespace-pre-line leading-relaxed text-sm text-slate-600">{{ $item->isi_berita }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="publish-berita-modal-{{ $item->id }}" class="modal-backdrop" hidden>
                    <div class="modal-card modal-card-sm">
                        <div class="modal-header">
                            <h2>Publikasikan Berita</h2>
                            <button type="button" data-modal-close="publish-berita-modal-{{ $item->id }}">×</button>
                        </div>
                        <div class="modal-body">
                            <p class="text-sm text-slate-600 leading-relaxed">Apakah Anda yakin ingin mempublikasikan berita <strong>{{ $item->judul }}</strong>?</p>
                            <form class="modal-footer" method="POST" action="{{ route('admin.berita.publish', $item) }}">
                                @csrf
                                @method('PATCH')
                                <button class="btn-secondary" type="button" data-modal-close="publish-berita-modal-{{ $item->id }}">Batal</button>
                                <button class="btn-primary" type="submit" style="background: #3b82f6;">Publikasikan</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div id="draft-berita-modal-{{ $item->id }}" class="modal-backdrop" hidden>
                    <div class="modal-card modal-card-sm">
                        <div class="modal-header">
                            <h2>Kembalikan ke Draft</h2>
                            <button type="button" data-modal-close="draft-berita-modal-{{ $item->id }}">×</button>
                        </div>
                        <div class="modal-body">
                            <p class="text-sm text-slate-600 leading-relaxed">Apakah Anda yakin ingin mengembalikan berita <strong>{{ $item->judul }}</strong> menjadi draft kembali?</p>
                            <form class="modal-footer" method="POST" action="{{ route('admin.berita.draft', $item) }}">
                                @csrf
                                @method('PATCH')
                                <button class="btn-secondary" type="button" data-modal-close="draft-berita-modal-{{ $item->id }}">Batal</button>
                                <button class="btn-primary" type="submit" style="background: #0ea5e9;">Jadikan Draft</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div id="delete-berita-modal-{{ $item->id }}" class="modal-backdrop" hidden>
                    <div class="modal-card modal-card-sm">
                        <div class="modal-header">
                            <h2>Hapus Berita</h2>
                            <button type="button" data-modal-close="delete-berita-modal-{{ $item->id }}">×</button>
                        </div>
                        <div class="modal-body">
                            <p class="text-sm text-slate-600">Apakah Anda yakin ingin menghapus berita <strong>{{ $item->judul }}</strong> secara permanen?</p>
                            <form class="modal-footer" method="POST" action="{{ route('admin.berita.destroy', $item) }}">
                                @csrf
                                @method('DELETE')
                                <button class="btn-secondary" type="button" data-modal-close="delete-berita-modal-{{ $item->id }}">Batal</button>
                                <button class="btn-delete-text" type="submit">Hapus</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <script>
        function resetAndCloseModal(modal) {
            if (!modal) return;
            modal.querySelectorAll('form').forEach((form) => form.reset());
            modal.setAttribute('hidden', true);
        }

        const beritaSearchForm = document.getElementById('berita-search-form');
        const beritaSearchInput = document.getElementById('berita-search-input');
        const beritaStatusFilter = document.getElementById('berita-status-filter');
        let beritaSearchTimer;
        let beritaSearchController;

        async function loadBeritaUrl(url, pushState = true) {
            beritaSearchController?.abort();
            beritaSearchController = new AbortController();
            const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, signal: beritaSearchController.signal });
            const html = await response.text();
            const nextResults = new DOMParser().parseFromString(html, 'text/html').getElementById('berita-results');
            const currentResults = document.getElementById('berita-results');
            if (nextResults && currentResults) currentResults.innerHTML = nextResults.innerHTML;
            if (pushState) window.history.replaceState({}, '', url);
        }

        function buildBeritaUrl() {
            const url = new URL(beritaSearchForm.action, window.location.origin);
            const searchValue = beritaSearchInput.value.trim();
            const statusValue = beritaStatusFilter.value;
            if (searchValue) url.searchParams.set('search', searchValue);
            if (statusValue) url.searchParams.set('status', statusValue);
            return url.toString();
        }

        beritaSearchForm?.addEventListener('submit', (event) => event.preventDefault());
        beritaSearchInput?.addEventListener('input', function () {
            clearTimeout(beritaSearchTimer);
            beritaSearchTimer = setTimeout(() => loadBeritaUrl(buildBeritaUrl()).catch((error) => {
                if (error.name !== 'AbortError') console.error(error);
            }), 300);
        });
        beritaStatusFilter?.addEventListener('change', () => loadBeritaUrl(buildBeritaUrl()).catch((error) => {
            if (error.name !== 'AbortError') console.error(error);
        }));

        document.addEventListener('click', function (event) {
            const paginationLink = event.target.closest('#berita-results nav a');
            if (paginationLink) {
                event.preventDefault();
                loadBeritaUrl(paginationLink.href).catch((error) => {
                    if (error.name !== 'AbortError') console.error(error);
                });
                return;
            }
            const targetId = event.target.closest('[data-modal-target]')?.dataset.modalTarget;
            if (targetId) document.getElementById(targetId)?.removeAttribute('hidden');
            const closeId = event.target.closest('[data-modal-close]')?.dataset.modalClose;
            if (closeId) resetAndCloseModal(document.getElementById(closeId));
            if (event.target.classList.contains('modal-backdrop')) resetAndCloseModal(event.target);
            if (event.target.closest('[data-toast-close]')) event.target.closest('[data-toast]')?.remove();
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') document.querySelectorAll('.modal-backdrop:not([hidden])').forEach(resetAndCloseModal);
        });
        document.querySelectorAll('[data-toast]').forEach((toast) => setTimeout(() => toast.remove(), 4500));
    </script>
</x-admin-layout>
