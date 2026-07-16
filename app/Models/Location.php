<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[
    Fillable([
        "nama_lokasi",
        "latitude",
        "longitude",
        "polygon_geojson",
        "status_kerawanan",
    ]),
]
class Location extends Model
{
    public function polseks(): HasMany
    {
        return $this->hasMany(Polsek::class, "lokasi_id");
    }

    public function cctvs(): HasMany
    {
        return $this->hasMany(Cctv::class, "lokasi_id");
    }

    public function laporans(): HasMany
    {
        return $this->hasMany(Laporan::class, "lokasi_id");
    }

    public function beritas(): HasMany
    {
        return $this->hasMany(Berita::class, "lokasi_id");
    }
}
