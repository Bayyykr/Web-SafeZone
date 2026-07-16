<x-admin-layout>
    @push('styles')
        @vite(['resources/css/admin/pages/master.css', 'resources/css/admin/pages/report.css'])
    @endpush
    <x-slot name="header">Riwayat Laporan</x-slot>

    @php
        $statusClass = [
            'selesai' => 'bg-gray-900 text-white',
            'arsip' => 'bg-gray-200 text-gray-800',
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
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6M7 4h7l5 5v11a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z" />
                    </svg>
                </div>
                <div>
                    <div class="user-hero-title">Riwayat Laporan</div>
                    <div class="user-hero-subtitle">Arsip permanen laporan reguler selesai dan laporan darurat ditangani</div>
                </div>
            </div>
            <div class="user-stats-row">
                <div class="user-stat-pill">
                    <strong>{{ number_format($summary['total']) }}</strong> Arsip
                </div>
                <div class="user-stat-pill pill-accent">
                    <strong>{{ number_format($summary['pending']) }}</strong> Pending
                </div>
                <div class="user-stat-pill pill-success">
                    <strong>{{ number_format($summary['confirmed']) }}</strong> Dikonfirmasi
                </div>
                <div class="user-stat-pill" style="background: #e0f2fe; border-color: transparent; color: #0369a1;">
                    <strong>{{ number_format($summary['done']) }}</strong> Selesai
                </div>
            </div>
        </div>

        <div class="user-table-card p-5 mb-6">
            <form class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3 items-end" method="GET" action="{{ route('admin.laporan.riwayat') }}">
                <div>
                    <label class="form-label" for="search">Cari Arsip</label>
                    <input id="search" class="form-input" name="search" value="{{ request('search') }}" placeholder="Kata kunci..." style="height: 40px; border-radius: 10px; border: 1px solid var(--border);">
                </div>
                <div>
                    <label class="form-label" for="jenis">Jenis</label>
                    <select id="jenis" class="form-select" name="jenis" style="height: 40px; border-radius: 10px; border: 1px solid var(--border);">
                        <option value="">Semua Jenis</option>
                        <option value="kejahatan" @selected(request('jenis') === 'kejahatan')>Kejahatan</option>
                        <option value="kecelakaan" @selected(request('jenis') === 'kecelakaan')>Kecelakaan</option>
                    </select>
                </div>
                <div>
                    <label class="form-label" for="kategori_id">Kategori</label>
                    <select id="kategori_id" class="form-select" name="kategori_id" style="height: 40px; border-radius: 10px; border: 1px solid var(--border);">
                        <option value="">Semua Kategori</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected((string) request('kategori_id') === (string) $category->id)>{{ $category->nama_kategori }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label" for="tanggal_mulai">Dari Tanggal</label>
                    <input id="tanggal_mulai" class="form-input" type="date" name="tanggal_mulai" value="{{ request('tanggal_mulai') }}" style="height: 40px; border-radius: 10px; border: 1px solid var(--border);">
                </div>
                <div>
                    <label class="form-label" for="tanggal_selesai">Sampai Tanggal</label>
                    <input id="tanggal_selesai" class="form-input" type="date" name="tanggal_selesai" value="{{ request('tanggal_selesai') }}" style="height: 40px; border-radius: 10px; border: 1px solid var(--border);">
                </div>
                <div class="col-span-1 sm:col-span-2 md:col-span-5 flex justify-end gap-2 mt-2">
                    <a class="btn-secondary" href="{{ route('admin.laporan.riwayat') }}">Reset</a>
                    <button class="btn-primary" type="submit">Terapkan Filter</button>
                </div>
            </form>
        </div>

        <div class="user-table-card mb-6">
            <div class="p-5 border-b border-slate-100">
                <h2 class="text-base font-bold text-slate-800">Audit Trail Laporan Reguler</h2>
                <p class="text-xs text-slate-500 mt-1">Laporan dari masyarakat yang sudah melewati konfirmasi dan selesai ditangani.</p>
            </div>
            <table class="user-table">
                <thead>
                    <tr>
                        <th style="width: 130px;">ID Laporan</th>
                        <th>Waktu & Kejadian</th>
                        <th>Pelapor</th>
                        <th>Kejadian & Lokasi</th>
                        <th style="width: 100px;">Status Akhir</th>
                        <th>Petugas / Polsek</th>
                        <th style="width: 80px; text-align: center;">Detail</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        @php($kodeLaporan = 'REP-' . $item->created_at?->format('Ym') . '-' . str_pad((string) $item->id, 3, '0', STR_PAD_LEFT))
                        <tr>
                            <td class="font-mono font-bold text-slate-800">{{ $kodeLaporan }}</td>
                            <td>
                                <div class="text-slate-700 font-semibold">Melapor: {{ $item->created_at?->format('d/m/Y H:i') }}</div>
                                <div class="text-xs text-slate-400 mt-0.5">Kejadian: {{ $item->created_at?->format('d/m/Y H:i') }}</div>
                            </td>
                            <td>
                                <div class="font-bold text-slate-800">{{ $item->user?->name ?? '-' }}</div>
                                <div class="text-xs text-slate-400 mt-0.5">{{ $item->user?->telepon ?? $item->user?->email ?? '-' }}</div>
                            </td>
                            <td>
                                <div class="font-semibold text-slate-700">{{ $item->kategori?->nama_kategori ?? '-' }}</div>
                                <div class="text-xs text-slate-400 mt-0.5">{{ $item->lokasi?->nama_lokasi ?? '-' }} • {{ $item->polsek?->nama ?? '-' }}</div>
                            </td>
                            <td>
                                <span class="status-badge active" style="background: #0f172a; color: #ffffff;">Selesai</span>
                            </td>
                            <td>
                                <div class="font-semibold text-slate-700">{{ $item->konfirmasi?->petugas?->name ?? '-' }}</div>
                                <div class="text-xs text-slate-400 mt-0.5">{{ $item->konfirmasi?->dikonfirmasi_pada?->format('d/m/Y H:i') ?? '-' }}</div>
                            </td>
                            <td style="text-align: center;">
                                <button class="btn-edit" type="button" data-modal-target="riwayat-detail-{{ $item->id }}" style="background: #2952e3;">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 14px; height: 14px;">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-slate-400 font-semibold">Belum ada laporan reguler yang selesai.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="user-table-footer">
                {{ $items->links() }}
            </div>
        </div>

        <div class="user-table-card">
            <div class="p-5 border-b border-slate-100">
                <h2 class="text-base font-bold text-slate-800">Arsip Laporan Darurat / SOS</h2>
                <p class="text-xs text-slate-500 mt-1">SOS yang telah diselesaikan petugas otomatis menetap di sini sebagai rekam jejak.</p>
            </div>
            <table class="user-table">
                <thead>
                    <tr>
                        <th style="width: 140px;">ID Darurat</th>
                        <th>Waktu SOS</th>
                        <th>Pelapor</th>
                        <th>Koordinat & Polsek</th>
                        <th>Response</th>
                        <th>Catatan Akhir</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($emergencyArchives as $item)
                        <tr>
                            <td class="font-mono font-bold text-slate-800">{{ $item->kode_darurat }}</td>
                            <td>
                                <div class="text-slate-700 font-semibold">{{ $item->waktu_sos?->format('d/m/Y H:i:s') ?? '-' }}</div>
                                <div class="text-xs text-slate-400 mt-0.5">Selesai: {{ $item->waktu_selesai?->format('d/m/Y H:i:s') ?? '-' }}</div>
                            </td>
                            <td>
                                <div class="font-bold text-slate-800">{{ $item->user?->name ?? 'Pelapor Anonim' }}</div>
                                <div class="text-xs text-slate-400 mt-0.5">{{ $item->user?->telepon ?? $item->user?->email ?? '-' }}</div>
                            </td>
                            <td>
                                <div class="font-mono text-xs text-slate-700">{{ $item->latitude }}, {{ $item->longitude }}</div>
                                <div class="text-xs text-slate-400 mt-0.5">{{ $item->nearestPolsek?->nama ?? '-' }} {{ $item->jarak_polsek_km !== null ? '• ' . $item->jarak_polsek_km . ' km' : '' }}</div>
                            </td>
                            <td>
                                <div class="font-semibold text-slate-700">{{ $item->response_time_minutes !== null ? $item->response_time_minutes . ' menit' : '-' }}</div>
                                <div class="text-xs text-slate-400 mt-0.5">Petugas: {{ $item->petugas_penanganan ?: '-' }}</div>
                            </td>
                            <td>
                                <div class="max-w-[320px] whitespace-pre-line text-xs text-slate-600 leading-relaxed">{{ $item->catatan_petugas ?: '-' }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-8 text-slate-400 font-semibold">Belum ada arsip laporan darurat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @foreach ($items as $item)
        @php($kodeLaporan = 'REP-' . $item->created_at?->format('Ym') . '-' . str_pad((string) $item->id, 3, '0', STR_PAD_LEFT))
        <div id="riwayat-detail-{{ $item->id }}" class="modal-backdrop" hidden>
            <div class="modal-card">
                <div class="modal-header">
                    <h2>Detail Audit {{ $kodeLaporan }}</h2>
                    <button type="button" data-modal-close="riwayat-detail-{{ $item->id }}">×</button>
                </div>
                <div class="modal-body space-y-4 text-sm text-slate-700">
                    @if ($item->foto_kejadian)
                        <img class="max-h-72 w-full rounded-xl object-cover border border-slate-200" src="{{ asset('storage/' . $item->foto_kejadian) }}" alt="File bukti laporan">
                    @endif
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <span class="text-xs text-slate-400 font-bold block">ID Laporan</span>
                            <strong class="text-slate-800">{{ $kodeLaporan }}</strong>
                        </div>
                        <div>
                            <span class="text-xs text-slate-400 font-bold block">Waktu Melapor</span>
                            <strong class="text-slate-800">{{ $item->created_at?->format('d/m/Y H:i:s') }}</strong>
                        </div>
                        <div>
                            <span class="text-xs text-slate-400 font-bold block">Nama Pelapor</span>
                            <strong class="text-slate-800">{{ $item->user?->name ?? '-' }}</strong>
                            <span class="text-xs text-slate-400 block mt-0.5">{{ $item->user?->telepon ?? $item->user?->email ?? '-' }}</span>
                        </div>
                        <div>
                            <span class="text-xs text-slate-400 font-bold block">Kategori Kejadian</span>
                            <strong class="text-slate-800">{{ $item->kategori?->nama_kategori ?? '-' }}</strong>
                            <span class="capitalize text-xs text-slate-400 block mt-0.5">{{ $item->kategori?->jenis ?? '-' }}</span>
                        </div>
                        <div>
                            <span class="text-xs text-slate-400 font-bold block">Lokasi Administratif</span>
                            <strong class="text-slate-800">{{ $item->lokasi?->nama_lokasi ?? '-' }}</strong>
                            <span class="text-xs text-slate-400 block mt-0.5">{{ $item->polsek?->nama ?? '-' }}</span>
                        </div>
                        <div>
                            <span class="text-xs text-slate-400 font-bold block">Koordinat Geografis</span>
                            <strong class="text-slate-800">{{ $item->latitude ?? '-' }}, {{ $item->longitude ?? '-' }}</strong>
                        </div>
                    </div>
                    <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                        <strong class="text-slate-800 block mb-1">{{ $item->judul_laporan }}</strong>
                        <p class="whitespace-pre-line text-slate-600 leading-relaxed">{{ $item->deskripsi ?: '-' }}</p>
                    </div>
                    <div class="rounded-xl bg-slate-900 p-4 text-white">
                        <strong class="block mb-2">Status & Log Penanganan</strong>
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <p><span class="text-slate-400">Status Akhir:</span> Selesai</p>
                            <p><span class="text-slate-400">Petugas:</span> {{ $item->konfirmasi?->petugas?->name ?? '-' }}</p>
                            <p class="col-span-2"><span class="text-slate-400">Catatan Akhir:</span> {{ $item->konfirmasi?->catatan ?: '-' }}</p>
                        </div>
                    </div>
                </div>
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
