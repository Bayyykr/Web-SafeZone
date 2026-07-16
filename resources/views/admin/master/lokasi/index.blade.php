<x-admin-layout>
    @push('styles')
        @vite('resources/css/admin/pages/master.css')
    @endpush
    <x-slot name="header">Lokasi</x-slot>

    @php
        $toastType = session('success') ? 'success' : (session('error') || $errors->any() ? 'error' : null);
        $toastMessage = session('success') ?: session('error') ?: ($errors->any() ? $errors->first() : null);
        $statusOptions = ['Aman', 'Rawan', 'Sangat Rawan'];
        $statusBadgeClass = [
            'Aman' => 'status-aman',
            'Rawan' => 'status-rawan',
            'Sangat Rawan' => 'status-sangat-rawan',
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
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                    </svg>
                </div>
                <div>
                    <div class="user-hero-title">Manajemen Lokasi</div>
                    <div class="user-hero-subtitle">Kelola titik koordinat, area pantau, dan status tingkat kerawanan wilayah</div>
                </div>
            </div>
            <div class="user-stats-row">
                <div class="user-stat-pill">
                    <strong>{{ $stats['total'] }}</strong> Total Lokasi
                </div>
                <div class="user-stat-pill pill-success">
                    <strong>{{ $stats['aman'] }}</strong> Aman
                </div>
                <div class="user-stat-pill pill-accent">
                    <strong>{{ $stats['rawan'] }}</strong> Rawan
                </div>
                <div class="user-stat-pill pill-danger" style="background: var(--danger-bg); border-color: transparent; color: var(--danger);">
                    <strong style="color: var(--danger);">{{ $stats['sangat_rawan'] }}</strong> Sangat Rawan
                </div>
            </div>
        </div>

        <div class="user-toolbar">
            <div></div>
            <div class="toolbar-actions-right">
                <form id="lokasi-search-form" method="GET" action="{{ route('admin.locations.index') }}">
                    <div class="user-search-wrap">
                        <svg class="user-search-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                        <input
                            id="lokasi-search-input"
                            class="user-search-input"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Cari lokasi atau status..."
                            autocomplete="off">
                    </div>
                </form>
                <select id="lokasi-status-filter" class="form-select min-w-44" name="status_kerawanan">
                    <option value="">Semua Status</option>
                    @foreach ($statusOptions as $status)
                        <option value="{{ $status }}" @selected(request('status_kerawanan') === $status)>{{ $status }}</option>
                    @endforeach
                </select>
                <button class="btn-primary" type="button" data-modal-target="create-lokasi-modal">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Tambah Lokasi
                </button>
            </div>
        </div>

        <div id="lokasi-results">
            <div class="user-table-card">
                <table class="user-table">
                    <thead>
                        <tr>
                            <th width="60">No</th>
                            <th>Nama Lokasi / Area Pantau</th>
                            <th>Koordinat Pusat</th>
                            <th>Status Kerawanan</th>
                            <th>Polygon</th>
                            <th width="120">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($items as $item)
                            <tr>
                                <td>{{ $items->firstItem() + $loop->index }}</td>
                                <td class="font-semibold">{{ $item->nama_lokasi }}</td>
                                <td>{{ $item->latitude ?? '-' }}, {{ $item->longitude ?? '-' }}</td>
                                <td>
                                    <span class="status-badge {{ $statusBadgeClass[$item->status_kerawanan] ?? 'role-user' }}">
                                        {{ $item->status_kerawanan }}
                                    </span>
                                </td>
                                <td>{{ $item->polygon_geojson ? 'Tersedia' : '-' }}</td>
                                <td>
                                    <div class="user-actions">
                                        <button class="btn-edit" type="button" data-modal-target="edit-lokasi-modal-{{ $item->id }}">✎</button>
                                        <button class="btn-delete" type="button" data-modal-target="delete-lokasi-modal-{{ $item->id }}">×</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="user-table-empty">
                                        <p>Data lokasi belum tersedia.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                @if ($items->hasPages())
                    <div class="user-table-footer">
                        {{ $items->links() }}
                    </div>
                @endif
            </div>

            <div id="create-lokasi-modal" class="modal-backdrop" hidden>
                <div class="modal-card">
                    <div class="modal-header">
                        <h2>Tambah Lokasi / Area Pantau</h2>
                        <button type="button" data-modal-close="create-lokasi-modal">×</button>
                    </div>
                    <form class="modal-body space-y-4" method="POST" action="{{ route('admin.locations.store') }}">
                        @csrf
                        @include('admin.master.lokasi.partials.fields', ['item' => null])
                        <div class="modal-footer">
                            <button class="btn-secondary" type="button" data-modal-close="create-lokasi-modal">Batal</button>
                            <button class="btn-primary" type="submit">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>

            @foreach ($items as $item)
                <div id="edit-lokasi-modal-{{ $item->id }}" class="modal-backdrop" hidden>
                    <div class="modal-card">
                        <div class="modal-header">
                            <h2>Edit Lokasi / Area Pantau</h2>
                            <button type="button" data-modal-close="edit-lokasi-modal-{{ $item->id }}">×</button>
                        </div>
                        <form class="modal-body space-y-4" method="POST" action="{{ route('admin.locations.update', $item) }}">
                            @csrf
                            @method('PUT')
                            @include('admin.master.lokasi.partials.fields', ['item' => $item])
                            <div class="modal-footer">
                                <button class="btn-secondary" type="button" data-modal-close="edit-lokasi-modal-{{ $item->id }}">Batal</button>
                                <button class="btn-primary" type="submit">Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div id="delete-lokasi-modal-{{ $item->id }}" class="modal-backdrop" hidden>
                    <div class="modal-card modal-card-sm">
                        <div class="modal-header">
                            <h2>Hapus Lokasi</h2>
                            <button type="button" data-modal-close="delete-lokasi-modal-{{ $item->id }}">×</button>
                        </div>
                        <div class="modal-body">
                            <p class="text-sm text-gray-600">Yakin ingin menghapus <strong>{{ $item->nama_lokasi }}</strong>?</p>
                            <form class="modal-footer" method="POST" action="{{ route('admin.locations.destroy', $item) }}">
                                @csrf
                                @method('DELETE')
                                <button class="btn-secondary" type="button" data-modal-close="delete-lokasi-modal-{{ $item->id }}">Batal</button>
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

        const lokasiSearchForm = document.getElementById('lokasi-search-form');
        const lokasiSearchInput = document.getElementById('lokasi-search-input');
        const lokasiStatusFilter = document.getElementById('lokasi-status-filter');
        let lokasiSearchTimer;
        let lokasiSearchController;

        async function loadLokasiUrl(url, pushState = true) {
            lokasiSearchController?.abort();
            lokasiSearchController = new AbortController();

            const response = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                signal: lokasiSearchController.signal,
            });

            const html = await response.text();
            const nextDocument = new DOMParser().parseFromString(html, 'text/html');
            const nextResults = nextDocument.getElementById('lokasi-results');
            const currentResults = document.getElementById('lokasi-results');

            if (nextResults && currentResults) {
                currentResults.innerHTML = nextResults.innerHTML;
            }

            if (pushState) {
                window.history.replaceState({}, '', url);
            }
        }

        function buildLokasiUrl() {
            const url = new URL(lokasiSearchForm.action, window.location.origin);
            const searchValue = lokasiSearchInput.value.trim();
            const statusValue = lokasiStatusFilter.value;

            if (searchValue) {
                url.searchParams.set('search', searchValue);
            }

            if (statusValue) {
                url.searchParams.set('status_kerawanan', statusValue);
            }

            return url.toString();
        }

        lokasiSearchForm?.addEventListener('submit', function (event) {
            event.preventDefault();
        });

        lokasiSearchInput?.addEventListener('input', function () {
            clearTimeout(lokasiSearchTimer);

            lokasiSearchTimer = setTimeout(() => {
                loadLokasiUrl(buildLokasiUrl()).catch((error) => {
                    if (error.name !== 'AbortError') console.error(error);
                });
            }, 300);
        });

        lokasiStatusFilter?.addEventListener('change', function () {
            loadLokasiUrl(buildLokasiUrl()).catch((error) => {
                if (error.name !== 'AbortError') console.error(error);
            });
        });

        document.addEventListener('click', function (event) {
            const paginationLink = event.target.closest('#lokasi-results nav a');
            if (paginationLink) {
                event.preventDefault();
                loadLokasiUrl(paginationLink.href).catch((error) => {
                    if (error.name !== 'AbortError') console.error(error);
                });
                return;
            }

            const targetId = event.target.closest('[data-modal-target]')?.dataset.modalTarget;
            if (targetId) {
                document.getElementById(targetId)?.removeAttribute('hidden');
            }

            const closeId = event.target.closest('[data-modal-close]')?.dataset.modalClose;
            if (closeId) {
                resetAndCloseModal(document.getElementById(closeId));
            }

            if (event.target.classList.contains('modal-backdrop')) {
                resetAndCloseModal(event.target);
            }

            if (event.target.closest('[data-toast-close]')) {
                event.target.closest('[data-toast]')?.remove();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                document.querySelectorAll('.modal-backdrop:not([hidden])').forEach(resetAndCloseModal);
            }
        });

        document.querySelectorAll('[data-toast]').forEach((toast) => {
            setTimeout(() => toast.remove(), 4500);
        });
    </script>
</x-admin-layout>
