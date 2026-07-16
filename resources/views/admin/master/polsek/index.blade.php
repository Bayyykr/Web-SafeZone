<x-admin-layout>
    @push('styles')
        @vite('resources/css/admin/pages/master.css')
    @endpush
    <x-slot name="header">Polsek</x-slot>

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
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                    </svg>
                </div>
                <div>
                    <div class="user-hero-title">Manajemen Polsek</div>
                    <div class="user-hero-subtitle">Kelola data kepolisian sektor beserta wilayah dan kontak</div>
                </div>
            </div>
            <div class="user-stats-row">
                <div class="user-stat-pill">
                    <strong>{{ $stats['total'] }}</strong> Total Polsek
                </div>
                <div class="user-stat-pill pill-accent">
                    <strong>{{ $stats['berLokasi'] }}</strong> Terhubung Lokasi
                </div>
            </div>
        </div>

        <div class="user-toolbar">
            <form id="polsek-search-form" method="GET" action="{{ route('admin.polseks.index') }}">
                <div class="user-search-wrap">
                    <svg class="user-search-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                    <input id="polsek-search-input" class="user-search-input" name="search"
                        value="{{ request('search') }}" placeholder="Cari nama, alamat, telepon..." autocomplete="off">
                </div>
            </form>
            <button class="btn-primary" type="button" data-modal-target="create-polsek-modal">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24"
                    stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Tambah Polsek
            </button>
        </div>

        <div id="polsek-results">
            <div class="user-table-card">
                <table class="user-table">
                    <colgroup>
                        <col style="width:46px">
                        <col style="width:180px">
                        <col style="width:160px">
                        <col style="width:270px">
                        <col style="width:130px">
                        <col style="width:78px">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Polsek</th>
                            <th>Wilayah Lokasi</th>
                            <th>Alamat</th>
                            <th>Telepon</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($items as $item)
                            <tr>
                                <td class="ut-no">{{ $items->firstItem() + $loop->index }}</td>
                                <td>
                                    <div class="polsek-name-cell">
                                        <div class="polsek-icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none"
                                                viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                                            </svg>
                                        </div>
                                        <span class="polsek-name">{{ $item->nama }}</span>
                                    </div>
                                </td>
                                <td>
                                    @if ($item->lokasi)
                                        <span class="polsek-lokasi-badge">{{ $item->lokasi->nama_lokasi }}</span>
                                    @else
                                        <span class="ut-no">—</span>
                                    @endif
                                </td>
                                <td class="ut-email" title="{{ $item->alamat ?? '' }}">{{ $item->alamat ?? '—' }}</td>
                                <td class="ut-phone">{{ $item->telepon ?? '—' }}</td>
                                <td>
                                    <div class="user-actions">
                                        <button class="btn-edit" type="button"
                                            data-modal-target="edit-polsek-modal-{{ $item->id }}" title="Edit">✎</button>
                                        <button class="btn-delete" type="button"
                                            data-modal-target="delete-polsek-modal-{{ $item->id }}" title="Hapus">×</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="user-table-empty">
                                        <div class="user-table-empty-icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none"
                                                viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                                            </svg>
                                        </div>
                                        <p>Data polsek belum tersedia.</p>
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

            <div id="create-polsek-modal" class="modal-backdrop" hidden>
                <div class="modal-card modal-card-sm">
                    <div class="modal-header">
                        <h2>Tambah Polsek</h2>
                        <button type="button" data-modal-close="create-polsek-modal">×</button>
                    </div>
                    <form class="modal-body" method="POST" action="{{ route('admin.polseks.store') }}">
                        @csrf
                        @include('admin.master.polsek.partials.fields', ['item' => null])
                        <div class="modal-footer">
                            <button class="btn-secondary" type="button"
                                data-modal-close="create-polsek-modal">Batal</button>
                            <button class="btn-primary" type="submit">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>

            @foreach ($items as $item)
                <div id="edit-polsek-modal-{{ $item->id }}" class="modal-backdrop" hidden>
                    <div class="modal-card modal-card-sm">
                        <div class="modal-header">
                            <h2>Edit Polsek</h2>
                            <button type="button" data-modal-close="edit-polsek-modal-{{ $item->id }}">×</button>
                        </div>
                        <form class="modal-body" method="POST" action="{{ route('admin.polseks.update', $item) }}">
                            @csrf
                            @method('PUT')
                            @include('admin.master.polsek.partials.fields', ['item' => $item])
                            <div class="modal-footer">
                                <button class="btn-secondary" type="button"
                                    data-modal-close="edit-polsek-modal-{{ $item->id }}">Batal</button>
                                <button class="btn-primary" type="submit">Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div id="delete-polsek-modal-{{ $item->id }}" class="modal-backdrop" hidden>
                    <div class="modal-card modal-card-sm">
                        <div class="modal-header">
                            <h2>Hapus Polsek</h2>
                            <button type="button" data-modal-close="delete-polsek-modal-{{ $item->id }}">×</button>
                        </div>
                        <div class="modal-body">
                            <p style="font-size:14px;color:var(--muted)">Yakin ingin menghapus <strong
                                    style="color:var(--text)">{{ $item->nama }}</strong>? Tindakan ini tidak dapat
                                dibatalkan.</p>
                            <form class="modal-footer" method="POST" action="{{ route('admin.polseks.destroy', $item) }}">
                                @csrf
                                @method('DELETE')
                                <button class="btn-secondary" type="button"
                                    data-modal-close="delete-polsek-modal-{{ $item->id }}">Batal</button>
                                <button class="btn-delete-text" type="submit">Hapus</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    @vite('resources/js/admin/polsek.js')
</x-admin-layout>