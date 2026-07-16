@php
    $masterOpen = request()->routeIs('admin.users.*', 'admin.polseks.*', 'admin.categories.*', 'admin.cctvs.*', 'admin.locations.*');
    $layananOpen = request()->routeIs('admin.konfirmasi-laporan.*', 'admin.berita.*');
    $laporanOpen = request()->routeIs('admin.laporan.*');
@endphp

<nav class="sidebar" aria-label="Navigasi admin">
    <button class="sidebar-toggle" data-sidebar-toggle aria-label="Buka/tutup sidebar">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" /></svg>
    </button>

    <div class="sidebar-brand">
        <div class="flex items-center gap-2 overflow-hidden">
            <img src="/icons/icon_app.png" alt="SafeZone Logo" class="w-12 h-12 object-contain">
            <div class="sidebar-brand-text-wrapper">
                <span class="sidebar-brand-text">SafeZone Admin</span>
                <div class="text-[9px] font-bold text-blue-200 tracking-widest uppercase pl-1 -mt-0.5">City Monitoring</div>
            </div>
        </div>
    </div>

    <div class="sidebar-menu-wrapper">
        <div class="category-title">MENU UTAMA</div>
        <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l9-9 9 9M5 10v10h14V10" /></svg>
            <span>Dashboard</span>
        </a>

        <div class="sidebar-dropdown {{ $masterOpen ? 'open' : '' }}" data-sidebar-dropdown="master">
            <div class="sidebar-dropdown-toggle cursor-pointer select-none flex items-center justify-between">
                <span>Master Data</span>
                <span class="chevron text-gray-400">▾</span>
            </div>
            <div class="sidebar-dropdown-content">
                <div class="sidebar-dropdown-inner">
                    <a href="{{ route('admin.users.index') }}" class="sidebar-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm-7 9a7 7 0 0 1 14 0" /></svg>
                        <span>Users</span>
                    </a>
                    <a href="{{ route('admin.polseks.index') }}" class="sidebar-link {{ request()->routeIs('admin.polseks.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 21h16M6 21V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v16M9 7h1m4 0h1M9 11h1m4 0h1M9 15h1m4 0h1" /></svg>
                        <span>Polsek</span>
                    </a>
                    <a href="{{ route('admin.categories.index', ['jenis' => 'kejahatan']) }}" class="sidebar-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                        <span>Kategori</span>
                    </a>
                    <a href="{{ route('admin.cctvs.index') }}" class="sidebar-link {{ request()->routeIs('admin.cctvs.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l5-3v10l-5-3v-4ZM4 7h11v10H4z" /></svg>
                        <span>CCTV</span>
                    </a>
                    <a href="{{ route('admin.locations.index') }}" class="sidebar-link {{ request()->routeIs('admin.locations.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21s7-4.438 7-11a7 7 0 1 0-14 0c0 6.562 7 11 7 11Z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10.5a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z" /></svg>
                        <span>Lokasi</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="sidebar-dropdown {{ $layananOpen ? 'open' : '' }}" data-sidebar-dropdown="layanan">
            <div class="sidebar-dropdown-toggle cursor-pointer select-none flex items-center justify-between">
                <span>Services</span>
                <span class="chevron text-gray-400">▾</span>
            </div>
            <div class="sidebar-dropdown-content">
                <div class="sidebar-dropdown-inner">
                    <a href="{{ route('admin.konfirmasi-laporan.index') }}" class="sidebar-link {{ request()->routeIs('admin.konfirmasi-laporan.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20Z" /></svg>
                        <span>Konfirmasi Laporan</span>
                    </a>
                    <a href="{{ route('admin.berita.index') }}" class="sidebar-link {{ request()->routeIs('admin.berita.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h10l6 6v8a2 2 0 0 1-2 2Z" /></svg>
                        <span>Berita</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="sidebar-dropdown {{ $laporanOpen ? 'open' : '' }}" data-sidebar-dropdown="laporan">
            <div class="sidebar-dropdown-toggle cursor-pointer select-none flex items-center justify-between">
                <span>Reports</span>
                <span class="chevron text-gray-400">▾</span>
            </div>
            <div class="sidebar-dropdown-content">
                <div class="sidebar-dropdown-inner">
                    <a href="{{ route('admin.laporan.infografik') }}" class="sidebar-link {{ request()->routeIs('admin.laporan.infografik') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 19V5m0 14h16M8 16V9m4 7V6m4 10v-4" /></svg>
                        <span>Infografik</span>
                    </a>
                    <a href="{{ route('admin.laporan.riwayat') }}" class="sidebar-link {{ request()->routeIs('admin.laporan.riwayat') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v5l3 2M21 12A9 9 0 1 1 3 12a9 9 0 0 1 18 0Z" /></svg>
                        <span>Riwayat</span>
                    </a>
                    <a href="{{ route('admin.laporan.darurat') }}" class="sidebar-link {{ request()->routeIs('admin.laporan.darurat') ? 'active' : '' }}">
                        <svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.72-1.36 3.486 0l6.518 11.59c.75 1.334-.213 2.986-1.743 2.986H3.482c-1.53 0-2.493-1.652-1.743-2.986l6.518-11.59ZM11 14a1 1 0 1 1-2 0 1 1 0 0 1 2 0Zm-1-2a1 1 0 0 0 1-1V8a1 1 0 1 0-2 0v3a1 1 0 0 0 1 1Z" clip-rule="evenodd" /></svg>
                        <span>Darurat</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    @php($authUser = Auth::user())
    <a href="{{ route('profile.edit') }}" class="sidebar-profile">
        <div class="avatar-dot {{ $authUser?->profile_photo ? 'has-photo' : '' }}">
            @if ($authUser?->profile_photo)
                <img src="{{ asset('storage/' . $authUser->profile_photo) }}" alt="Foto profil {{ $authUser->name }}">
            @else
                {{ strtoupper(substr($authUser?->name ?? 'U', 0, 1)) }}
            @endif
        </div>
        <div class="profile-info">
            <span class="profile-name">{{ $authUser?->name }}</span>
            <span class="profile-role">{{ strtoupper(str_replace('_', ' ', $authUser?->role)) }}</span>
        </div>
    </a>
</nav>
