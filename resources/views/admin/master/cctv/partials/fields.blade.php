@php
    $cctv = $item;
    $defaultAiStream = config('services.cctv_ai.stream_url', 'http://127.0.0.1:5000/video_feed');
    $defaultRawStream = config('services.cctv_ai.raw_stream_url', 'http://127.0.0.1:5000/video_feed_raw');
@endphp

<div class="space-y-4">
    <div>
        <label class="form-label">Nama CCTV / Unit BegalVision <span class="form-required">*</span></label>
        <input
            class="form-input"
            name="nama"
            value="{{ old('nama', $cctv?->nama) }}"
            placeholder="Contoh: BegalVision AI - Pos Pantau Alun-Alun Lumajang"
            required>
        @error('nama') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="form-label">Wilayah Master Lokasi</label>
        <select class="form-select" name="lokasi_id">
            <option value="">Pilih wilayah lokasi</option>
            @foreach (($locations ?? collect()) as $location)
                <option value="{{ $location->id }}" @selected((string) old('lokasi_id', $cctv?->lokasi_id) === (string) $location->id)>{{ $location->nama_lokasi }}</option>
            @endforeach
        </select>
        @error('lokasi_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="form-label">Keterangan Posisi / Konfigurasi CCTV</label>
        <textarea
            class="form-input min-h-[90px]"
            name="keterangan"
            placeholder="Contoh: Unit Webcam USB Edge AI BegalVision dengan sensor ESP-32 (PIR, Mic INMP441, Lux BH1750, Buzzer Alarm)">{{ old('keterangan', $cctv?->keterangan) }}</textarea>
        <p class="mt-1 text-xs text-gray-500">Isi keterangan singkat tentang posisi pantau, perangkat sensor, atau jenis kamera.</p>
        @error('keterangan') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <div class="flex items-center justify-between mb-1">
            <label class="form-label mb-0">URL Live Streaming</label>
            <span class="text-xs text-blue-600 font-semibold">Mendukung BegalVision AI & YouTube</span>
        </div>
        <input
            id="url_stream_input_{{ $cctv?->id ?? 'new' }}"
            class="form-input font-mono text-xs"
            name="url_stream"
            value="{{ old('url_stream', $cctv?->url_stream ?? $defaultAiStream) }}"
            placeholder="http://127.0.0.1:5000/video_feed atau https://www.youtube.com/watch?v=...">
        <div class="mt-2 flex flex-wrap gap-2 text-xs">
            <button
                type="button"
                class="px-2.5 py-1 rounded bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200 transition font-medium cursor-pointer"
                onclick="document.getElementById('url_stream_input_{{ $cctv?->id ?? 'new' }}').value = '{{ $defaultAiStream }}'">
                ⚡ Preset: BegalVision AI Stream
            </button>
            <button
                type="button"
                class="px-2.5 py-1 rounded bg-gray-50 text-gray-700 hover:bg-gray-100 border border-gray-200 transition font-medium cursor-pointer"
                onclick="document.getElementById('url_stream_input_{{ $cctv?->id ?? 'new' }}').value = '{{ $defaultRawStream }}'">
                📷 Preset: Webcam Raw (Tanpa AI)
            </button>
        </div>
        <p class="mt-1.5 text-xs text-gray-500">Mendukung endpoint MJPEG Flask AI (<code>/video_feed</code>) maupun URL Livestream YouTube.</p>
        @error('url_stream') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div style="margin-top:14px">
        <label class="user-checkbox-label">
            <input type="checkbox" name="aktif" value="1" @checked(old('aktif', $cctv?->aktif ?? true))>
            <span>CCTV Aktif</span>
        </label>
        @error('aktif') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
</div>
