@php
    $cctv = $item;
@endphp

<div class="space-y-4">
    <div>
        <label class="form-label">Nama CCTV <span class="form-required">*</span></label>
        <input
            class="form-input"
            name="nama"
            value="{{ $cctv?->nama }}"
            placeholder="Contoh: CCTV Alun-Alun Lumajang"
            required>
        @error('nama') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="form-label">Wilayah Master Lokasi</label>
        <select class="form-select" name="lokasi_id">
            <option value="">Pilih wilayah lokasi</option>
            @foreach (($locations ?? collect()) as $location)
                <option value="{{ $location->id }}" @selected((string) $cctv?->lokasi_id === (string) $location->id)>{{ $location->nama_lokasi }}</option>
            @endforeach
        </select>
        @error('lokasi_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="form-label">Keterangan Posisi CCTV</label>
        <textarea
            class="form-input min-h-[90px]"
            name="keterangan"
            placeholder="Contoh: Menghadap pintu masuk sisi utara Alun-Alun Lumajang">{{ $cctv?->keterangan }}</textarea>
        <p class="mt-1 text-xs text-gray-500">Isi keterangan singkat tentang posisi atau arah pantau CCTV.</p>
        @error('keterangan') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="form-label">URL Live Streaming YouTube</label>
        <input
            class="form-input"
            name="url_stream"
            value="{{ $cctv?->url_stream }}"
            placeholder="https://www.youtube.com/watch?v=... atau https://www.youtube.com/live/...">
        <p class="mt-1 text-xs text-gray-500">Sistem akan otomatis menampilkan player live streaming jika URL valid.</p>
        @error('url_stream') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div style="margin-top:14px">
        <label class="user-checkbox-label">
            <input type="checkbox" name="aktif" value="1" @checked($cctv?->aktif ?? true)>
            <span>CCTV Aktif</span>
        </label>
        @error('aktif') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
</div>
