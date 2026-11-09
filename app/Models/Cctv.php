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

    public function getIsYoutubeAttribute(): bool
    {
        if (! $this->url_stream) {
            return false;
        }

        $host = strtolower(parse_url($this->url_stream, PHP_URL_HOST) ?? '');
        return str_contains($host, 'youtu.be') || str_contains($host, 'youtube.com');
    }

    public function getIsMjpegAttribute(): bool
    {
        if (! $this->url_stream) {
            return true; // default streaming is BegalVision MJPEG
        }

        if ($this->is_youtube) {
            return false;
        }

        $url = strtolower($this->url_stream);
        return str_contains($url, 'video_feed')
            || str_contains($url, 'video_feed_raw')
            || str_contains($url, 'mjpg')
            || str_contains($url, 'mjpeg')
            || str_contains($url, 'stream')
            || str_contains($url, ':5000')
            || str_starts_with($url, 'http://')
            || str_starts_with($url, 'https://');
    }

    public function getIsBegalvisionAttribute(): bool
    {
        $url = strtolower($this->url_stream ?? '');
        $nama = strtolower($this->nama ?? '');
        $ket = strtolower($this->keterangan ?? '');

        return str_contains($url, 'video_feed')
            || str_contains($nama, 'begal')
            || str_contains($nama, 'ai')
            || str_contains($ket, 'begalvision')
            || str_contains($ket, 'esp-32')
            || str_contains($ket, 'onnx')
            || (!$this->is_youtube && !empty($this->url_stream));
    }

    public function getAiStreamUrlAttribute(): string
    {
        if ($this->url_stream && !$this->is_youtube) {
            $url = rtrim(trim($this->url_stream), '/');
            if (!str_contains($url, 'video_feed')) {
                return "{$url}/video_feed";
            }
            return $url;
        }

        return config('services.cctv_ai.stream_url', 'http://127.0.0.1:5000/video_feed');
    }

    public function getRawStreamUrlAttribute(): string
    {
        if ($this->url_stream && !$this->is_youtube) {
            $url = rtrim(trim($this->url_stream), '/');
            if (str_contains($url, 'video_feed_raw')) {
                return $url;
            }
            if (str_contains($url, 'video_feed')) {
                return str_replace('video_feed', 'video_feed_raw', $url);
            }
            return "{$url}/video_feed_raw";
        }

        return config('services.cctv_ai.raw_stream_url', 'http://127.0.0.1:5000/video_feed_raw');
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
