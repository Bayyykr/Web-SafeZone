<x-admin-layout>
    @push('styles')
        @vite(['resources/css/admin/pages/master.css', 'resources/css/admin/pages/infographic.css'])
    @endpush
    <x-slot name="header">Infografik Keamanan & Ketertiban</x-slot>

    <div class="user-page">
        <div class="user-hero">
            <div class="user-hero-left">
                <div class="user-hero-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 1 0 7.5 7.5h-7.5V6Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0 0 13.5 3v7.5Z" />
                    </svg>
                </div>
                <div>
                    <div class="user-hero-title">Infografik Laporan</div>
                    <div class="user-hero-subtitle">Visualisasi dan statistik data kejahatan serta kecelakaan wilayah</div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div class="user-table-card p-5">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Filter Berdasarkan Bulan</h3>
                <form class="flex items-center gap-2" method="GET" action="{{ route('admin.laporan.infografik') }}">

                    <input id="bulan" name="bulan" type="month" value="{{ $selectedMonth }}" class="form-input" style="height: 40px; border-radius: 10px; border: 1px solid var(--border); max-width: 240px;">
                    <button type="submit" class="btn-primary" style="height: 40px; padding: 0 16px;">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width: 16px; height: 16px;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                    </button>
                </form>
            </div>

            <div class="user-table-card p-5">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Filter Jangkauan Tanggal</h3>
                <form class="flex items-center gap-2" method="GET" action="{{ route('admin.laporan.infografik') }}">
                    <input type="hidden" name="bulan" value="{{ $selectedMonth }}">
                    <input name="tanggal_mulai" type="date" value="{{ $startDate }}" class="form-input" style="height: 40px; border-radius: 10px; border: 1px solid var(--border); max-width: 180px;">
                    <span class="text-slate-400 font-bold">s/d</span>
                    <input name="tanggal_selesai" type="date" value="{{ $endDate }}" class="form-input" style="height: 40px; border-radius: 10px; border: 1px solid var(--border); max-width: 180px;">
                    <button type="submit" class="btn-primary" style="height: 40px; padding: 0 16px;">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width: 16px; height: 16px;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>

        <div class="space-y-6">
            <div class="user-table-card p-6">
                <h3 class="text-sm font-bold text-slate-800 mb-4">Statistik Tren Bulanan</h3>
                <div class="infographic-chart" style="height: 320px;">
                    <canvas id="infografikStatistikChart"></canvas>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="user-table-card p-6">
                    <h3 class="text-sm font-bold text-slate-800 mb-4">Jumlah Laporan per Kecamatan</h3>
                    <div class="infographic-chart" style="height: 360px;">
                        <canvas id="infografikKecamatanChart"></canvas>
                    </div>
                </div>

                <div class="user-table-card p-6">
                    <h3 class="text-sm font-bold text-slate-800 mb-4">Akumulasi Kasus per Kategori</h3>
                    <div class="infographic-chart" style="height: 360px;">
                        <canvas id="infografikKategoriChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script id="infografik-chart-data" type="application/json">@json($chartData)</script>
</x-admin-layout>
