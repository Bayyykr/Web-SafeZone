<?php

namespace App\Services;

use App\Models\Berita;
use Illuminate\Support\Facades\Storage;

class BeritaService
{
    public function store(array $data, ?object $fotoFile, int $userId): Berita
    {
        $data['user_id'] = $userId;
        $data['published_at'] = $data['status'] === 'published' ? now() : null;

        if ($fotoFile) {
            $data['foto'] = $fotoFile->store('berita', 'public');
        }

        return Berita::create($data);
    }

    public function update(Berita $berita, array $data, ?object $fotoFile): Berita
    {
        if ($fotoFile) {
            if ($berita->foto) {
                Storage::disk('public')->delete($berita->foto);
            }
            $data['foto'] = $fotoFile->store('berita', 'public');
        }

        if (($data['status'] ?? $berita->status) === 'published' && !$berita->published_at) {
            $data['published_at'] = now();
        }

        if (($data['status'] ?? $berita->status) === 'draft') {
            $data['published_at'] = null;
        }

        $berita->update($data);

        return $berita;
    }

    public function destroy(Berita $berita): bool
    {
        if ($berita->foto) {
            Storage::disk('public')->delete($berita->foto);
        }

        return $berita->delete();
    }

    public function publish(Berita $berita): bool
    {
        return $berita->update([
            'status' => 'published',
            'published_at' => $berita->published_at ?: now(),
        ]);
    }

    public function draft(Berita $berita): bool
    {
        return $berita->update([
            'status' => 'draft',
            'published_at' => null,
        ]);
    }
}
