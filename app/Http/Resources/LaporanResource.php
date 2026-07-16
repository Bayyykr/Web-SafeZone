<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LaporanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'judul_laporan' => $this->judul_laporan,
            'deskripsi' => $this->deskripsi,
            'kategori' => $this->kategori ? new CategoryResource($this->kategori) : null, // Assuming CategoryResource exists
            'lokasi' => $this->lokasi ? new LocationResource($this->lokasi) : null, // Assuming LocationResource exists
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'foto_kejadian_url' => $this->foto_kejadian ? asset('storage/' . $this->foto_kejadian) : null,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
