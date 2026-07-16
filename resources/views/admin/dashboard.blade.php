<x-admin-layout>
    @push('styles')
        @vite('resources/css/admin/pages/dashboard.css')
    @endpush
    <x-slot name="header">Dashboard</x-slot>

    <script id="dashboard-chart-data" type="application/json">@json($chartData)</script>
    <script id="dashboard-map-data" type="application/json">@json($mapData)</script>

    <div class="dashboard-container">
        <div>
            <h3 class="summary-section-title">Ringkasan Laporan Operasional</h3>
            <div class="summary-grid">
                <div class="summary-card">
                    <div class="summary-card-info">
                        <span class="summary-card-label">Laporan Ditangani</span>
                        <div class="summary-card-value">{{ $laporanStats['ditangani'] }}</div>
                    </div>
                    <div class="summary-icon blue">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                    </div>
                </div>

                <div class="summary-card">
                    <div class="summary-card-info">
                        <span class="summary-card-label">Laporan Menunggu</span>
                        <div class="summary-card-value">{{ $laporanStats['menunggu'] }}</div>
                    </div>
                    <div class="summary-icon amber">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>

                <div class="summary-card">
                    <div class="summary-card-info">
                        <span class="summary-card-label">Laporan Selesai</span>
                        <div class="summary-card-value">{{ $laporanStats['selesai'] }}</div>
                    </div>
                    <div class="summary-icon emerald">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        <div class="dashboard-map-card">
            <div id="map" class="w-full h-full"></div>
            <div class="map-view-toggle">
                <label class="switch-toggle">
                    <input type="checkbox" id="map-view-switch">
                    <span class="switch-slider">
                        <span class="switch-label left">Heatmap</span>
                        <span class="switch-label right">Markers</span>
                    </span>
                </label>
            </div>
        </div>

        <div class="dashboard-charts-grid">
            <div class="dashboard-card">
                <div>
                    <div class="card-header-wrapper">
                        <h3 class="card-heading">Tren Laporan Tahun Ini</h3>
                        <button class="text-gray-400 hover:text-gray-600" type="button" aria-label="Menu">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                            </svg>
                        </button>
                    </div>
                    <p class="card-subtitle">Statistik kejahatan & kecelakaan bulanan</p>
                </div>
                <div class="chart-container">
                    <canvas id="statistikChart"></canvas>
                </div>
            </div>

            <div class="dashboard-card">
                <div>
                    <h3 class="card-heading mb-4">Lokasi Laporan Terbanyak</h3>
                    <div class="flex flex-col gap-3">
                        @foreach($chartData['locations']['labels'] as $index => $label)
                            @if($index < 4)
                                <div class="location-item">
                                    <div class="location-item-info">
                                        <span>{{ $label }}</span>
                                        <span>{{ number_format($chartData['locations']['totals'][$index]) }}</span>
                                    </div>
                                    <div class="location-progress-bg">
                                        @php
                                            $max = max($chartData['locations']['totals']) ?: 1;
                                            $percent = ($chartData['locations']['totals'][$index] / $max) * 100;
                                        @endphp
                                        <div class="location-progress-fill" style="width: {{ $percent }}%;"></div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
                <a href="{{ route('admin.locations.index') }}" class="btn-view-all">
                    LIHAT SEMUA WILAYAH
                </a>
            </div>

            <div class="dashboard-card">
                <div>
                    <h3 class="card-heading mb-4">Kategori Dominan</h3>
                    <div class="chart-doughnut-container">
                        <canvas id="kejahatanKecelakaanChart"></canvas>
                        <div class="chart-doughnut-center">
                            <span class="chart-doughnut-label">Kategori</span>
                            <span class="chart-doughnut-number">{{ count($chartData['categories']['labels']) }}</span>
                        </div>
                    </div>
                </div>
                <div class="categories-legend-grid">
                    @foreach($chartData['categories']['labels'] as $index => $label)
                        @if($index < 4)
                            <div class="legend-item">
                                <span class="legend-color"
                                    style="background-color: {{ $chartData['categories']['colors'][$index] ?? '#2952e3' }};"></span>
                                <span class="legend-text" title="{{ $label }}">{{ $label }}</span>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>

    </div>
</x-admin-layout>