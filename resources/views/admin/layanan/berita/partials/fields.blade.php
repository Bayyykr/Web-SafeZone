<div class="space-y-4">
    <div>
        <label class="form-label" for="judul-{{ $item?->id ?? 'create' }}">Judul Berita</label>
        <input id="judul-{{ $item?->id ?? 'create' }}" class="form-input" name="judul" value="{{ old('judul', $item?->judul) }}" required style="height: 40px; border-radius: 10px; border: 1px solid var(--border);">
    </div>

    <div class="user-form-grid">
        <div>
            <label class="form-label" for="lokasi-{{ $item?->id ?? 'create' }}">Kecamatan / Lokasi</label>
            <select id="lokasi-{{ $item?->id ?? 'create' }}" class="form-select" name="lokasi_id" style="height: 40px; border-radius: 10px; border: 1px solid var(--border);">
                <option value="">Pilih lokasi</option>
                @foreach ($locations as $location)
                    <option value="{{ $location->id }}" @selected((string) old('lokasi_id', $item?->lokasi_id) === (string) $location->id)>
                        {{ $location->nama_lokasi }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="form-label" for="status-{{ $item?->id ?? 'create' }}">Status</label>
            <select id="status-{{ $item?->id ?? 'create' }}" class="form-select" name="status" required style="height: 40px; border-radius: 10px; border: 1px solid var(--border);">
                <option value="draft" @selected(old('status', $item?->status ?? 'draft') === 'draft')>Draft</option>
                <option value="published" @selected(old('status', $item?->status) === 'published')>Published</option>
            </select>
        </div>
    </div>

    <div>
        <label class="form-label" for="isi-{{ $item?->id ?? 'create' }}">Isi Berita</label>
        <textarea id="isi-{{ $item?->id ?? 'create' }}" class="form-input" name="isi_berita" rows="6" required style="border-radius: 10px; border: 1px solid var(--border); padding: 10px 12px;">{{ old('isi_berita', $item?->isi_berita) }}</textarea>
    </div>

    <div>
        <label class="form-label" for="foto-{{ $item?->id ?? 'create' }}">Foto</label>
        <input id="foto-{{ $item?->id ?? 'create' }}" class="form-input" name="foto" type="file" accept="image/*" style="height: auto; padding: 6px 12px; border-radius: 10px; border: 1px solid var(--border);">
        @if ($item?->foto)
            <p class="mt-1 text-xs text-gray-500">Foto saat ini: {{ basename($item->foto) }}</p>
        @endif
    </div>
</div>
