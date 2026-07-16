<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }} Admin</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/css/admin.css', 'resources/js/app.js', 'resources/js/admin.js'])
        @stack('styles')
    </head>
    @php($globalActiveSosCount = \App\Models\EmergencyReport::where('status', 'aktif')->count())
    <body class="font-sans antialiased preload" data-active-sos-count="{{ $globalActiveSosCount }}" data-sos-status-url="{{ route('admin.laporan.darurat.status') }}">
        <script>
            (function() {
                try {
                    if (window.matchMedia('(min-width: 1025px)').matches && localStorage.getItem('admin-sidebar-collapsed') === 'true') {
                        document.body.classList.add('sidebar-collapsed');
                    }
                } catch(e) {}
            })();
        </script>
        <div class="admin-shell">
            @include('layouts.admin-navigation')
            <div class="sidebar-overlay" data-sidebar-overlay></div>

            <div class="content-area">
                <header class="admin-header">
                    <div class="admin-header-left">
                        <div class="page-title">{{ $header === 'Dashboard' ? 'Monitoring Keamanan Wilayah' : ($header ?? 'Monitoring Keamanan Wilayah') }}</div>
                    </div>
                    
                    <div class="flex items-center gap-6">
                        <div class="hidden md:flex flex-col text-right">
                            <span id="realtime-date" class="text-[10px] font-bold text-gray-400 uppercase tracking-wider"></span>
                            <span id="realtime-clock" class="text-xs font-extrabold text-gray-700"></span>
                        </div>
                        
                        <button class="relative p-2 text-gray-400 hover:text-gray-600 transition" aria-label="Notifikasi">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                        </button>

                        @php($authUser = Auth::user())
                        <x-dropdown align="right" width="48">
                            <x-slot name="trigger">
                                <button class="avatar-dot {{ $authUser?->profile_photo ? 'has-photo' : '' }}" type="button" aria-label="Menu pengguna">
                                    @if ($authUser?->profile_photo)
                                        <img src="{{ asset('storage/' . $authUser->profile_photo) }}" alt="Foto profil {{ $authUser->name }}">
                                    @else
                                        {{ strtoupper(substr($authUser?->name ?? 'U', 0, 1)) }}
                                    @endif
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <x-dropdown-link :href="route('profile.edit')">
                                    {{ __('Profile') }}
                                </x-dropdown-link>

                                <button type="button" class="block w-full px-4 py-2 text-start text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out" data-modal-target="logout-confirmation-modal">
                                    {{ __('Log Out') }}
                                </button>
                            </x-slot>
                        </x-dropdown>
                    </div>
                </header>

                <main>
                    {{ $slot }}
                </main>
            </div>
        </div>

        <div id="logout-confirmation-modal" class="modal-backdrop" hidden>
            <div class="modal-card modal-card-sm">
                <div class="modal-header">
                    <h2>Konfirmasi Logout</h2>
                    <button type="button" data-modal-close="logout-confirmation-modal">×</button>
                </div>
                <div class="modal-body">
                    <p class="text-sm text-gray-700">Apakah Anda yakin ingin keluar dari akun ini?</p>
                    <form class="modal-footer px-0 pb-0" method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="btn-secondary" type="button" data-modal-close="logout-confirmation-modal">Batal</button>
                        <button class="btn-delete-text" type="submit">Logout</button>
                    </form>
                </div>
            </div>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(function() {
                    document.body.classList.remove('preload');
                }, 50);
            });
        </script>
    </body>
</html>
