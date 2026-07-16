<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['lokasi_id', 'nama', 'url_stream', 'keterangan', 'aktif'])]
class Cctv extends Model
{
    protected function casts(): array
    {
        return ['aktif' => 'boolean'];
    }

    public function lokasi(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'lokasi_id');
    }

    public function getEmbedUrlAttribute(): ?string
    {
        if (! $this->url_stream) {
            return null;
        }

        $url = trim($this->url_stream);
        $parts = parse_url($url);
        $host = strtolower($parts['host'] ?? '');
        $path = trim($parts['path'] ?? '', '/');
        $videoId = null;

        if (str_contains($host, 'youtu.be')) {
            $videoId = explode('/', $path)[0] ?? null;
        }

        if (str_contains($host, 'youtube.com')) {
            if (str_starts_with($path, 'watch')) {
                parse_str($parts['query'] ?? '', $query);
                $videoId = $query['v'] ?? null;
            } elseif (str_starts_with($path, 'live/')) {
                $videoId = explode('/', $path)[1] ?? null;
            } elseif (str_starts_with($path, 'embed/')) {
                $videoId = explode('/', $path)[1] ?? null;
            }
        }

        if (! $videoId) {
            return $url;
        }

        return 'https://www.youtube.com/embed/'.
            $videoId.
            '?autoplay=1&mute=1&rel=0';
    }
}
