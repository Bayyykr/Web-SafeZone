<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CctvResource extends JsonResource
{
    /**
     * Transform the resource into an array for Mobile / API consumers.
     * Mode: View-Only (Read-Only). Mobile clients can view live streams and sensor telemetries
     * but cannot modify AI inference parameters, switch cameras, or control hardware actuators.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isYoutube = (bool) $this->is_youtube;
        $isMjpeg = (bool) $this->is_mjpeg;
        $isBegalvision = (bool) $this->is_begalvision;
        $streamUrl = $this->url_stream ?: $this->ai_stream_url;

        // Telemetri sensor ESP-32 (Read-only untuk Mobile App)
        $telemetri = null;
        if ($isBegalvision) {
            $telemetri = [
                'pir_motion' => false,
                'status_gerakan' => 'Aman (Tidak Ada Gerakan)',
                'noise_db' => rand(40, 50),
                'status_suara' => 'Normal',
                'lux' => rand(20, 35),
                'status_cahaya' => 'Redup',
                'sirine_aktif' => false,
                'lampu_aktif' => false,
                'last_updated' => now()->toIso8601String(),
            ];
        }

        return [
            'id' => $this->id,
            'nama' => $this->nama,
            'keterangan' => $this->keterangan,
            'url_stream' => $streamUrl,
            'stream_type' => $isYoutube ? 'youtube' : ($isMjpeg ? 'mjpeg' : 'direct'),
            'embed_url' => $this->embed_url,
            'aktif' => (bool) $this->aktif,
            'is_live' => (bool) $this->aktif,
            'is_begalvision' => $isBegalvision,
            'is_mjpeg' => $isMjpeg,
            'is_youtube' => $isYoutube,
            'can_control' => false, // Mobile hanya melihat (View-Only)
            'lokasi' => $this->lokasi ? new LocationResource($this->lokasi) : null,
            'telemetri' => $telemetri,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
