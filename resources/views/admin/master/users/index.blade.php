<x-admin-layout>
    @push('styles')
        @vite(['resources/css/admin/pages/master.css', 'resources/css/admin/pages/user.css'])
    @endpush
    <x-slot name="header">Users</x-slot>

    @php
        $toastType    = session('success') ? 'success' : (session('error') || $errors->any() ? 'error' : null);
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
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                    </svg>
                </div>
                <div>
                    <div class="user-hero-title">Manajemen Users</div>
                    <div class="user-hero-subtitle">Kelola akun, role, dan status pengguna sistem</div>
                </div>
            </div>
            <div class="user-stats-row">
                <div class="user-stat-pill">
                    <strong>{{ $stats['total'] }}</strong> Total
                </div>
                <div class="user-stat-pill pill-success">
                    <strong>{{ $stats['active'] }}</strong> Aktif
                </div>
                <div class="user-stat-pill pill-accent">
                    <strong>{{ $stats['admins'] }}</strong> Admin
                </div>
            </div>
        </div>

        <div class="user-toolbar">
            <form id="users-search-form" method="GET" action="{{ route('admin.users.index') }}">
                <div class="user-search-wrap">
                    <svg class="user-search-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                    <input
                        id="users-search-input"
                        class="user-search-input"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Cari nama, email, role..."
                        autocomplete="off">
                </div>
            </form>
            <button class="btn-primary" type="button" data-modal-target="create-user-modal">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Tambah User
            </button>
        </div>

        <div id="users-results">
            <div class="user-table-card">
                <table class="user-table">
                    <colgroup>
                        <col class="ucol-no">
                        <col class="ucol-user">
                        <col class="ucol-email">
                        <col class="ucol-phone">
                        <col class="ucol-role">
                        <col class="ucol-status">
                        <col class="ucol-action">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Pengguna</th>
                            <th>Email</th>
                            <th>Telepon</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($items as $item)
                            <tr>
                                <td class="ut-no">{{ $items->firstItem() + $loop->index }}</td>
                                <td>
                                    <div class="user-name-cell">
                                        <div class="user-avatar">{{ mb_substr($item->name, 0, 2) }}</div>
                                        <div class="user-name-info">
                                            <strong>{{ $item->name }}</strong>
                                            @if ($item->alamat)
                                                <small>{{ $item->alamat }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="ut-email" title="{{ $item->email }}">{{ $item->email }}</td>
                                <td class="ut-phone">{{ $item->telepon ?: '—' }}</td>
                                <td>
                                    <span class="user-role-badge role-{{ $item->role ?? 'user' }}">
                                        {{ ucfirst($item->role ?? 'user') }}
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge {{ $item->aktif ? 'active' : 'inactive' }}">
                                        {{ $item->aktif ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="user-actions">
                                        <button class="btn-edit" type="button" data-modal-target="edit-user-modal-{{ $item->id }}" title="Edit">✎</button>
                                        <button class="btn-delete" type="button" data-modal-target="delete-user-modal-{{ $item->id }}" title="Hapus">×</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="user-table-empty">
                                        <div class="user-table-empty-icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                            </svg>
                                        </div>
                                        <p>Data user belum tersedia.</p>
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

            <div id="create-user-modal" class="modal-backdrop" hidden>
                <div class="modal-card">
                    <div class="modal-header">
                        <h2>Tambah User</h2>
                        <button type="button" data-modal-close="create-user-modal">×</button>
                    </div>
                    <form class="modal-body" method="POST" action="{{ route('admin.users.store') }}" data-user-form novalidate>
                        @csrf
                        @include('admin.master.users.partials.fields', ['item' => null, 'passwordRequired' => true])
                        <div class="modal-footer">
                            <button class="btn-secondary" type="button" data-modal-close="create-user-modal">Batal</button>
                            <button class="btn-primary" type="submit">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>

            @foreach ($items as $item)
                <div id="edit-user-modal-{{ $item->id }}" class="modal-backdrop" hidden>
                    <div class="modal-card">
                        <div class="modal-header">
                            <h2>Edit User</h2>
                            <button type="button" data-modal-close="edit-user-modal-{{ $item->id }}">×</button>
                        </div>
                        <form class="modal-body" method="POST" action="{{ route('admin.users.update', $item) }}" data-user-form novalidate>
                            @csrf
                            @method('PUT')
                            @include('admin.master.users.partials.fields', ['item' => $item, 'passwordRequired' => false])
                            <div class="modal-footer">
                                <button class="btn-secondary" type="button" data-modal-close="edit-user-modal-{{ $item->id }}">Batal</button>
                                <button class="btn-primary" type="submit">Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div id="delete-user-modal-{{ $item->id }}" class="modal-backdrop" hidden>
                    <div class="modal-card modal-card-sm">
                        <div class="modal-header">
                            <h2>Hapus User</h2>
                            <button type="button" data-modal-close="delete-user-modal-{{ $item->id }}">×</button>
                        </div>
                        <div class="modal-body">
                            <p style="font-size:14px;color:var(--muted)">Yakin ingin menghapus user <strong style="color:var(--text)">{{ $item->name }}</strong>? Tindakan ini tidak dapat dibatalkan.</p>
                            <form class="modal-footer" method="POST" action="{{ route('admin.users.destroy', $item) }}">
                                @csrf
                                @method('DELETE')
                                <button class="btn-secondary" type="button" data-modal-close="delete-user-modal-{{ $item->id }}">Batal</button>
                                <button class="btn-delete-text" type="submit">Hapus</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    @vite('resources/js/admin/users.js')
</x-admin-layout>
