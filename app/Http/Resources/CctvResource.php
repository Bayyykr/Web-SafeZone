<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CctvResource extends JsonResource
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
            'nama' => $this->nama,
            'url_stream' => $this->url_stream,
            'aktif' => $this->aktif,
            'lokasi' => $this->lokasi ? new LocationResource($this->lokasi) : null, // Assuming LocationResource exists
        ];
    }
}
