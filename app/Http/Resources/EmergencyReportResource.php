<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmergencyReportResource extends JsonResource
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
            'nearest_polsek' => $this->polsek ? new PolsekResource($this->polsek) : null, // Assuming PolsekResource exists
            'kode_darurat' => $this->kode_darurat,
            'status' => $this->status,
            'latitude' => (float) $this->latitude,
            'longitude' => (float) $this->longitude,
            'alamat_terdeteksi' => $this->alamat_terdeteksi,
            'jarak_polsek_km' => $this->jarak_polsek_km,
            'waktu_sos' => $this->waktu_sos,
            'telemetri' => $this->telemetri,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
