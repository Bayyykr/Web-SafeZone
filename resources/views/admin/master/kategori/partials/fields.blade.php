@php
    $selectedJenis = old('jenis', $item?->jenis ?? $jenis ?? 'kejahatan');
    $selectedColor = old('warna_marker', $item?->warna_marker ?? '#FF0000');
@endphp

<div class="space-y-4">
    <div>
        <label class="form-label" for="kat-nama-{{ $item?->id ?? 'create' }}">
            Nama Kategori <span class="form-required">*</span>
        </label>
        <input
            class="form-input"
            id="kat-nama-{{ $item?->id ?? 'create' }}"
            name="nama_kategori"
            value="{{ old('nama_kategori', $item?->nama_kategori) }}"
            placeholder="Contoh: Pembegalan / Perampokan Jalanan"
            required
            maxlength="255"
            data-label="Nama Kategori">
    </div>

    <div>
        <label class="form-label" for="kat-jenis-{{ $item?->id ?? 'create' }}">
            Jenis <span class="form-required">*</span>
        </label>
        <select
            class="form-select"
            id="kat-jenis-{{ $item?->id ?? 'create' }}"
            name="jenis"
            required
            data-label="Jenis">
            <option value="kejahatan" @selected($selectedJenis === 'kejahatan')>Kejahatan</option>
            <option value="kecelakaan" @selected($selectedJenis === 'kecelakaan')>Kecelakaan</option>
        </select>
    </div>

    <div>
        <label class="form-label">Warna Marker Peta <span class="form-required">*</span></label>
        <div class="color-picker-wrap">
            <input type="hidden" name="warna_marker" value="{{ $selectedColor }}" data-color-value>
            <div class="color-picker-header">
                <span class="color-preview-swatch" data-color-preview></span>
                <span class="color-name-label" data-color-label>Merah</span>
            </div>
            <div class="color-swatches-grid" data-color-swatches></div>
        </div>
    </div>
</div>
