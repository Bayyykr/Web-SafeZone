<x-admin-layout>
    @push('styles')
        @vite('resources/css/admin/pages/master.css')
    @endpush
    <x-slot name="header">Kategori</x-slot>

    @php
        $toastType    = session('success') ? 'success' : (session('error') || $errors->any() ? 'error' : null);
        $toastMessage = session('success') ?: session('error') ?: ($errors->any() ? $errors->first() : null);
    @endphp

    @php
        $warnaMap = [
            '#FF0000' => 'Merah',
            '#E91E63' => 'Merah Muda',
            '#9C27B0' => 'Ungu',
            '#673AB7' => 'Ungu Tua',
            '#3F51B5' => 'Indigo',
            '#2196F3' => 'Biru',
            '#03A9F4' => 'Biru Muda',
            '#00BCD4' => 'Tosca',
            '#009688' => 'Hijau Tosca',
            '#4CAF50' => 'Hijau',
            '#8BC34A' => 'Hijau Muda',
            '#CDDC39' => 'Kuning Hijau',
            '#FFEB3B' => 'Kuning',
            '#FFC107' => 'Amber',
            '#FF9800' => 'Oranye',
            '#FF5722' => 'Oranye Tua',
            '#795548' => 'Coklat',
            '#607D8B' => 'Abu Biru',
            '#9E9E9E' => 'Abu-abu',
            '#000000' => 'Hitam'
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
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581a1.44 1.44 0 0 0 2.037 0l4.318-4.318a1.44 1.44 0 0 0 0-2.037l-9.58-9.581A2.25 2.25 0 0 0 9.568 3Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" />
                    </svg>
                </div>
                <div>
                    <div class="user-hero-title">Manajemen Kategori</div>
                    <div class="user-hero-subtitle">Kelola klasifikasi kejahatan dan kecelakaan beserta warna marker peta</div>
                </div>
            </div>
            <div class="user-stats-row">
                <div class="user-stat-pill">
                    <strong>{{ $stats['total'] }}</strong> Total Kategori
                </div>
                <div class="user-stat-pill pill-accent">
                    <strong>{{ $stats['kejahatan'] }}</strong> Kejahatan
                </div>
                <div class="user-stat-pill pill-success">
                    <strong>{{ $stats['kecelakaan'] }}</strong> Kecelakaan
                </div>
            </div>
        </div>

        <div class="user-toolbar">
            <div class="filter-tabs-wrap">
                <a class="tab-link {{ $jenis === 'kejahatan' ? 'active' : '' }}" href="{{ route('admin.categories.index', ['jenis' => 'kejahatan']) }}">
                    <span class="tab-dot dot-kejahatan"></span>
                    Kejahatan ({{ $stats['kejahatan'] }})
                </a>
                <a class="tab-link {{ $jenis === 'kecelakaan' ? 'active' : '' }}" href="{{ route('admin.categories.index', ['jenis' => 'kecelakaan']) }}">
                    <span class="tab-dot dot-kecelakaan"></span>
                    Kecelakaan ({{ $stats['kecelakaan'] }})
                </a>
            </div>

            <div class="toolbar-actions-right">
                <form id="kategori-search-form" method="GET" action="{{ route('admin.categories.index') }}">
                    <input type="hidden" name="jenis" value="{{ $jenis }}">
                    <div class="user-search-wrap">
                        <svg class="user-search-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                        <input
                            id="kategori-search-input"
                            class="user-search-input"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Cari kategori..."
                            autocomplete="off">
                    </div>
                </form>
                <button class="btn-primary" type="button" data-modal-target="create-kategori-modal">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Tambah Kategori
                </button>
            </div>
        </div>

        <div id="kategori-results">
            <div class="user-table-card">
                <table class="user-table">
                    <colgroup>
                        <col style="width:46px">
                        <col style="width:240px">
                        <col style="width:160px">
                        <col style="width:200px">
                        <col style="width:78px">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Kategori</th>
                            <th>Jenis</th>
                            <th>Warna Marker</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($items as $item)
                            <tr>
                                <td class="ut-no">{{ $items->firstItem() + $loop->index }}</td>
                                <td class="font-semibold">{{ $item->nama_kategori }}</td>
                                <td>
                                    <span class="user-role-badge role-{{ $item->jenis === 'kejahatan' ? 'operator' : 'user' }}">
                                        {{ ucfirst($item->jenis) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="kat-color-cell">
                                        <span class="kat-color-dot" style="background-color: {{ $item->warna_marker }}"></span>
                                        <span class="kat-color-name">{{ $warnaMap[strtoupper($item->warna_marker)] ?? $item->warna_marker }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="user-actions">
                                        <button class="btn-edit" type="button" data-modal-target="edit-kategori-modal-{{ $item->id }}" title="Edit">✎</button>
                                        <button class="btn-delete" type="button" data-modal-target="delete-kategori-modal-{{ $item->id }}" title="Hapus">×</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <div class="user-table-empty">
                                        <div class="user-table-empty-icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581a1.44 1.44 0 0 0 2.037 0l4.318-4.318a1.44 1.44 0 0 0 0-2.037l-9.58-9.581A2.25 2.25 0 0 0 9.568 3Z" />
                                            </svg>
                                        </div>
                                        <p>Data kategori belum tersedia.</p>
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

            <div id="create-kategori-modal" class="modal-backdrop" hidden>
                <div class="modal-card modal-card-sm">
                    <div class="modal-header">
                        <h2>Tambah Kategori</h2>
                        <button type="button" data-modal-close="create-kategori-modal">×</button>
                    </div>
                    <form class="modal-body" method="POST" action="{{ route('admin.categories.store') }}">
                        @csrf
                        @include('admin.master.kategori.partials.fields', ['item' => null, 'jenis' => $jenis])
                        <div class="modal-footer">
                            <button class="btn-secondary" type="button" data-modal-close="create-kategori-modal">Batal</button>
                            <button class="btn-primary" type="submit">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>

            @foreach ($items as $item)
                <div id="edit-kategori-modal-{{ $item->id }}" class="modal-backdrop" hidden>
                    <div class="modal-card modal-card-sm">
                        <div class="modal-header">
                            <h2>Edit Kategori</h2>
                            <button type="button" data-modal-close="edit-kategori-modal-{{ $item->id }}">×</button>
                        </div>
                        <form class="modal-body" method="POST" action="{{ route('admin.categories.update', $item) }}">
                            @csrf
                            @method('PUT')
                            @include('admin.master.kategori.partials.fields', ['item' => $item, 'jenis' => $jenis])
                            <div class="modal-footer">
                                <button class="btn-secondary" type="button" data-modal-close="edit-kategori-modal-{{ $item->id }}">Batal</button>
                                <button class="btn-primary" type="submit">Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div id="delete-kategori-modal-{{ $item->id }}" class="modal-backdrop" hidden>
                    <div class="modal-card modal-card-sm">
                        <div class="modal-header">
                            <h2>Hapus Kategori</h2>
                            <button type="button" data-modal-close="delete-kategori-modal-{{ $item->id }}">×</button>
                        </div>
                        <div class="modal-body">
                            <p style="font-size:14px;color:var(--muted)">Yakin ingin menghapus kategori <strong style="color:var(--text)">{{ $item->nama_kategori }}</strong>? Tindakan ini tidak dapat dibatalkan.</p>
                            <form class="modal-footer" method="POST" action="{{ route('admin.categories.destroy', $item) }}">
                                @csrf
                                @method('DELETE')
                                <button class="btn-secondary" type="button" data-modal-close="delete-kategori-modal-{{ $item->id }}">Batal</button>
                                <button class="btn-delete-text" type="submit">Hapus</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    @vite('resources/js/admin/kategori.js')
</x-admin-layout>
