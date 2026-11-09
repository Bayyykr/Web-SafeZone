<x-admin-layout>
    @push('styles')
        @vite(['resources/css/admin/pages/master.css', 'resources/css/admin/pages/cctv.css'])
    @endpush
    <x-slot name="header">CCTV Real-time Lumajang</x-slot>

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
                        <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z" />
                    </svg>
                </div>
                <div>
                    <div class="user-hero-title">Manajemen CCTV</div>
                    <div class="user-hero-subtitle">Kelola dan pantau live streaming CCTV di wilayah Kabupaten Lumajang</div>
                </div>
            </div>
            <div class="user-stats-row">
                <div class="user-stat-pill">
                    <strong>{{ $stats['total'] }}</strong> Total CCTV
                </div>
                <div class="user-stat-pill pill-success">
                    <strong>{{ $stats['aktif'] }}</strong> Aktif
                </div>
                <div class="user-stat-pill pill-accent">
                    <strong>{{ $stats['nonaktif'] }}</strong> Nonaktif
                </div>
                <div class="user-stat-pill" style="border-color: #c7d2fe; background: #eef2ff; color: #4338ca;">
                    <strong>{{ $stats['ai_streams'] ?? 0 }}</strong> BegalVision AI
                </div>
            </div>
        </div>

        <div class="user-toolbar">
            <div></div>
            <div class="toolbar-actions-right">
                <form id="cctv-search-form" method="GET" action="{{ route('admin.cctvs.index') }}">
                    <div class="user-search-wrap">
                        <svg class="user-search-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                        <input
                            id="cctv-search-input"
                            class="user-search-input"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Cari CCTV..."
                            autocomplete="off">
                    </div>
                </form>
                <a class="btn-secondary" href="{{ route('admin.cctvs.index') }}">
                    Reset
                </a>
                <button class="btn-primary" type="button" data-modal-target="create-cctv-modal">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Tambah CCTV
                </button>
            </div>
        </div>

        <div id="cctv-results">
            <div class="cctv-grid">
                @forelse ($items as $item)
                    <article class="cctv-card">
                        <div class="cctv-stream">
                            @if ($item->is_youtube && $item->embed_url)
                                <iframe
                                    src="{{ $item->embed_url }}"
                                    title="Live Streaming {{ $item->nama }}"
                                    loading="lazy"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                    allowfullscreen>
                                </iframe>
                            @elseif ($item->is_mjpeg && $item->url_stream)
                                <img
                                    src="{{ $item->url_stream }}"
                                    alt="Live Streaming {{ $item->nama }}"
                                    class="w-full h-full object-cover"
                                    loading="lazy"
                                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                />
                                <div class="cctv-empty-preview" style="display: none;">
                                    <svg width="34" height="34" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z" />
                                    </svg>
                                    <span>Stream BegalVision Menghubungkan...</span>
                                    <a href="{{ route('admin.cctvs.show', $item) }}" style="color: #60a5fa; text-decoration: underline; font-size: 11px;">Buka Pemantauan Live</a>
                                </div>
                            @elseif ($item->embed_url)
                                <iframe
                                    src="{{ $item->embed_url }}"
                                    title="Live Streaming {{ $item->nama }}"
                                    loading="lazy"
                                    allowfullscreen>
                                </iframe>
                            @else
                                <div class="cctv-empty-preview">
                                    <svg width="34" height="34" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 10l5-3v10l-5-3v-4ZM4 7h11v10H4z" />
                                    </svg>
                                    <span>Link live streaming belum diisi</span>
                                </div>
                            @endif

                            <div style="position: absolute; top: 10px; left: 10px; display: flex; align-items: center; gap: 6px; z-index: 5;">
                                <span class="cctv-live-badge {{ $item->aktif ? '' : 'style-off' }}">
                                    <span></span>
                                    {{ $item->aktif ? 'LIVE' : 'OFF' }}
                                </span>
                                @if ($item->is_begalvision)
                                    <span style="background: rgba(79, 70, 229, 0.9); color: #fff; font-size: 9px; font-weight: 800; padding: 3px 6px; border-radius: 4px; letter-spacing: 0.5px; display: inline-flex; align-items: center; gap: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.3);">
                                        <svg style="width: 10px; height: 10px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                        AI DETECT
                                    </span>
                                @endif
                            </div>
                            <span class="cctv-time">{{ now()->format('d/m/Y, h:i:s A') }}</span>
                        </div>

                        <div class="cctv-card-footer">
                            <div class="min-w-0" style="flex: 1;">
                                <a class="cctv-title-link" href="{{ route('admin.cctvs.show', $item) }}" title="{{ $item->nama }}">{{ $item->nama }}</a>
                                <p title="{{ $item->keterangan ?: ($item->lokasi?->nama_lokasi ?? ($item->aktif ? 'Online' : 'Nonaktif')) }}">
                                    {{ $item->keterangan ?: ($item->lokasi?->nama_lokasi ?? ($item->aktif ? 'Online' : 'Nonaktif')) }}
                                </p>
                            </div>
                            <div class="cctv-card-actions">
                                <a href="{{ route('admin.cctvs.show', $item) }}" class="btn-secondary" style="padding: 4px 10px; font-size: 11px; font-weight: 700; height: 32px; display: inline-flex; align-items: center; gap: 4px;" title="Pantau Live AI">
                                    <svg style="width: 13px; height: 13px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    Pantau
                                </a>
                                <button class="btn-edit" type="button" title="Edit CCTV" data-modal-target="edit-cctv-modal-{{ $item->id }}">✎</button>
                                <button class="btn-delete" type="button" title="Hapus CCTV" data-modal-target="delete-cctv-modal-{{ $item->id }}">×</button>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="cctv-empty-state">
                        <h2>Data CCTV belum tersedia</h2>
                        <p>Tambahkan data CCTV terlebih dahulu, lalu isi URL live streaming dan keterangan posisi CCTV.</p>
                        <button class="btn-primary" type="button" data-modal-target="create-cctv-modal">Tambah CCTV</button>
                    </div>
                @endforelse
            </div>

            <div class="mt-6">{{ $items->links() }}</div>

            <div id="create-cctv-modal" class="modal-backdrop" hidden>
                <div class="modal-card modal-card-sm">
                    <div class="modal-header">
                        <h2>Tambah CCTV</h2>
                        <button type="button" data-modal-close="create-cctv-modal">×</button>
                    </div>
                    <form class="modal-body space-y-4" method="POST" action="{{ route('admin.cctvs.store') }}">
                        @csrf
                        @include('admin.master.cctv.partials.fields', ['item' => $createItem])
                        <div class="modal-footer">
                            <button class="btn-secondary" type="button" data-modal-close="create-cctv-modal">Batal</button>
                            <button class="btn-primary" type="submit">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>

            @foreach ($items as $item)
                <div id="edit-cctv-modal-{{ $item->id }}" class="modal-backdrop" hidden>
                    <div class="modal-card modal-card-sm">
                        <div class="modal-header">
                            <h2>Edit CCTV</h2>
                            <button type="button" data-modal-close="edit-cctv-modal-{{ $item->id }}">×</button>
                        </div>
                        <form class="modal-body space-y-4" method="POST" action="{{ route('admin.cctvs.update', $item) }}">
                            @csrf
                            @method('PUT')
                            @include('admin.master.cctv.partials.fields', ['item' => $item])
                            <div class="modal-footer">
                                <button class="btn-secondary" type="button" data-modal-close="edit-cctv-modal-{{ $item->id }}">Batal</button>
                                <button class="btn-primary" type="submit">Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div id="delete-cctv-modal-{{ $item->id }}" class="modal-backdrop" hidden>
                    <div class="modal-card modal-card-sm">
                        <div class="modal-header">
                            <h2>Hapus CCTV</h2>
                            <button type="button" data-modal-close="delete-cctv-modal-{{ $item->id }}">×</button>
                        </div>
                        <div class="modal-body">
                            <p class="text-sm text-gray-600">Yakin ingin menghapus <strong>{{ $item->nama }}</strong>?</p>
                            <form class="modal-footer" method="POST" action="{{ route('admin.cctvs.destroy', $item) }}">
                                @csrf
                                @method('DELETE')
                                <button class="btn-secondary" type="button" data-modal-close="delete-cctv-modal-{{ $item->id }}">Batal</button>
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

        const cctvSearchForm = document.getElementById('cctv-search-form');
        const cctvSearchInput = document.getElementById('cctv-search-input');
        let cctvSearchTimer;
        let cctvSearchController;

        async function loadCctvUrl(url, pushState = true) {
            cctvSearchController?.abort();
            cctvSearchController = new AbortController();

            const response = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                signal: cctvSearchController.signal,
            });

            const html = await response.text();
            const nextDocument = new DOMParser().parseFromString(html, 'text/html');
            const nextResults = nextDocument.getElementById('cctv-results');
            const currentResults = document.getElementById('cctv-results');

            if (nextResults && currentResults) {
                currentResults.innerHTML = nextResults.innerHTML;
            }

            if (pushState) {
                window.history.replaceState({}, '', url);
            }
        }

        cctvSearchForm?.addEventListener('submit', function (event) {
            event.preventDefault();

            const url = new URL(cctvSearchForm.action, window.location.origin);
            const searchValue = cctvSearchInput.value.trim();

            if (searchValue) {
                url.searchParams.set('search', searchValue);
            }

            loadCctvUrl(url.toString()).catch((error) => {
                if (error.name !== 'AbortError') console.error(error);
            });
        });

        cctvSearchInput?.addEventListener('input', function () {
            clearTimeout(cctvSearchTimer);

            cctvSearchTimer = setTimeout(() => {
                const url = new URL(cctvSearchForm.action, window.location.origin);
                const searchValue = cctvSearchInput.value.trim();

                if (searchValue) {
                    url.searchParams.set('search', searchValue);
                }

                loadCctvUrl(url.toString()).catch((error) => {
                    if (error.name !== 'AbortError') console.error(error);
                });
            }, 300);
        });

        document.addEventListener('click', function (event) {
            const paginationLink = event.target.closest('#cctv-results nav a');
            if (paginationLink) {
                event.preventDefault();
                loadCctvUrl(paginationLink.href).catch((error) => {
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
