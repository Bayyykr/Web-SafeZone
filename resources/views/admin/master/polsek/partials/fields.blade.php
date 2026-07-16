@php $polsek = $item; @endphp

<div class="space-y-4">
    <div>
        <label class="form-label" for="ps-nama-{{ $polsek?->id ?? 'create' }}">
            Nama Polsek <span class="form-required">*</span>
        </label>
        <input
            class="form-input"
            id="ps-nama-{{ $polsek?->id ?? 'create' }}"
            name="nama"
            value="{{ $polsek?->nama }}"
            placeholder="Contoh: Polsek Lumajang Kota"
            required
            maxlength="255"
            data-label="Nama Polsek">
    </div>

    <div>
        <label class="form-label" for="ps-lokasi-{{ $polsek?->id ?? 'create' }}">
            Wilayah Lokasi
        </label>
        <select
            class="form-select"
            id="ps-lokasi-{{ $polsek?->id ?? 'create' }}"
            name="lokasi_id"
            data-label="Wilayah Lokasi">
            <option value="">— Pilih wilayah lokasi —</option>
            @foreach (($locations ?? collect()) as $location)
                <option value="{{ $location->id }}" @selected((string) $polsek?->lokasi_id === (string) $location->id)>
                    {{ $location->nama_lokasi }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="form-label" for="ps-alamat-{{ $polsek?->id ?? 'create' }}">
            Alamat
        </label>
        <textarea
            class="form-input"
            id="ps-alamat-{{ $polsek?->id ?? 'create' }}"
            name="alamat"
            placeholder="Alamat lengkap polsek"
            maxlength="255"
            style="min-height:80px;resize:vertical"
            data-label="Alamat">{{ $polsek?->alamat }}</textarea>
    </div>

    <div>
        <label class="form-label" for="ps-telepon-{{ $polsek?->id ?? 'create' }}">
            Telepon
        </label>
        <input
            class="form-input"
            id="ps-telepon-{{ $polsek?->id ?? 'create' }}"
            type="tel"
            name="telepon"
            value="{{ $polsek?->telepon }}"
            placeholder="Contoh: 0334881001"
            maxlength="30"
            inputmode="numeric"
            data-numeric
            data-label="Telepon">
    </div>
</div>
