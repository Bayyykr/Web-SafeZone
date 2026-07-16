<x-admin-layout>
    <x-slot name="header">Live CCTV</x-slot>

    <div class="user-page">
        <div class="user-hero">
            <div class="user-hero-left">
                <div class="user-hero-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z" />
                    </svg>
                </div>
                <div>
                    <div class="user-hero-title">{{ $item->nama }}</div>
                    <div class="user-hero-subtitle">
                        Wilayah: {{ $item->lokasi?->nama_lokasi ?? '-' }} | Posisi: {{ $item->keterangan ?: '-' }}
                    </div>
                </div>
            </div>
            <a class="btn-secondary" href="{{ route('admin.cctvs.index') }}">Kembali</a>
        </div>

        @if ($item->embed_url)
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-black shadow-lg">
                <div class="aspect-video w-full">
                    <iframe
                        class="h-full w-full"
                        src="{{ $item->embed_url }}"
                        title="Live Streaming {{ $item->nama }}"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        allowfullscreen>
                    </iframe>
                </div>
            </div>
        @else
            <div class="rounded-xl border border-yellow-200 bg-yellow-50 p-5 text-yellow-800">
                URL live streaming belum tersedia atau tidak valid. Silakan edit data CCTV dan masukkan URL livestream YouTube.
            </div>
        @endif
    </div>
</x-admin-layout>
