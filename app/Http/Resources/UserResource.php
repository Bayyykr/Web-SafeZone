<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            // Add any other relevant user fields here, e.g., profile_photo_url
            'profile_photo_url' => $this->when($this->profile_photo, asset('storage/' . $this->profile_photo)),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
