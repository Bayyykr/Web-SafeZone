<x-admin-layout>
    @push('styles')
        @vite(['resources/css/admin/pages/master.css', 'resources/css/admin/pages/report.css'])
    @endpush
    <x-slot name="header">Konfirmasi Laporan</x-slot>

    @php
        $toastType = session('success') ? 'success' : (session('error') || $errors->any() ? 'error' : null);
        $toastMessage = session('success') ?: session('error') ?: ($errors->any() ? $errors->first() : null);
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
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                    </svg>
                </div>
                <div>
                    <div class="user-hero-title">Konfirmasi Laporan</div>
                    <div class="user-hero-subtitle">Validasi dan tindak lanjuti laporan keamanan masyarakat</div>
                </div>
            </div>
            <div class="user-stats-row">
                <div class="user-stat-pill">
                    <strong>{{ $stats['total'] }}</strong> Total
                </div>
                <div class="user-stat-pill pill-accent">
                    <strong>{{ $stats['pending'] }}</strong> Pending
                </div>
                <div class="user-stat-pill pill-success">
                    <strong>{{ $stats['dikonfirmasi'] }}</strong> Dikonfirmasi
                </div>
                <div class="user-stat-pill" style="background: var(--danger-bg); border-color: transparent; color: var(--danger);">
                    <strong>{{ $stats['ditolak'] }}</strong> Ditolak
                </div>
                <div class="user-stat-pill" style="background: #e0f2fe; border-color: transparent; color: #0369a1;">
                    <strong>{{ $stats['selesai'] }}</strong> Selesai
                </div>
            </div>
        </div>

        <div class="user-toolbar">
            <form id="konfirmasi-search-form" method="GET" action="{{ route('admin.konfirmasi-laporan.index') }}">
                <div class="flex gap-2">
                    <div class="user-search-wrap">
                        <svg class="user-search-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                        <input id="konfirmasi-search-input" class="user-search-input" name="search" value="{{ request('search') }}" placeholder="Cari laporan..." autocomplete="off">
                    </div>
                    <select id="konfirmasi-status-filter" class="form-select" name="status" style="height: 40px; border-radius: 10px; border: 1px solid var(--border); width: 160px;">
                        <option value="">Semua Status</option>
                        <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                        <option value="dikonfirmasi" @selected(request('status') === 'dikonfirmasi')>Dikonfirmasi</option>
                        <option value="ditolak" @selected(request('status') === 'ditolak')>Ditolak</option>
                        <option value="selesai" @selected(request('status') === 'selesai')>Selesai</option>
                    </select>
                </div>
            </form>
            <a class="btn-primary" href="{{ route('admin.konfirmasi-laporan.export', request()->query()) }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="margin-right: 4px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                Export CSV
            </a>
        </div>

        <div id="konfirmasi-results">
            <div class="user-table-card">
                <table class="user-table">
                    <thead>
                        <tr>
                            <th style="width: 60px;">No</th>
                            <th>Pengirim</th>
                            <th>Kategori & Judul</th>
                            <th style="width: 120px;">No HP</th>
                            <th style="width: 130px;">Wilayah</th>
                            <th>Tanggal</th>
                            <th style="width: 100px;">Status</th>
                            <th style="width: 100px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($items as $item)
                            <tr>
                                <td class="ut-no">{{ $items->firstItem() + $loop->index }}</td>
                                <td>
                                    <div class="user-name-cell">
                                        <div class="user-avatar">{{ mb_substr($item->user?->name ?? '?', 0, 2) }}</div>
                                        <div class="user-name-info">
                                            <strong>{{ $item->user?->name ?? '-' }}</strong>
                                            <small>{{ $item->user?->email ?? '-' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="user-name-info">
                                        <strong>{{ $item->kategori?->nama_kategori ?? '-' }}</strong>
                                        <small title="{{ $item->judul_laporan }}">{{ \Illuminate\Support\Str::limit($item->judul_laporan, 45) }}</small>
                                    </div>
                                </td>
                                <td class="ut-phone">{{ $item->user?->telepon ?? '-' }}</td>
                                <td>{{ $item->lokasi?->nama_lokasi ?? $item->polsek?->nama ?? '-' }}</td>
                                <td class="ut-email">{{ $item->created_at?->format('d/m/Y, h:i A') }}</td>
                                <td>
                                    @if ($item->status === 'pending')
                                        <span class="status-badge status-rawan">Pending</span>
                                    @elseif ($item->status === 'dikonfirmasi')
                                        <span class="status-badge active">Dikonfirmasi</span>
                                    @elseif ($item->status === 'ditolak')
                                        <span class="status-badge inactive">Ditolak</span>
                                    @elseif ($item->status === 'selesai')
                                        <span class="status-badge" style="background: #e0f2fe; color: #0369a1;">Selesai</span>
                                    @else
                                        <span class="status-badge" style="background: #f1f5f9; color: var(--muted);">{{ ucfirst($item->status) }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="user-actions">
                                        <button class="btn-edit" style="background: var(--accent);" type="button" title="Detail" data-modal-target="detail-laporan-modal-{{ $item->id }}">◉</button>
                                        <button class="btn-edit" type="button" title="Konfirmasi" data-modal-target="konfirmasi-laporan-modal-{{ $item->id }}">✓</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">
                                    <div class="user-table-empty">
                                        <div class="user-table-empty-icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3Z" />
                                            </svg>
                                        </div>
                                        <p>Data laporan belum tersedia.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($items->hasPages())
                <div class="user-table-footer" style="margin-top: 16px;">
                    {{ $items->links() }}
                </div>
            @endif

            @foreach ($items as $item)
                <div id="detail-laporan-modal-{{ $item->id }}" class="modal-backdrop" hidden>
                    <div class="modal-card">
                        <div class="modal-header">
                            <h2>Detail Laporan</h2>
                            <button type="button" data-modal-close="detail-laporan-modal-{{ $item->id }}">×</button>
                        </div>
                        <div class="modal-body">
                            <div style="display: flex; flex-direction: column; gap: 16px;">
                                @if ($item->foto_kejadian)
                                    <img class="max-h-64 w-full rounded-lg object-cover" src="{{ asset('storage/' . $item->foto_kejadian) }}" alt="Foto kejadian" style="max-height: 240px; width: 100%; object-fit: cover; border-radius: 12px;">
                                @endif
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; font-size: 13px; color: var(--text);">
                                    <div>
                                        <label style="font-weight: 700; color: var(--muted); display: block; margin-bottom: 2px;">Pengirim</label>
                                        <div>{{ $item->user?->name ?? '-' }} ({{ $item->user?->email ?? '-' }})</div>
                                    </div>
                                    <div>
                                        <label style="font-weight: 700; color: var(--muted); display: block; margin-bottom: 2px;">No HP</label>
                                        <div>{{ $item->user?->telepon ?? '-' }}</div>
                                    </div>
                                    <div>
                                        <label style="font-weight: 700; color: var(--muted); display: block; margin-bottom: 2px;">Kategori</label>
                                        <div>{{ $item->kategori?->nama_kategori ?? '-' }}</div>
                                    </div>
                                    <div>
                                        <label style="font-weight: 700; color: var(--muted); display: block; margin-bottom: 2px;">Wilayah Kecamatan</label>
                                        <div>{{ $item->lokasi?->nama_lokasi ?? '-' }}</div>
                                    </div>
                                    <div>
                                        <label style="font-weight: 700; color: var(--muted); display: block; margin-bottom: 2px;">Polsek Terkait</label>
                                        <div>{{ $item->polsek?->nama ?? '-' }}</div>
                                    </div>
                                    <div>
                                        <label style="font-weight: 700; color: var(--muted); display: block; margin-bottom: 2px;">Koordinat Lokasi</label>
                                        <div>{{ $item->latitude ?? '-' }}, {{ $item->longitude ?? '-' }}</div>
                                    </div>
                                </div>
                                <div style="border-top: 1px solid var(--border); padding-top: 16px;">
                                    <label style="font-weight: 700; color: var(--muted); display: block; margin-bottom: 4px;">{{ $item->judul_laporan }}</label>
                                    <div style="font-size: 13px; line-height: 1.5; color: var(--text); white-space: pre-line;">{{ $item->deskripsi ?: '-' }}</div>
                                </div>
                                @if ($item->konfirmasi)
                                    <div style="background: var(--page-bg); border: 1px solid var(--border); border-radius: 12px; padding: 14px;">
                                        <label style="font-weight: 700; color: var(--text); display: block; margin-bottom: 4px;">Catatan Konfirmasi Petugas</label>
                                        <div style="font-size: 13px; color: var(--text);">{{ $item->konfirmasi->catatan ?: '-' }}</div>
                                        <div style="font-size: 11px; color: var(--muted); margin-top: 8px;">
                                            Petugas: {{ $item->konfirmasi->petugas?->name ?? '-' }} &bull; {{ $item->konfirmasi->dikonfirmasi_pada?->format('d/m/Y H:i') }}
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div id="konfirmasi-laporan-modal-{{ $item->id }}" class="modal-backdrop" hidden>
                    <div class="modal-card modal-card-sm">
                        <div class="modal-header">
                            <h2>Konfirmasi Laporan</h2>
                            <button type="button" data-modal-close="konfirmasi-laporan-modal-{{ $item->id }}">×</button>
                        </div>
                        <form class="modal-body" method="POST" action="{{ route('admin.konfirmasi-laporan.update', $item) }}" style="display: flex; flex-direction: column; gap: 16px;">
                            @csrf
                            @method('PATCH')
                            <div>
                                <label class="form-label" for="status-{{ $item->id }}">Status Validasi</label>
                                <select id="status-{{ $item->id }}" class="form-select" name="status" required>
                                    <option value="dikonfirmasi" @selected($item->status === 'dikonfirmasi')>Dikonfirmasi</option>
                                    <option value="ditolak" @selected($item->status === 'ditolak')>Ditolak</option>
                                    <option value="selesai" @selected($item->status === 'selesai')>Selesai</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label" for="catatan-{{ $item->id }}">Catatan Verifikasi</label>
                                <textarea id="catatan-{{ $item->id }}" class="form-input" name="catatan" rows="4" placeholder="Tulis catatan jika diperlukan...">{{ old('catatan', $item->konfirmasi?->catatan) }}</textarea>
                            </div>
                            <div class="modal-footer" style="margin-top: 8px;">
                                <button class="btn-secondary" type="button" data-modal-close="konfirmasi-laporan-modal-{{ $item->id }}">Batal</button>
                                <button class="btn-primary" type="submit">Simpan</button>
                            </div>
                        </form>
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

        const konfirmasiSearchForm = document.getElementById('konfirmasi-search-form');
        const konfirmasiSearchInput = document.getElementById('konfirmasi-search-input');
        const konfirmasiStatusFilter = document.getElementById('konfirmasi-status-filter');
        let konfirmasiSearchTimer;
        let konfirmasiSearchController;

        async function loadKonfirmasiUrl(url, pushState = true) {
            konfirmasiSearchController?.abort();
            konfirmasiSearchController = new AbortController();
            const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, signal: konfirmasiSearchController.signal });
            const html = await response.text();
            const nextResults = new DOMParser().parseFromString(html, 'text/html').getElementById('konfirmasi-results');
            const currentResults = document.getElementById('konfirmasi-results');
            if (nextResults && currentResults) currentResults.innerHTML = nextResults.innerHTML;
            if (pushState) window.history.replaceState({}, '', url);
        }

        function buildKonfirmasiUrl() {
            const url = new URL(konfirmasiSearchForm.action, window.location.origin);
            const searchValue = konfirmasiSearchInput.value.trim();
            const statusValue = konfirmasiStatusFilter.value;
            if (searchValue) url.searchParams.set('search', searchValue);
            if (statusValue) url.searchParams.set('status', statusValue);
            return url.toString();
        }

        konfirmasiSearchForm?.addEventListener('submit', (event) => event.preventDefault());
        konfirmasiSearchInput?.addEventListener('input', function () {
            clearTimeout(konfirmasiSearchTimer);
            konfirmasiSearchTimer = setTimeout(() => loadKonfirmasiUrl(buildKonfirmasiUrl()).catch((error) => {
                if (error.name !== 'AbortError') console.error(error);
            }), 300);
        });
        konfirmasiStatusFilter?.addEventListener('change', () => loadKonfirmasiUrl(buildKonfirmasiUrl()).catch((error) => {
            if (error.name !== 'AbortError') console.error(error);
        }));

        document.addEventListener('click', function (event) {
            const paginationLink = event.target.closest('#konfirmasi-results nav a');
            if (paginationLink) {
                event.preventDefault();
                loadKonfirmasiUrl(paginationLink.href).catch((error) => {
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
