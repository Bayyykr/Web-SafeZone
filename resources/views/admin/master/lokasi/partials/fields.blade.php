@php
    $selectedStatus = old('status_kerawanan', $item?->status_kerawanan ?? 'Aman');
@endphp

<div class="space-y-4">
    <div>
        <label class="form-label">Nama Lokasi / Area Pantau <span class="form-required">*</span></label>
        <input
            class="form-input"
            name="nama_lokasi"
            value="{{ old('nama_lokasi', $item?->nama_lokasi) }}"
            placeholder="Contoh: Lumajang (Kota)"
            required>
        @error('nama_lokasi') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="form-label">Latitude</label>
            <input
                class="form-input"
                name="latitude"
                value="{{ old('latitude', $item?->latitude) }}"
                placeholder="-8.1331">
            @error('latitude') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="form-label">Longitude</label>
            <input
                class="form-input"
                name="longitude"
                value="{{ old('longitude', $item?->longitude) }}"
                placeholder="113.2224">
            @error('longitude') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
    </div>

    <div>
        <label class="form-label">Status Kerawanan <span class="form-required">*</span></label>
        <select class="form-select" name="status_kerawanan" required>
            <option value="Aman" @selected($selectedStatus === 'Aman')>Aman</option>
            <option value="Rawan" @selected($selectedStatus === 'Rawan')>Rawan</option>
            <option value="Sangat Rawan" @selected($selectedStatus === 'Sangat Rawan')>Sangat Rawan</option>
        </select>
        @error('status_kerawanan') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="form-label">Polygon GeoJSON <span class="font-normal text-gray-400">(Opsional)</span></label>
        <textarea
            class="form-input min-h-28"
            name="polygon_geojson"
            placeholder='{"type":"Polygon","coordinates":[...]}'>{{ $item?->polygon_geojson }}</textarea>
        <p class="mt-1 text-xs text-gray-500">Digunakan jika nanti wilayah ingin divisualisasikan sebagai choropleth/area penuh di peta.</p>
        @error('polygon_geojson') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
</div>
