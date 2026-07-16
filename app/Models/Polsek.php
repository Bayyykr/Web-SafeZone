<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(["lokasi_id", "nama", "alamat", "telepon"])]
class Polsek extends Model
{
    public function lokasi(): BelongsTo
    {
        return $this->belongsTo(Location::class, "lokasi_id");
    }

    public function laporans(): HasMany
    {
        return $this->hasMany(Laporan::class);
    }
}
