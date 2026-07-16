<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(["nama_kategori", "jenis", "warna_marker"])]
class Category extends Model
{
    public function laporans(): HasMany
    {
        return $this->hasMany(Laporan::class, "kategori_id");
    }
}
