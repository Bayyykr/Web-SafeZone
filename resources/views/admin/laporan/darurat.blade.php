<x-admin-layout>
    @push('styles')
        @vite(['resources/css/admin/pages/master.css', 'resources/css/admin/pages/report.css'])
    @endpush
    <x-slot name="header">Laporan Darurat SOS</x-slot>

    @php
        $statusClass = [
            'aktif' => 'bg-red-500 text-white',
            'dalam_penanganan' => 'bg-yellow-500 text-slate-900',
        ];
    @endphp

    @if (session('success'))
        <div class="toast-notification success" data-toast>
            <div class="toast-icon">✓</div>
            <div>
                <p class="toast-title">Berhasil</p>
                <p class="toast-message">{{ session('success') }}</p>
            </div>
            <button type="button" data-toast-close aria-label="Tutup notifikasi">×</button>
        </div>
    @endif

    <div class="user-page">
        <div class="user-hero">
            <div class="user-hero-left">
                <div class="user-hero-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                    </svg>
                </div>
                <div>
                    <div class="user-hero-title">Monitor Laporan Darurat Real-Time</div>
                    <div class="user-hero-subtitle">Prioritas penanganan darurat kecelakaan & kejahatan terintegrasi dengan Polsek terdekat</div>
                </div>
            </div>
            <div class="user-stats-row">
                <div class="user-stat-pill pill-accent">
                    <strong>{{ number_format($summary['active']) }}</strong> SOS Aktif
                </div>
                <div class="user-stat-pill">
                    <strong>{{ number_format($summary['handling']) }}</strong> Penanganan
                </div>
                <div class="user-stat-pill">
                    <strong>{{ number_format($summary['today']) }}</strong> Hari Ini
                </div>
                <div class="user-stat-pill pill-success">
                    <strong>{{ number_format($summary['done']) }}</strong> Selesai / Arsip
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <div class="lg:col-span-2 user-table-card p-4">
                <script id="sos-map-data" type="application/json">@json($items->items())</script>
                <div id="sos-map" class="h-[400px] w-full rounded-xl border border-slate-100" style="z-index: 1;"></div>
                @vite(['resources/js/sos-map.js'])
            </div>
            <div class="user-table-card p-5 flex flex-col justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-800 mb-2">Komponen Visual & Alarm</h2>
                    <p class="text-xs text-slate-500 leading-relaxed">Pin merah berkedip pada peta menunjukkan lokasi sinyal darurat aktif. Klik pin untuk membuka koordinat GPS presisi korban secara real-time.</p>
                </div>
                <div class="mt-4 pt-4 border-t border-slate-100">
                    <button class="btn-secondary w-full" type="button" data-test-alarm style="height: 40px; border-radius: 10px;">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 16px; height: 16px; display: inline-block; margin-right: 6px; vertical-align: middle;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.114 5.636a9 9 0 0 1 0 12.728M16.463 8.288a5.25 5.25 0 0 1 0 7.424M6.75 8.25l4.72-4.72a.75.75 0 0 1 1.28.53v15.88a.75.75 0 0 1-1.28.53l-4.72-4.72H4.51c-.88 0-1.704-.507-1.938-1.354A9.009 9.009 0 0 1 2.25 12c0-.83.112-1.633.322-2.396C2.806 8.756 3.63 8.25 4.51 8.25H6.75Z" />
                        </svg>
                        Test Audio Alarm
                    </button>
                </div>
            </div>
        </div>

        <div class="user-table-card mb-6">
            <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-base font-bold text-slate-800">Data Interaksi Real-Time</h2>
                    <p class="text-xs text-slate-500 mt-1">Daftar sinyal SOS aktif dan laporan yang sedang ditangani petugas lapangan.</p>
                </div>
                <form class="flex items-center gap-2" method="GET" action="{{ route('admin.laporan.darurat') }}">
                    <input class="form-input" name="search" value="{{ request('search') }}" placeholder="Cari kode, pelapor..." style="height: 40px; border-radius: 10px; border: 1px solid var(--border); max-width: 180px;">
                    <select class="form-select" name="status" style="height: 40px; border-radius: 10px; border: 1px solid var(--border); width: 150px;">
                        <option value="">Semua Status</option>
                        <option value="aktif" @selected(request('status') === 'aktif')>Aktif</option>
                        <option value="dalam_penanganan" @selected(request('status') === 'dalam_penanganan')>Penanganan</option>
                    </select>
                    <button class="btn-primary" type="submit" style="height: 40px; padding: 0 16px;">Filter</button>
                </form>
            </div>

            <table class="user-table">
                <thead>
                    <tr>
                        <th style="width: 130px;">ID Darurat</th>
                        <th style="width: 110px;">Status</th>
                        <th>Pelapor</th>
                        <th>Koordinat & Alamat</th>
                        <th>Polsek Terdekat</th>
                        <th>Response Time</th>
                        <th style="width: 260px; min-width: 260px; text-align: center;">Quick Dispatch</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        <tr class="{{ $item->status === 'aktif' ? 'bg-[#eff6ff]' : '' }}">
                            <td class="font-mono font-bold text-slate-800">
                                <div>{{ $item->kode_darurat }}</div>
                                <div class="text-xs text-slate-400 font-normal mt-0.5">{{ $item->waktu_sos?->format('d/m/Y H:i:s') ?? '-' }}</div>
                            </td>
                            <td>
                                @if($item->status === 'aktif')
                                    <span class="status-badge" style="background: var(--accent); color: #ffffff;">Aktif</span>
                                @else
                                    <span class="status-badge status-rawan">Penanganan</span>
                                @endif
                            </td>
                            <td>
                                <div class="font-bold text-slate-800">{{ $item->user?->name ?? 'Pelapor Anonim' }}</div>
                                <div class="text-xs text-slate-400 mt-0.5">{{ $item->user?->telepon ?? $item->user?->email ?? '-' }}</div>
                            </td>
                            <td>
                                <div class="font-mono text-xs text-slate-700">{{ $item->latitude }}, {{ $item->longitude }}</div>
                                <div class="text-xs text-slate-400 mt-0.5 max-w-[200px] truncate" title="{{ $item->alamat_terdeteksi }}">{{ $item->alamat_terdeteksi ?: 'Alamat belum terdeteksi' }}</div>
                            </td>
                            <td>
                                <div class="font-semibold text-slate-700">{{ $item->nearestPolsek?->nama ?? '-' }}</div>
                                <div class="text-xs text-slate-400 mt-0.5">{{ $item->jarak_polsek_km !== null ? $item->jarak_polsek_km . ' km dari lokasi' : 'Jarak belum dihitung' }}</div>
                            </td>
                            <td>
                                @if ($item->response_time_minutes !== null)
                                    <div class="font-bold text-slate-800">{{ $item->response_time_minutes }} menit</div>
                                    <div class="text-xs text-slate-400 mt-0.5">Kirim: {{ $item->waktu_dispatch?->format('H:i:s') }}</div>
                                @else
                                    <span class="text-xs font-semibold text-blue-600 animate-pulse">Menunggu dispatch</span>
                                @endif
                            </td>
                            <td style="width: 260px; min-width: 260px;">
                                <div class="flex items-center justify-center gap-1.5 flex-nowrap whitespace-nowrap">
                                    <a class="btn-secondary" target="_blank" rel="noopener" href="https://www.google.com/maps?q={{ $item->latitude }},{{ $item->longitude }}" style="height: 32px; line-height: 32px; padding: 0 10px; font-size: 11px;">Maps</a>
                                    @if ($item->status === 'aktif')
                                        <form method="POST" action="{{ route('admin.laporan.darurat.dispatch', $item) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn-primary" type="submit" style="height: 32px; padding: 0 10px; font-size: 11px; background: var(--accent); border-color: transparent;">Kirim Personel</button>
                                        </form>
                                    @endif
                                    <button class="btn-secondary" type="button" data-modal-target="selesai-sos-{{ $item->id }}" style="height: 32px; padding: 0 10px; font-size: 11px;">Selesai</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-slate-400 font-semibold">Tidak ada laporan darurat aktif.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="user-table-footer">
                {{ $items->links() }}
            </div>
        </div>

        <details class="user-table-card p-4">
            <summary class="cursor-pointer text-xs font-bold text-slate-500 uppercase tracking-wider">Simulasi Sinyal SOS / GPS Mobile Device</summary>
            <form class="mt-4 grid gap-3 md:grid-cols-4" method="POST" action="{{ route('admin.laporan.darurat.store') }}">
                @csrf
                <input class="form-input" name="latitude" placeholder="Latitude" value="-8.1336" required style="height: 40px; border-radius: 10px; border: 1px solid var(--border);">
                <input class="form-input" name="longitude" placeholder="Longitude" value="113.2228" required style="height: 40px; border-radius: 10px; border: 1px solid var(--border);">
                <input class="form-input md:col-span-2" name="alamat_terdeteksi" placeholder="Alamat patokan otomatis dari device..." style="height: 40px; border-radius: 10px; border: 1px solid var(--border);">
                <button class="btn-primary md:col-span-4" type="submit" style="height: 40px; border-radius: 10px;">Kirim Sinyal SOS Simulasi</button>
            </form>
        </details>
    </div>

    @foreach ($items as $item)
        <div id="selesai-sos-{{ $item->id }}" class="modal-backdrop" hidden>
            <div class="modal-card modal-card-sm">
                <div class="modal-header">
                    <h2>Selesaikan SOS {{ $item->kode_darurat }}</h2>
                    <button type="button" data-modal-close="selesai-sos-{{ $item->id }}">×</button>
                </div>
                <form class="modal-body space-y-4" method="POST" action="{{ route('admin.laporan.darurat.complete', $item) }}">
                    @csrf
                    @method('PATCH')
                    <p class="rounded-xl bg-slate-50 border border-slate-100 p-3 text-xs text-slate-500 leading-relaxed">Setelah disimpan, data SOS ini otomatis diarsipkan ke dalam menu Riwayat Laporan.</p>
                    <div>
                        <label class="form-label" for="catatan-{{ $item->id }}">Catatan Akhir Petugas</label>
                        <textarea id="catatan-{{ $item->id }}" class="form-input" name="catatan_petugas" rows="4" placeholder="Tuliskan perkembangan situasi di lokasi..." required style="border-radius: 10px; border: 1px solid var(--border);"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button class="btn-secondary" type="button" data-modal-close="selesai-sos-{{ $item->id }}">Batal</button>
                        <button class="btn-primary" type="submit">Arsipkan SOS</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

    <script>
        document.addEventListener('click', function (event) {
            const targetId = event.target.closest('[data-modal-target]')?.dataset.modalTarget;
            if (targetId) document.getElementById(targetId)?.removeAttribute('hidden');

            const closeId = event.target.closest('[data-modal-close]')?.dataset.modalClose;
            if (closeId) document.getElementById(closeId)?.setAttribute('hidden', true);

            if (event.target.classList.contains('modal-backdrop')) event.target.setAttribute('hidden', true);
            if (event.target.closest('[data-toast-close]')) event.target.closest('[data-toast]')?.remove();
        });
        document.querySelectorAll('[data-toast]').forEach((toast) => setTimeout(() => toast.remove(), 4500));
    </script>
</x-admin-layout>
